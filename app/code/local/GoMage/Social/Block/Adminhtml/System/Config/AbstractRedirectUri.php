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
 * @since        Class available since Release 1.2.0
 */ 
 
abstract class GoMage_Social_Block_Adminhtml_System_Config_AbstractRedirectUri extends Mage_Adminhtml_Block_System_Config_Form_Field {
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {		
		$store = Mage::app()->getRequest()
			->getParam('store');
			
		$store_id = Mage::app()->getStore($store)
			->getStoreId();
		
		$url = Mage::getUrl(
			'gomage_social/' . $this->getTypeService() . '/callback', 
			array(
				'_secure'	=> true,
				'_store'	=> ($store_id ? $store_id : 1),
				'_nosid'	=> true,
			)
		);
		
		return '<span style="font-weight:bold">' . $url . '</span>';
	}
	
	abstract public function getTypeService();	 
}