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
class GoMage_Social_Model_Adminhtml_System_Config_Source_Services
{
    public function toOptionArray()
    {
        $helper = Mage::helper('gomage_social');

        return array(
            array('value' => '', 'label' => ''),
            array('value' => GoMage_Social_Model_Type::FACEBOOK, 'label' => $helper->__('Facebook')),
            array('value' => GoMage_Social_Model_Type::LINKEDIN, 'label' => $helper->__('LinkedIn')),
            array('value' => GoMage_Social_Model_Type::GOOGLE, 'label' => $helper->__('Google')),
            array('value' => GoMage_Social_Model_Type::TWITTER, 'label' => $helper->__('Twitter')),
            array('value' => GoMage_Social_Model_Type::TUMBLR, 'label' => $helper->__('Tumblr')),
            array('value' => GoMage_Social_Model_Type::REDDIT, 'label' => $helper->__('Reddit')),
            array('value' => GoMage_Social_Model_Type::AMAZON, 'label' => $helper->__('Amazon')),
            array('value' => GoMage_Social_Model_Type::INSTAGRAM, 'label' => $helper->__('Instagram')),
        );
    }
}