<?php


/**
 * Plugin Name: WebP OTF
 * Description: Generate WebP images on the fly.
 * Version: 0.0.1
 * Author: Rafael Nowrotek
 * Author URI: https:/benignware.com
 * Network: true
 */
require 'lib.php';

use function benignware\webp_otf\get_image_webp_url;

add_filter( 'wp_content_img_tag', function( $filtered_image ) {
	$filtered_image = preg_replace_callback('/src=["\']([^"\']+)["\']/', function($matches) {
		$dest = sprintf('src="%s"', get_image_webp_url($matches[1]));

		return $dest;
	}, $filtered_image);

	return $filtered_image;
}, 10, 3 );

add_filter('wp_get_attachment_image_src', function ($image) {
	return [
		get_image_webp_url($image[0]),
		$image[1],
		$image[2]
	];
});

add_filter( 'wp_calculate_image_srcset', function($sources) {
	return array_map(function($item) {
		return array_merge($item, [
			'url' => get_image_webp_url($item['url'])
		]);
	}, $sources);;
});
