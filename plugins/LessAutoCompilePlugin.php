<?php
require_once "Less/Lessc.php";

class LessAutoCompilePlugin extends Zend_Controller_Plugin_Abstract {
	// main config
	protected $cnf;
	// css config for cache 
	protected $css_cnf;
	// flag if any css file is updated
	protected $css_ini_updates = false;
	// lessphp compilator
	protected $less;
	
	// Configuration
	protected $_root_path = "";
	protected $_public_path = "";
	
	public function routeStartup(Zend_Controller_Request_Abstract $request){
		$this->cnf = Zend_Registry::get("cnf");
		$this->autoCompile();
	}
		
	public function autoCompile(){
		$cnf = $this->cnf;

		if(isset($cnf->path->root))
			$this->setRootPath($cnf->path->root);
		if(isset($cnf->path->public))
			$this->setPublicPath($cnf->path->public);

		if($cnf->less->development && !empty($cnf->less->files)){
			$this->less = new Less_Lessc();
			$files = explode(",", $cnf->less->files);
			if(isset($cnf->less->formatter))
				$this->less->setFormatter($cnf->less->formatter);
			if(isset($cnf->less->css_ini)){
				$css_ini = $this->_root_path . $cnf->less->css_ini;
				if(file_exists($css_ini)){
					$css_config = new Zend_Config_Ini($css_ini, null, array('skipExtends' => true,'allowModifications' => true));
				}else{
					$css_config = new Zend_Config(array(), true);
				}
				$this->css_cnf = $css_config;
			}
			$this->css_ini_updates = false;
			foreach($files as $file){
				$this->fileCompilation($file);
			}
			if(isset($css_ini) && $this->css_ini_updates){
				$writer = new Zend_Config_Writer_Ini(array('config' => $css_config, 'filename' => $css_ini));
				$writer
					// ->setRenderWithoutSections()
					->write();
			}
		}
		if(isset($cnf->less->css_ini)){
			// Занесение объекта конфигурации в реестр
			Zend_Registry::set('css', $css_config);
		}
	}
	
	protected function fileCompilation($file){
		$cnf = $this->cnf;
		if(file_exists($this->_root_path . $cnf->less->path . $file)){
			$cache = $this->getCache($file);
			// if cache data empty put input file name as data
			if(empty($cache))
				$cache = $this->getInputFileName($file);
			// Compilation
			$newCache = $this->less->cachedCompile($cache);
			
			$this->saveCached($cache, $newCache, $file);
		}
	}
	
	protected function getCache($file){
		$cache = null;
		$cnf = $this->cnf;
		$css_config = $this->css_cnf;
		
		$inputFile = $this->getInputFileName($file);
		// if not set css_ini file use .cache file for each compiled .less file
		if(!isset($cnf->less->css_ini)){
			// cahch file name
			$cacheFile = $inputFile.".cache";
			if (file_exists($cacheFile)) {
				$cache = unserialize(file_get_contents($cacheFile));
			}
		// else look into ini file for cach data
		}else{
			// absolute path to css file
			$absOutputFile = $this->getAbsOutputFileName($file);
			// if there is cache
			if(isset($css_config->$absOutputFile->cache)){
				// get it
				$cache = unserialize($css_config->$absOutputFile->get('cache', serialize(array())));
				// id root different unset cahce data
				if($cache['root']!=$inputFile)
					$cache = null;
			}
		}
		return $cache;
	}
	
	protected function saveCached($cache, $newCache, $file){
		$cnf = $this->cnf;
		$css_config = $this->css_cnf;
		
		$inputFile = $this->getInputFileName($file);
		$outputFile = $this->getOutputFileName($file);
		
		if(!isset($cnf->less->css_ini)){
			if (!is_array($cache) || $newCache["updated"] > $cache["updated"]) {
				// cahch file name
				$cacheFile = $inputFile.".cache";
				// if updated value is newer than was, save new .cache file
				file_put_contents($cacheFile, serialize($newCache));
				// and save compiled data into file
				file_put_contents($outputFile, $newCache['compiled']);
			}
		}else{
			// absolute path to css file
			$absOutputFile = $this->getAbsOutputFileName($file);
			if(!isset($css_config->$absOutputFile->updated) || $newCache["updated"]>$css_config->$absOutputFile->updated){
				// if updated value is newer than was, 
				if(!isset($css_config->$absOutputFile))
					// create new cinfig entry if missed
					$css_config->$absOutputFile = array();
				// set new update value
				$css_config->$absOutputFile->updated = $newCache["updated"];

				// save compiled data to file
				file_put_contents($outputFile, $newCache['compiled']);
				
				// we will not save compiled data into config
				unset($newCache['compiled']);

				// set cache serialized value to config
				$css_config->$absOutputFile->cache = serialize($newCache);
				// and put flag congig updated
				$this->css_ini_updates = true;
			}
		}
	}
	
	protected function getInputFileName($file){
		$cnf = $this->cnf;
		// full path to input less file
		return $this->_root_path . $cnf->less->path . $file;
	}
	protected function getOutputFileName($file){
		$cnf = $this->cnf;
		$str = new Dnk_Common_String();
		$ext = $str->getExtension($file);
		$outFile = substr($file, 0, strlen($file)-strlen($ext))."css";
		// full path to output css file
		return $this->_root_path . $cnf->less->outPath . $outFile;
	}
	protected function getAbsOutputFileName($file){
		$cnf = $this->cnf;
		// absolute path to css file
		return str_replace($this->_public_path, "/", $this->getOutputFileName($file));
	}
	
	function setPublicPath($path){
		$this->_public_path = $path;
	}
	function setRootPath($path){
		$this->_root_path = $path;
	}
}

?>