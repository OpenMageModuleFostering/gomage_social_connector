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
class GoMage_Social_AmazonController extends GoMage_Social_Controller_Social 
{
	protected $credentials;
	
	public function getSocialType() 
	{
		return GoMage_Social_Model_Type::AMAZON;
	}
	
	public function getCredentials() 
	{
		if (!$this->credentials) {
			$redirect_uri		= Mage::getUrl('gomage_social/amazon/callback', array('_secure' => true));		
			$this->credentials	= new GoMage_Amazon_Credentials(array(
				'client_id'		=> Mage::getStoreConfig('gomage_social/amazon/id'),
				'client_secret'	=> Mage::getStoreConfig('gomage_social/amazon/secret'),
				'redirect_uri'	=> $redirect_uri,
			));
		}
		
		return $this->credentials;
	}
	
	public function loginAction() 
	{
		try {		
			if ($this->getSession()->isLoggedIn()) {
				return $this->_redirectUrl();
			}
			
			$service		= new GoMage_Amazon_Service($this->getCredentials());
			$redirect_url	= $service->getAuthorizationUrl(array('scope' => 'profile'));		
			
			$url_backward	=
				($this->getRequest()->getParam('gs_url', ''))
					? Mage::helper('core')->urlDecode($this->getRequest()->getParam('gs_url'))
						: Mage::getBaseUrl();
					
			$_profile		= array(
				'url_backward' => $url_backward
			);
			
			Mage::getSingleton('core/session')->setGsProfile((object) $_profile);
		} catch (Exception $e) {
			$this->getSession()->addError($this->__($e->getMessage()));
			$redirect_url = null;
		}	
		
		return $this->_redirectUrl($redirect_url);
	}
	
	public function callbackAction() 
	{
		try {
			$code = $this->getRequest()->getParam('code');
			
			if ($this->getSession()->isLoggedIn() || empty($code)) {
				return $this->_redirectUrl();
			}
			
			$credentials	= $this->getCredentials();
			
			$credentials->setData('code', $code);
					
			$service		= new GoMage_Amazon_Service($credentials);
			$oauth_token	= $service->requestToken();
			
			if ($oauth_token->getAccessToken()) {
				Mage::getSingleton('core/session')->setData('oauth_token', $oauth_token);
				$service->getCredentials()->setData('oauth_token', $oauth_token);
				
				$profile = new Varien_Object($service->requestUserProfile()->getData('Profile'));
				
				if ($profile->getData('CustomerId')) {		
					$social_collection = Mage::getModel('gomage_social/entity')
						->getCollection()
						->addFieldToFilter('social_id', $profile->getData('CustomerId'))
						->addFieldToFilter('type_id', $this->getSocialType());
								
					if(Mage::getSingleton('customer/config_share')->isWebsiteScope()) {
						$social_collection->addFieldToFilter('website_id', Mage::app()->getWebsite()->getId());
					} 
					
					$social		= $social_collection->getFirstItem();
					$customer	= Mage::getModel('customer/customer');
						
					if (Mage::getSingleton('customer/config_share')->isWebsiteScope()) {
						$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
					}
					
					if ($social && $social->getId()){				
						$customer->load($social->getData('customer_id'));
						
						if ($customer->getId()){
							 $this->getSession()->loginById($customer->getId()); 
						}
					} else {						
						$_profile = array(
							'id'			=> $profile->getData('CustomerId'),
							'email'			=> $profile->getData('PrimaryEmail'),
							'first_name'	=> $profile->getData('Name'),
							'last_name'		=> $profile->getData('Name'),
						);			
						
						$_profile = (object) $_profile;						
						$customer->loadByEmail($_profile->email);
						
						if (!$customer->getId()){
							$customer = $this->createCustomer($_profile);
						}	
										
						if ($customer && $customer->getId()){						
							$this->createSocial($_profile->id, $customer->getId());							
							$this->getSession()->loginById($customer->getId());
						}				
					}
				}
			} else {
				$this->getSession()->addError($this->__('Could not connect to Amazon. Refresh the page or try again later.'));	
			}
		} catch(Exception $e) {
			$this->getSession()->addError($this->__($e->getMessage()));
		}
		
		$url_backward = Mage::getSingleton('core/session')->getGsProfile()->url_backward;	
		$this->_clear();
		
		return $this->_redirectUrl($url_backward);
	}
}