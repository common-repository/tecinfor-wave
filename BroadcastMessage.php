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

class BroadcastMessage {
	// URL used for notification
	const ENDPOINT = 'https://p000-wave.adobe.com/notificationgateway/1.0/notification';

	private $topic = '';
	private $message = '';
	private $link = '';
	private $apitoken = '';
	
	public function __construct($apitoken, $topic, $message, $link) {
		$this->apitoken = $apitoken;
		$this->topic = $topic;
		$this->message = $message;
		$this->link = $link;
	}
	
	public function send() {
		$notification_properties = array(
		  'X-apitoken'  => $this->apitoken,
		  'topic'       => $this->topic,
		  'message'     => $this->message,
		  'link'        => $this->link,
		  //'image'    => $imagefile,
		  //'imagetype'    => $imagemimetype, 
		  // if using an image url instead of specifying a thumbnail, disable the above two
		  // parameters.
		  // 'imageurl' => $imageurl,
		  // 'accesstoken' => $access_token
		  );
		
		$curl_handle = curl_init(); 
		curl_setopt($curl_handle, CURLOPT_URL, self::ENDPOINT);
		curl_setopt($curl_handle, CURLOPT_POST, 1);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $notification_properties);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1); 
		$result = curl_exec($curl_handle);
		$info = curl_getinfo($curl_handle);
		$http_code = $info['http_code'];
		curl_close($curl_handle); 
		return $http_code;
	}
}

?>