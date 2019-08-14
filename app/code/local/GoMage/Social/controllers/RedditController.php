<?php
/**
 * GoMage Social Connector Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.2.0
 * @since        Class available since Release 1.1.0
 */

require_once (Mage::getBaseDir('lib') . DS . 'GoMage' . DS . 'Reddit' . DS . 'Client.php');
require_once (Mage::getBaseDir('lib') . DS . 'GoMage' . DS . 'Reddit' . DS . 'GrantType/IGrantType.php');
require_once (Mage::getBaseDir('lib') . DS . 'GoMage' . DS . 'Reddit' . DS . 'GrantType/AuthorizationCode.php');

class GoMage_Social_RedditController extends GoMage_Social_Controller_SocialNoMail {

 protected  $authorizeUrl	= 'https://ssl.reddit.com/api/v1/authorize';
 protected  $accessTokenUrl	= 'https://ssl.reddit.com/api/v1/access_token';

    public function getSocialType() {
        return GoMage_Social_Model_Type::REDDIT;
    }

    public function loginAction() {
		$callback_url	= Mage::getUrl('gomage_social/reddit/callback', array('_secure' => true));
		$client			= new OAuth2\Client(
			Mage::getStoreConfig('gomage_social/reddit/id'), 
			Mage::getStoreConfig('gomage_social/reddit/secret'), 
			OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC
		);
		
		$authUrl = $client->getAuthenticationUrl(
			$this->authorizeUrl, 
			$callback_url, 
			array(
				"scope"	=> "identity", 
				"state"	=> "SomeUnguessableValue"
			)
		);
		
		return $this->_redirectUrl($authUrl);
    }

    public function callbackAction() {
        $callback_url	= Mage::getUrl('gomage_social/reddit/callback', array('_secure' => true));
        $client			= new OAuth2\Client(
			Mage::getStoreConfig('gomage_social/reddit/id'), 
			Mage::getStoreConfig('gomage_social/reddit/secret'), 
			OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC
		);
        $params = array(
			"code"			=> $this->getRequest()->getParam('code'), 
			"redirect_uri"	=> $callback_url
		);

        if ($params['code']) {
			$response			= $client->getAccessToken($this->accessTokenUrl, "authorization_code", $params);
			$accessTokenResult	= $response["result"];
			
			$client->setAccessToken($accessTokenResult["access_token"]);
			$client->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);
			
			$response	= (object) $client->fetch("https://oauth.reddit.com/api/v1/me.json");
			$profile	= null;
			
			switch ($response->code) {
				case 200 :
					$profile = (object) $response->result;
					break;
				default :
					$this->getSession()->addError($this->__('Could not connect to Reddit. Refresh the page or try again later.'));
					
					return $this->_redirectUrl();
			}
	
			if ($profile) {
				if ($profile->id) {
					$social_collection = Mage::getModel('gomage_social/entity')
						->getCollection()
						->addFieldToFilter('social_id', $profile->id)
						->addFieldToFilter('type_id', GoMage_Social_Model_Type::REDDIT);
					
					if (Mage::getSingleton('customer/config_share')->isWebsiteScope()) {
						$social_collection->addFieldToFilter('website_id', Mage::app()->getWebsite()->getId());
					}
					
					$social = $social_collection->getFirstItem();
					
					if ($social && $social->getId()) {
						if ($social->social_id == $profile->id) {
							$customer = Mage::getModel('customer/customer');
							
							if (Mage::getSingleton('customer/config_share')->isWebsiteScope()) {
								$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
							}
							
							$customer->load($social->getData('customer_id'));
	
							if ($customer && $customer->getId()) {
								if (!$customer->getConfirmation()) {
									$this->getSession()->loginById($customer->getId());
								} else{
									$this->getSession()->addError($this->__('This account is not confirmed.'));
								}
							}
						}
					} else {
						$profile->url = Mage::getUrl('gomage_social/reddit/checkEmail', array('_secure' => true));
						$profile->urlEmailClose = Mage::getUrl('gomage_social/twitter/emailClose', array('_secure' => true));
						$profile->type_id =  GoMage_Social_Model_Type::REDDIT;
						
						Mage::getSingleton('core/session')->setGsProfile($profile);
					}
				}
			}
        }

        return $this->_redirectUrl();
    }
}