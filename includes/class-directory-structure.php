<?php
/**
 * Advanced Custom Fields: Auto JSON Sync Directory Structure.
 *
 * @since 0.1.0
 * @package ACF_Auto_JSON_Sync
 */

/**
 * Advanced Custom Fields: Auto JSON Sync Directory Structure.
 *
 * @since 0.1.0
 * @author Jason Witt
 */
class ACF_AJS_Directory_Structure {

	/**
	 * The ACF JSON directory path.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @var string
	 */
	protected $acf_json_dir;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @return void
	 */
	public function __construct() {

		// Get the JSON dirs.
		if ( function_exists( 'acf_get_setting' ) ) {
			$this->acf_json_dirs = acf_get_setting( 'load_json' );
		} else {
			$this->acf_json_dirs = array( trailingslashit( get_template_directory() ) . 'acf-json' );
		}
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @return void
	 */
	public function hooks() {

		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Instantiate.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @return void
	 */
	public function init() {

		// The ACF json directory.
		$this->maybe_create_directories();
		$this->maybe_create_file();
	}

	/**
	 * Get WP Filesystem.
	 *
	 * @author Jason Witt
	 * @since 0.1.0
	 *
	 * @return object $wp_filesystem The WP Filesystem.
	 */
	public function get_wp_filesystem() {

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();

		global $wp_filesystem;

		return $wp_filesystem;
	}

	/**
	 * Create ACF directories.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @return bool Return if directory exists.
	 */
	private function maybe_create_directories() {

		foreach ( $this->acf_json_dirs as $dir ) {

			// Bail early if directory exists.
			if ( file_exists( $dir ) ) {
				return;
			}

			// Create the directory if it doesn't exist.
			wp_mkdir_p( $dir );
		}
	}

	/**
	 * Create index.php file
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @return bool return Return if file exists.
	 */
	private function maybe_create_file() {

		foreach ( $this->acf_json_dirs as $dir ) {
			$file          = trailingslashit( $dir ) . 'index.php';
			$wp_filesystem = $this->get_wp_filesystem();
			$content       = "<?php if ( ! defined( 'WPINC' ) ) { wp_die( 'No Access Allowed!', 'Error!', array( 'back_link' => true ) ); }";

			// Bail eraly if file exists.
			if ( file_exists( $file ) ) {
				return;
			}

			// Create the file.
			if ( wp_is_writable( $dir ) ) {
				$wp_filesystem->put_contents( $file, $content );
			}
		}
	}
}
