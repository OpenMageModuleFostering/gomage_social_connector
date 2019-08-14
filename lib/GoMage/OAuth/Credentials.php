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
 
class GoMage_OAuth_Credentials extends Varien_Object 
{	
	public function _construct() 
	{
		if (!$this->getClientId()) {
			throw new Exception('client_id is required');
		} else if (!$this->getClientSecret()) {
			throw new Exception('client_secret is required');
		} else if (!$this->getRedirectUri()) {
			throw new Exception('redirect_uri is required');
		}
    }
}