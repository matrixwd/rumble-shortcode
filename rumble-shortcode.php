<?php
/*
Plugin Name: Rumble Shortcodes
Description: Adds a custom shortcode to get Rumble Videos.. ex. [rumblevid url="https://rumble.com/embed/vmmi4h/?pub=4" poster=false overlay="https://site.com/image.jpg"]
Author: Eric Murphy, Jack Mullen
Version: 1.0.0
Donate link: https://matrixwebdesigers.com/donate
License: GPLv3 or later
Author URI: https://matrixwebdesigers.com
Plugin URI:        https://matrixwebdesigners.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

if (!defined('ABSPATH')) die('No direct access allowed');

add_shortcode('rumblevid','rumble_video_control');

if ( ! function_exists( 'rumble_video_control' ) ) {
	function rumble_video_control ($atts =  array()) {

		// set up default parameters
		$src_url = shortcode_atts(array(
			'url' => '',
			'overlay' 	=> '',
			'poster' 	=> '',
			'height' 	=> '',
			'width' 	=> ''
		), $atts);
		
		$url = $src_url['url']; 
		//$url="https://rumble.com/embed/vlhru1/?pub=4";

		$urlmatch = array();
                
		$htmlString = wp_remote_retrieve_body( wp_remote_get($url) ); // no need to escape entities, esc url below
		
		if ( ! is_wp_error( $htmlString ) ) {
			echo wp_remote_retrieve_body( $htmlString );
		}
		
		#$reg='#\{\"mp4\"\:\{\"url\"\:\"(https\:.*?\")#';
		#for video tag 
		#$reg='#\<video.*?(src\=\".*?\".*?(poster\=".*?\"))#';
		
		$reg ='#\{\"mp4\"\:\{\"url\"\:\"(https\:.*?)\".*?\"i\"\:\"(https\:.*\.jpg)\"#';
		preg_match($reg,$htmlString,$urlmatch);

		$reg = "#http[s]*\:\/\/rumble\.com\/embed\/.*?\/?pub#";
		preg_match($reg,$url,$check);
		
		if ( !empty($src_url['url']) && isset($src_url['url']) && isset($check[0]) ) {
			
			$rumble_source = (string)$urlmatch[1];
			$vid_source = esc_url($rumble_source); // esc the url

			if($src_url['poster'] === 'true' || $src_url['poster'] == 1 ){ // if this is set to true then we dont look for one uploaded
				$poster_img = (string)$urlmatch[2];
				$poster = esc_url( $poster_img ); // esc the url
			}else if( !empty($src_url['overlay']) && isset($src_url['overlay']) ){
				$poster_img = $src_url['overlay'];
				$poster = esc_url( $poster_img ); // esc the url
			}else{
				$poster = ''; // nothing is set
			}
			
			if( !empty($src_url['height']) && isset($src_url['height']) ) {
				$height = 'height: ' . sanitize_text_field($src_url['height']) . ';'; // sanitize height
			}else{
				$height = 'height: 0;'; // if none we insert it
			}
			
			if( !empty($src_url['width']) && isset($src_url['width']) ) {
				$width = 'width: ' . sanitize_text_field($src_url['width']) . ';'; // sanitize width
			}else{
				$width = 'width: 100%;'; // if none we insert it
			}
			
			$output = '<div style="position: relative; padding-bottom: calc(var(--aspect-ratio, .5625) * 100%); ' . esc_attr($height) . ' ' . esc_attr($width) . ' margin: 0 auto;" class="rumble-video-container"><video controls controlsList="nodownload" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #000;" preload="metadata" src="' . esc_url($vid_source) . '" poster="' . esc_url($poster) . '"></video></div>';
			
		}else{
			$output = '<p><b>Error</b> - ' . esc_url($url) . ' is not an embed code. Be sure you entered the Rumble EMBED code. If <b>poster="false"</b> or <b>"0"</b> please enter full url to image overlay starting with http(s) unless image is self hosted, then it can be a relative url.</p>'; 
			return $output;
		}
		
		return $output;
	}
}

require 'inc/updates/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://matrixwebdesigners.com/updates/plugins/rumble-shortcode/info.json',
		__FILE__, //Full path to the main plugin file or functions.php.
		'rumble-video-control'
	);