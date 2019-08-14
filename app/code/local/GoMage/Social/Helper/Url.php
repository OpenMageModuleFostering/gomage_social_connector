<?php

class GoMage_Social_Helper_Url extends Mage_Core_Helper_Abstract
{
	public function isSecure($url = null)
	{			
		return 
			($url)
				? (parse_url($url, PHP_URL_SCHEME) == 'https')
					: (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
	}
	
	public function getUrl($route = '', array $params = array())
	{		
		$this->detectProtocol();
		
		$params['_secure'] = $this->isSecure();;
		
		return Mage::getUrl($route, $params);
	}
}