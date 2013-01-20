# lessphp for Zend Framework

Plugin for autocompiling less in Zend Framework based on lessphp (http://leafo.net/lessphp/)

[Home page](http://zendless.cr-a.net/)

## Installation

1. Get source code using one of options:
	* download latest release [Download the latest release](https://github.com/ludmanp/ZendLess/zipball/master) or
	* clone the repo `git://github.com/ludmanp/ZendLess.git`
2. Copy folder Less to your library folder
3. Copy `LessAutoCompilePlugin.php` from plugins folder to your plugins folder
4. In `Zend_Registry` have been registered config with name `cnf` containing

	```
	'less' => array(
		'development'	=> true,
		'files'			=> 'list of less files separated by comma (,)',
		'path'			=> 'path/to/less/folder/', 
		'formatter'		=> 'lessjs', // allowed "lessjs", "compressed", "classic"
		'outPath'		=> 'path/to/css/folder/', 
		'css_ini'		=> 'path/to/css.ini', 
	),
	```
	
	If you use other name for config change it in `LessAutoCompilePlugin.php` in `routeStartup`

5. Register in bootstrap `LessAutoCompilePlugin.php` plugin 
	
	```
	$lessAutoCompilePlugin = new LessAutoCompilePlugin();
       $front->registerPlugin($lessAutoCompilePlugin);
	```

6. Register helper path 

```
$view->addHelperPath("Less/View/Helper", "Less_View_Helper");
```
	
If `less->outPath` and `less->css_ini` folders are wratable it will work.

## Parameters

+ **development** - if false autocompilation is switched off.
+ **files** - coma separated list of `.less` files. Example: `'bootstrap.less,docs.less'`.
+ **path** - path to `.less` files from the host root
+ **formatter** - lessphp output formatter parameter. `lessjs` _(default)_, `compressed` and `classic` are available. For more detailes see http://leafo.net/lessphp/docs/#output_formatting
+ **outPath** - path where to save compiled `.css` files.
+ **css_ini** - path to file for saving lessphp cached information. 
	
	**Note!** if this parameter is empty or not defined less compilator will work and cached info will try to save into `less->path` folder for each `.less` file separatle with `.cache` extension. 

## Main features

+ Autocompile `.less` files when changes to .less files are done. More info on http://leafo.net/lessphp/docs/#compiling_automatically (`cachedCompile` used)
+ Puts last updated hash to `.css` file appending by 

	```
	$this->view->headLink()->appendStylesheet('path/to/css/file.css');
	```
	
	It will be appended like
	
	```
	<link href="path/to/css/file.css?123456" media="screen" rel="stylesheet" type="text/css" >
	```
	
	where **123456** - last updated hash from lessphp
	
## References

+ For more info on **lessphp** see http://leafo.net/lessphp/ , full documentation http://leafo.net/lessphp/docs/
+ For more information on *less* see http://lesscss.org/