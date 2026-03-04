<?php
/**
 * Plugin Name: MBC Google Analytics
 * Description: Loads Google Analytics (GA4) on production only.
 * Version: 1.0.0
 * Author: My Buddy Claude
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output the GA4 gtag.js snippet in the document head.
 *
 * Only loads on production (wp_get_environment_type() === 'production')
 * and skips admin pages.
 */
function mbc_google_analytics_head(): void {
	if ( is_admin() ) {
		return;
	}

	if ( wp_get_environment_type() !== 'production' ) {
		return;
	}

	$measurement_id = 'G-TK3MZH16N1';
	?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $measurement_id ); ?>"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', '<?php echo esc_js( $measurement_id ); ?>');
	</script>
	<?php
}
add_action( 'wp_head', 'mbc_google_analytics_head', 1 );
