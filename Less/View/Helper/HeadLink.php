<?php
// This helper is made to add last update hash to css compiled from less.
require_once 'Zend/View/Helper/HeadLink.php';

class Less_View_Helper_HeadLink extends Zend_View_Helper_HeadLink
{
    public function createDataStylesheet(array $args)
    {
		$data = parent::createDataStylesheet($args);
		if(!$data)
			return false;
		// StyleSheets	
		// if there is not such registry
		if (!Zend_Registry::isRegistered('css')) {
			// return $data as is
			return $data;
		}
		$css_cnf = Zend_Registry::get("css");
		// converts to array 
		$attributes = (array) $data;
		$href = $attributes['href'];
		// if there is updated parameter..
		if($updated = $css_cnf->get($href, new Zend_Config(array(), true))->get('updated', '')){
			// ... add this parameter to href like ?<updated>
			$attributes['href'] .= '?'.$updated;
			// check for duplacations
			if ($this->_isDuplicateStylesheet($attributes['href'])) {
				return false;
			}
		}
		// and return data back like object
		return $this->createData($attributes);
	}
}
?>