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
 * @since        Class available since Release 1.1.0
 */ 
abstract class GoMage_Social_Controller_SocialNoMail extends GoMage_Social_Controller_Social 
{
	protected function createCustomer($profile) 
	{
		$customer = Mage::getModel('customer/customer');
		$password =  $customer->generatePassword(8);

		if (is_array($profile)) {
			$profile = (object) $profile;
		}
		
        $customer->setData('firstname', $profile->first_name)
        	->setData('lastname', $profile->last_name)
        	->setData('email', $profile->email)
        	->setData('password', $password)
			->setData('password_confirmation', $password)
        	->setConfirmation($password);
        
        $errors = $customer->validate();

        if (is_array($errors) && count($errors)) {
        	$this->getSession()->addError(implode(' ', $errors));
			
        	return false; 
        }

        $customer->save();
		$customer->sendNewAccountEmail(
			'confirmation',
			Mage::getSingleton('core/session')->getBeforeAuthUrl(),
			Mage::app()->getStore()
				->getId()
		);
		
        Mage::getSingleton('core/session')->addSuccess(
			$this->__(
				'Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', 
				Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())
			)
		);

        return $customer;    
	}

    public function checkEmailAction() 
	{ 
        $message  = array();
        $message['redirect'] = null;
		
        if ($customer_email = $this->getRequest()->getParam('email')) {
            $customer = Mage::getModel("customer/customer");
            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer->loadByEmail($customer_email);
			
            if ($profile = Mage::getSingleton('core/session')->getGsProfile()) {
				if ($customer->getId()) {		
					$message['redirect'] =  Mage::getUrl('customer/account/login', array('_secure' => true));
					Mage::getSingleton('core/session')->addNotice('There is already an account with this email address. We suggest using the standard login form.');
				} else {
					$social_collection = Mage::getModel('gomage_social/entity')
						->getCollection()
						->addFieldToFilter('social_id', $profile->id)
						->addFieldToFilter('type_id', $profile->type_id);
					
					if(Mage::getSingleton('customer/config_share')->isWebsiteScope()) {
						$social_collection->addFieldToFilter('website_id', Mage::app()->getWebsite()->getId());
					}
					
					$social		= $social_collection->getFirstItem();	
					$customer	= null;
					
					if ($social && $social->getId()) {
						$customer = Mage::getModel('customer/customer');
						
						if (Mage::getSingleton('customer/config_share')->isWebsiteScope()) {
							$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
						}
						
						$customer->load($social->getData('customer_id'));
					}
					
					if ($customer && $customer->getId()) {
						if (!$customer->getConfirmation()) {
							$this->getSession()->loginById($customer->getId());
						}
					} else {
						$customer = Mage::getModel('customer/customer');
						
						if (Mage::getSingleton('customer/config_share')->isWebsiteScope()) {
							$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
						}
					
						$name = explode(" ", $profile->name);
						$profile->email = $customer_email;
						$profile->first_name =  $name[0];
						
						if (isset($name[1])) {
							$profile->last_name = $name[1];
						} else {
							$profile->last_name = $name[0];
						}
							
						$customer->loadByEmail($profile->email);
					
						if (!$customer->getId()) {
							$customer = $this->createCustomer($profile);
						}
					
						if ($customer && $customer->getId()) {
							$this->createSocial($profile->id, $customer->getId());
							$customer->setConfirmation(true);
							
							if (!$customer->getConfirmation()) {
								$this->getSession()->loginById($customer->getId());
							}
						}	
					}		
				}
				
				$this->_clear();
			}
		}
		
		if (!$message['redirect']) {
			$message['success'] = true;
		}
		
		return $this->getResponse()->setBody(Zend_Json::encode($message));
	}

	public function emailCloseAction() 
	{
		$this->_clear();
	}
}