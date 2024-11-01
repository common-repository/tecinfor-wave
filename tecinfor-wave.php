<?php
/*
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
 
/**
 * @package Grobmeier_Wave
 * @author Christian Grobmeier
 * @version 1.3
 */
/*
Plugin Name: tecinfor-wave - By Michel Melo
Plugin URI: http://tecinfor.net
Description: This is a plugin for feeding Adobe Wave. It sends a request to the feed when a blog post has been published.
Author: Christian Grobmeier
Version: 1.3
Author URI:http://www.grobmeier.de/?ref:tecinfor.net
*/
include('APIToken.php');
include('WaveException.php');
include('BroadcastMessage.php');

// admin actions
if ( is_admin() ){
  add_action('admin_menu', 'tecinfor_wave_plugin_menu');
  add_action( 'admin_init', 'register_mysettings' );
} 

// whitelist options
function register_mysettings() { 
  register_setting( 'tecinfor-wave-option-group', 'wave_password' );
  register_setting( 'tecinfor-wave-option-group', 'wave_username' );
  register_setting( 'tecinfor-wave-option-group', 'wave_topic' );
}

function tecinfor_wave_plugin_menu() {
	add_options_page(
		'tecinfor-Wave Plugin Options', 
		'tecinfor-Wave Plugin Plugin', 
		8, 
		'tecinfor-wave-plugin', 
		'tecinfor_wave_plugin_options');
}

function tecinfor_wave_plugin_options() {
	echo '<div class="wrap">';
	echo '<h2>tecinfor Wave Plugin - Opções</h2>';
	echo '<form method="post" action="options.php">';
	wp_nonce_field('update-options');
	echo '<table class="form-table">';
	echo '<tr valign="top">';
	echo '<th scope="row">API token</th>';
	echo '<td><input type="text" name="wave_topic" value="';
	echo get_option('wave_topic');
	echo '" /></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '<th scope="row">Wave Username</th>';
	echo '<td><input type="text" name="wave_username" value="';
	echo get_option('wave_username');
	echo '" /></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '<th scope="row">Password</th>';
	echo '<td><input type="text" name="wave_password" value="';
	echo get_option('wave_password');
	echo '" /></td>';
	echo '</tr>';
	echo '</table>';
	echo '<input type="hidden" name="action" value="update" />';
	echo '<input type="hidden" name="page_options" value="wave_topic,wave_username,wave_password" />';
	settings_fields( 'tecinfor-wave-option-group' );
	echo '<p class="submit">';
	echo '<input type="submit" class="button-primary" value="';
	echo _e('Save Changes');
	echo '" />';
	echo '</p>';
	echo '</form>';
	echo '</div>';
}


add_action ('publish_post', 'tecinfor_wave_send');

function tecinfor_wave_send() {
	$username = get_option('wave_username');
	$password = get_option('wave_password');
	$topic = get_option('wave_topic');
	if($username == '' || $password == '' || $topic == '') {
		// no action - error would be nice
		return;
	}
	
	query_posts('showposts=1');
	$posts = get_posts('numberposts=1'); 
	$title = $posts[0]->post_title;
	$link = $posts[0]->guid;
	
	$token = APIToken::getInstance($username, $password);
	$tokenValue = $token->getToken();
	
	$message = new BroadcastMessage(
					$tokenValue, 
					$topic, 
					$title, $link);
	$code = $message->send();
}

?>
