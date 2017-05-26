<?php
/**
 * Plugin Name: Advanced Custom Fields: Auto JSON Sync
 * Plugin URI:  https://github.com/jawittdesigns/advanced-custom-fields-auto-json-sync
 * Description: Automatically update your local ACF JSON field groups
 * Version:     NEXT
 * Author:      Jason Witt
 * Author URI:  https://jawittdesigns.com
 * Donate link: https://github.com/jawittdesigns/advanced-custom-fields-auto-json-sync
 * License:     GPLv2
 * Text Domain: afc-ajs
 * Domain Path: /languages
 *
 * @link    https://github.com/jawittdesigns/advanced-custom-fields-auto-json-sync
 *
 * @package ACF_Auto_JSON_Sync
 * @version 0.1.1
 */

/**
 * Copyright (c) 2017 Jason Witt (email : contact@jawittdesings.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Main initiation class.
 *
 * @since 0.1.0
 */
final class ACF_Auto_JSON_Sync {

	/**
	 * Path of plugin directory.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @var string
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @var string
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @var ACF_Auto_JSON_Sync
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @return  ACF_WDS_Extensions A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 */
	protected function __construct() {
		$this->basename     = plugin_basename( __FILE__ );
		$this->path         = plugin_dir_path( __FILE__ );
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 */
	public function plugin_classes() {
		$this->include_file( 'includes/class-directory-structure' );
		new ACF_AJS_Directory_Structure;
		$this->include_file( 'includes/class-update-field-groups' );
		new ACF_AJS_Update_Field_Groups;
	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Activate the plugin.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 */
	public function _activate() {
		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Init hooks
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 */
	public function init() {

		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Load translated strings for plugin.
		load_plugin_textdomain( 'afc-ajs', false, dirname( $this->basename ) . '/languages/' );

		// Initialize plugin classes.
		$this->plugin_classes();
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @return boolean True if requirements met, false if not.
	 */
	public function check_requirements() {

		// Bail early if plugin meets requirements.
		if ( $this->meets_requirements() ) {
			return true;
		}

		// Add a dashboard notice.
		add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

		// Deactivate our plugin.
		add_action( 'admin_init', array( $this, 'deactivate_me' ) );

		// Didn't meet the requirements.
		return false;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 */
	public function deactivate_me() {

		// We do a check for deactivate_plugins before calling it, to protect
		// any developers from accidentally calling it too early and breaking things.
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( $this->basename );
		}
	}

	/**
	 * Check that all plugin requirements are met.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @return boolean True if requirements are met.
	 */
	public function meets_requirements() {

		// Include plugin.php file to use the is_plugin_active() function.
		if ( file_exists( ABSPATH . 'wp-admin/includes/plugin.php' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check for Advanced custom fileds pro.
		if ( ! is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {

			return false;
		}

		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 */
	public function requirements_not_met_notice() {

		// compile default message.
		$default_message = sprintf(
			__( 'The Advanced Custom Fields: Auto JSON Sync plugin requires the Advanced Custom Fields Pro plugin to be active.', 'afc-ajs' ),
			admin_url( 'plugins.php' )
		);

		// add details if any exist.
		if ( ! empty( $this->activation_errors ) && is_array( $this->activation_errors ) ) {
			$details = '<small>' . implode( '</small><br /><small>', $this->activation_errors ) . '</small>';
		}

		// output errors.
		?>
		<div id="message" class="error">
			<p><?php echo $default_message; // WPCS: XSS ok. ?></p>
		</div>
		<?php
	}

	/**
	 * Include a file from the includes directory.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @param  string $filename Name of the file to be included.
	 * @return boolean          Result of include call.
	 */
	public static function include_file( $filename ) {

		$file = self::dir( $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path.
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}
}

/**
 * Grab the ACF_Auto_JSON_Sync object and return it.
 * Wrapper for ACF_Auto_JSON_Sync::get_instance().
 *
 * @since 0.1.0
 * @author Jason Witt
 *
 * @return ACF_Auto_JSON_Sync  Singleton instance of plugin class.
 */
function afc_ajs() {
	return ACF_Auto_JSON_Sync::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( afc_ajs(), 'init' ) );

// Activation and deactivation.
register_activation_hook( __FILE__, array( afc_ajs(), '_activate' ) );
