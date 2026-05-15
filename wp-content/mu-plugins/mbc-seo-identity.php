<?php
/**
 * Plugin Name: MBC SEO Identity
 * Description: Configures the `sameAs` URLs for the Person and Organization JSON-LD schemas output by MBC SEO.
 * Version: 1.0.0
 * Author: My Buddy Claude
 *
 * @package MBC_SEO
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical profile URLs for the primary Person entity (Brandon Dove).
 */
add_filter(
	'mbc_seo_person_same_as',
	static function (): array {
		return array(
			'https://github.com/brandondove',
			'https://linkedin.com/in/brandondove',
			'https://x.com/brandondove',
			'https://brandondove.com',
		);
	}
);
