<?php
/**
 * GoMage Social Connector Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.4.0
 * @since        Class available since Release 1.0.0
 */

class GoMage_Social_Block_Login extends Mage_Core_Block_Template 
{
    protected  $place;
	
	public function __construct() 
	{
		parent::__construct();
		
		if (! $this->getSession()->isLoggedIn() && Mage::helper('gomage_social')->isActive()) {
			$this->setTemplate('gomage/social/login.phtml');
		}
	}

	private function getSession() 
	{
		return Mage::getSingleton('customer/session');
	}
	
	public function setPlace($place) 
	{
		$this->place = $place;
		
		return $this;
	}
	
	public function getPlace() 
	{
		return $this->place;
	}
	
	public function getImage($service = '') 
	{
		if ($service) {
			$image = Mage::getStoreConfig('gomage_social/' . $service . '/image');
			
			if ($image) {
				return Mage::getBaseUrl('media') . 'gomage/social/' . $image;
			}
		}
		
		return false;
	}
	
	public function getText($service = '') 
	{
		if ($service) {
			$text = Mage::getStoreConfig('gomage_social/' . $service . '/text');
			
			if ($text) {
				return $text;
			}
		}
		
		return $this->__('Login');
	}

	public function getLoginType($service = '') 
	{
		return Mage::getStoreConfig('gomage_social/'. $service .'/' . $this->getPlace() . '_type');
	}

    public function getServiceBlock($type,  $is_last) 
	{
		$service = GoMage_Social_Model_Type::getTypeService($type);	
		
		switch ($service) {
			case 'facebook' :
			case 'google' :
				$block = $this->getLayout()->createBlock('gomage_social/login_' . $service);
				break;
			default : $block = $this->getLayout()->createBlock('gomage_social/login_service');		
		}	
		
		return $block->setData('is_last', $is_last)
			->setData('service', $service)
			->setPlace($this->getPlace());
    }
}
