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
 * @since        Class available since Release 1.2.0
 */ 
 
class GoMage_Amazon_Service {
	
	const SERVICE_ENDPOINT			= 'https://www.amazon.com/ap';
	const AUTHORIZATION_ENDPOINT	= 'https://www.amazon.com/ap/oa';
	const ACCESS_TOKEN_ENDPOINT		= 'https://www.amazon.com/ap/oatoken';
	
	public static $return_request_error	= false;
	
	public $http_info		= array();
	public $http_response	= '';
	public $useragent		= 'Amazon OAuth';
	public $connecttimeout	= 30;
	public $timeout			= 30;
	public $ssl_verifypeer	= false;
	
	protected $credentials;
	
	public function __construct(GoMage_Amazon_Credentials $credentials) {
		$this->credentials = $credentials;
	}
	
	public function getCredentials() {
		return $this->credentials;
	}
	
	public function request($url, $method, $parameters  = array(), $extraHeaders = array()) {
		$this->http_info		= array();
		$this->http_response	= '';
		$ci = curl_init();
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADER, false);
		
		switch ($method) {
			case 'GET' :				
				if (!empty($parameters)) {
					$url .= '?' . http_build_query($parameters);
				}
			break;
			case 'POST' :
				curl_setopt($ci, CURLOPT_POST, true);
				
				if (!empty($parameters)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $parameters);
				}
			break;	
		}
		
		curl_setopt($ci, CURLOPT_HTTPHEADER, $extraHeaders);
		curl_setopt($ci, CURLOPT_URL, $url);
		
		$this->http_response	= curl_exec($ci);
		$this->http_info		= curl_getinfo($ci);
		$error					= curl_error($ci);
		
		curl_close($ci);
		
		if ($error) {
			throw new Exception($error);
		} 
			
		if ($this->http_info['http_code'] !== 200) {
			switch (self::$return_request_error) {
				case false :
					throw new Exception('Could not connect to Amazon. Refresh the page or try again later.');
				break;
				case true :
					throw new Exception(
						"Amazon OAuth request failed: "		. 
						"\t\r\n HTTP code: "		. $this->http_info['http_code'] . 
						"\t\r\n HTTP response: "	. $this->http_response
					);
				break;
			}	
		}
		
		return $this->http_response;
	}
	
	public function getAuthorizationUrl($parameters = array()) {
		$parameters = array_merge(
			$parameters,
			array(
				'type'          => 'web_server',
                'client_id'     => $this->credentials->getClientId(),
                'redirect_uri'  => $this->credentials->getRedirectUri(),
                'response_type' => 'code',
			)
		);
		
		return self::AUTHORIZATION_ENDPOINT . '?' . http_build_query($parameters);
	}
	
	public function getTokenUrl($parameters = array()) {
		$parameters = array_merge(
			$parameters,
			array(
				'code'          => $this->credentials->getCode(),
				'client_id'     => $this->credentials->getClientId(),
				'client_secret' => $this->credentials->getClientSecret(),
				'redirect_uri'  => $this->credentials->getRedirectUri(),
				'grant_type'    => 'authorization_code',
			)
		);
		
		return self::ACCESS_TOKEN_ENDPOINT . '?' . http_build_query($parameters);
	}
	
	public function requestToken($parameters = array()) {
		$parameters = array_merge(
			$parameters,
			array(
				'code'          => $this->credentials->getCode(),
				'client_id'     => $this->credentials->getClientId(),
				'client_secret' => $this->credentials->getClientSecret(),
				'redirect_uri'  => $this->credentials->getRedirectUri(),
				'grant_type'    => 'authorization_code',
			)
		);
		
		$this->request(self::ACCESS_TOKEN_ENDPOINT, 'POST', $parameters);
			
		return new Varien_Object(Zend_Json::decode($this->http_response));
	}
	
	public function requestUserProfile($parameters = array()) {	
		$parameters = array_merge(
			$parameters,
			array(
				'access_token'  => $this->credentials
					->getOauthToken()
					->getAccessToken(),
			)
		);	
		
		$this->request(self::SERVICE_ENDPOINT . '/user/profile', 'GET', $parameters);
			
		return new Varien_Object(Zend_Json::decode($this->http_response));
	}
}