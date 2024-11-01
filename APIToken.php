<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements. See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * 
 *		http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 */

class APIToken {
	// URL used for authentication
	const ENDPOINT = 'https://id-wave.adobe.com/identity/1.0/auth/apitoken.xml';

	// Adobe ID and password of user with permission to publish to a feed.
	private $username = '';
	private $password = '';

	// API token that will be returned from the authorization call
	private $apitoken = ''; 

	private $tokenFound = false;
	
	private static $instance = null;

	private function __construct($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}	
	
	public static function getInstance($username = '', $password = '') {
		if(self::$instance == null) {
			self::$instance = new APIToken($username, $password);
			self::$instance->execute();
		}
		return self::$instance;
	}
	
	public function setToken($token) {
		$this->apitoken = $token;
	}
	
	public function getToken() {
		return $this->apitoken;
	}
	
	public function execute() {
		$handle = $this->openSession();

		try {
			$result = $this->sendRequest($handle);
			$this->parseResult($result);
			//$this->writeTokenToFile();
		} catch (WaveException $e) {
			$this->closeSession($handle);
			throw $e;			
		}

		$this->closeSession($handle);
		return $this->apitoken;
	}

	public function writeTokenToFile() {
		$handle = fopen('apiToken.txt', 'w');
		fputs($handle, $this->apitoken);
		fclose($handle);
	}
	
	// parse the result XML	
	public function parseResult($result) {
		if (! ($parser = xml_parser_create()) ) { 
			  die ('Cannot create parser');
		}
		xml_set_object($parser, $this);
		xml_set_element_handler($parser, 'start_element','end_element');
		xml_set_character_data_handler($parser, 'content');
		if (!xml_parse($parser, $result, true)) {
		  $reason = xml_error_string(xml_get_error_code($parser));
		  $reason .= xml_get_current_line_number($parser);
		  throw new WaveException($reason);
		}
		
		xml_parser_free($parser);
		return $result;
	}
	
	private function openSession() {
		return curl_init();
	}
	
	private function closeSession($handle) {
		curl_close($handle);
	}
	
	private function sendRequest($handle) {
		curl_setopt($handle, CURLOPT_URL, self::ENDPOINT);
		curl_setopt($handle, CURLOPT_POST, 1);
		curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($handle, CURLOPT_USERPWD, $this->username.':'.$this->password);    
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1); 
		
		$result = curl_exec($handle);
		if (!$result) {
			$error = curl_errno($handle) . " - " . curl_error($handle);
			throw new WaveException("Could not execute request to URL ".self::ENDPOINT.": ".$error);
		}
		
		$info = curl_getinfo($handle);
		$http_code = $info['http_code'];
		
		if($http_code == '200') {
			return $result;
		} 
		throw new WaveException('Unexpected HTTP Code: '.$http_code);
	}
	
	
	private function start_element($parser, $name, $attribs) {
	  if ($name == 'APITOKEN') {
		    $this->tokenFound = true;
	  }
	}
	
	private function end_element($parser, $name) {
	}
	
	private function content($parser, $data) {
		if($this->tokenFound == true) {
			APIToken::getInstance()->setToken($data);
			$this->tokenFound = false;
		}
	}
}

?>