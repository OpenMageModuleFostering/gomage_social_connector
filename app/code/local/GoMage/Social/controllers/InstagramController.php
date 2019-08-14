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
class GoMage_Social_InstagramController extends GoMage_Social_Controller_SocialNoMail
{
    protected $credentials;
	
	public function getSocialType()
    {
        return GoMage_Social_Model_Type::INSTAGRAM;
    }
	
	public function getCredentials()
    {
        if (!$this->credentials) {
            $redirect_uri		= Mage::getUrl('gomage_social/instagram/callback', array('_secure' => true));
            $this->credentials	= new GoMage_Instagram_Credentials(array(
                'client_id'     => Mage::getStoreConfig('gomage_social/instagram/id'),
                'client_secret' => Mage::getStoreConfig('gomage_social/instagram/secret'),
                'redirect_uri'  => $redirect_uri,
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
			
            $service		= new GoMage_Instagram_Service($this->getCredentials());
            $redirect_url	= $service->getAuthorizationUrl(array('scope' => 'basic'));
			
			$url_backward	=
				($this->getRequest()->getParam('gs_url', ''))
					? Mage::helper('core')->urlDecode($this->getRequest()->getParam('gs_url'))
						: Mage::getBaseUrl();
							
			$_profile		= array(
				'url_backward'		=> $url_backward,
				'url_check_email'	=> Mage::getUrl('gomage_social/instagram/checkEmail', array('_secure' => Mage::helper('gomage_social/url')->isSecure($url_backward))),
				'url_email_close'	=> Mage::getUrl('gomage_social/instagram/emailClose', array('_secure' => Mage::helper('gomage_social/url')->isSecure($url_backward))),
				'type_id'			=> $this->getSocialType()
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
            
			$service		= new GoMage_Instagram_Service($credentials);
            $oauth_token	= $service->requestToken();
			
            if ($oauth_token->getAccessToken()) {
				Mage::getSingleton('core/session')->setData('oauth_token', $oauth_token);
                $service->getCredentials()->setData('oauth_token', $oauth_token);
                
				$profile = new Varien_Object($service->requestUserProfile()->getData('data'));
                
				if ($profile->getData('id')) {
					$social_collection = Mage::getModel('gomage_social/entity')
						->getCollection()
						->addFieldToFilter('social_id', $profile->getData('id'))
						->addFieldToFilter('type_id', $this->getSocialType());
					
					if (Mage::getSingleton('customer/config_share')->isWebsiteScope()) {
						$social_collection->addFieldToFilter('website_id', Mage::app()->getWebsite()->getId());
					}
					
					$social = $social_collection->getFirstItem();
					
					if ($social && $social->getId()) {
						if ($social->social_id == $profile->getData('id')) {
							$customer = Mage::getModel('customer/customer');
							
							if (Mage::getSingleton('customer/config_share')->isWebsiteScope()) {
								$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
							}
							
							$customer->load($social->getData('customer_id'));
	
							if ($customer && $customer->getId()) {
								if (!$customer->getConfirmation()) {
									$this->getSession()->loginById($customer->getId());
								} else {
									$this->getSession()->addError($this->__('This account is not confirmed.'));
								}
							}
						}
					}  else {
						$_profile = array_merge((array) Mage::getSingleton('core/session')->getGsProfile(), array(
							'id'			=> $profile->getData('id'),
							'name'			=> $profile->getData('username') . ' ' . $profile->getData('username'),
							'first_name'	=> $profile->getData('username'),
							'last_name'		=> $profile->getData('username'),
						));
						
						Mage::getSingleton('core/session')->setGsProfile((object) $_profile);
					}
                }
            } else {
                $this->getSession()->addError($this->__('Could not connect to Instagram. Refresh the page or try again later.'));
            }
        } catch (Exception $e) {
            $this->getSession()->addError($this->__($e->getMessage())); 
        }  
		
		return $this->_redirectUrl();      
    }
}