<?php
/**
 * Bootstrapper.
 *
 * @since   1.0.0
 * @package ACF_Auto_JSON_Sync
 */

$use_base  = true;

// Get our tests directory.
$_tests_dir = ( getenv( 'WP_TESTS_DIR' ) ) ? getenv( 'WP_TESTS_DIR' ) : '/tmp/wordpress-tests-lib';

// Include our tests functions.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually require our plugin for testing.
 *
 * @since 1.0.0
 */
function _manually_load_pro_dev_tools_plugin() {

	// Add the theme.
	switch_theme( '_s' );

	// Plugins to activate.
	$active_plugins = array(
		'advanced-custom-fields-auto-json-sync/advanced-custom-fields-auto-json-sync.php',
		'advanced-custom-fields-pro/acf.php',
	);

	// Require our plugin.
	if ( file_exists( dirname( dirname( __FILE__ ) ) . '/advanced-custom-fields-auto-json-sync.php' ) ) {
		require dirname( dirname( __FILE__ ) ) . '/advanced-custom-fields-auto-json-sync.php';
	}

	// Update the active_plugins options with the $active_plugins array.
	update_option( 'active_plugins', $active_plugins );
}

// Inject in our plugin.
tests_add_filter( 'muplugins_loaded', '_manually_load_pro_dev_tools_plugin' );

// Include the main tests bootstrapper.
require $_tests_dir . '/includes/bootstrap.php';

if ( $use_base ) {
	require dirname( __FILE__ ) . '/base.php';
}
