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

class GoMage_Social_Block_Login_Service extends GoMage_Social_Block_Login {
	
	public function __construct() {
		parent::__construct();	
		$this->setTemplate('gomage/social/login/service.phtml');
	}
}
