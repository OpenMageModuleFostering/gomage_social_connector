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
 
class GoMage_Instagram_Service extends GoMage_OAuth_Service
{
	const SERVICE_ENDPOINT			= 'https://api.instagram.com/v1/users/self';
	const AUTHORIZATION_ENDPOINT	= 'https://api.instagram.com/oauth/authorize';
	const ACCESS_TOKEN_ENDPOINT		= 'https://api.instagram.com/oauth/access_token';
	
	public $useragent		= 'Instagram OAuth';
}