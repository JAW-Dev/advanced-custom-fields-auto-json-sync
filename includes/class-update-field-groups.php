<?php
/**
 * Advanced Custom Fields: Auto JSON Sync Update Field Groups.
 *
 * @since 0.1.0
 * @package ACF_Auto_JSON_Sync
 */

/**
 * Advanced Custom Fields: Auto JSON Sync Update Field Groups.
 *
 * @since 0.1.0
 * @author Jason Witt
 */
class ACF_AJS_Update_Field_Groups {

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
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
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
		$this->maybe_update_field_groups();
	}

	/**
	 * Maybe update field groups
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @return void
	 */
	public function maybe_update_field_groups() {

		// Get the JSON dirs.
		if ( function_exists( 'acf_get_setting' ) ) {
			$json_dirs = acf_get_setting( 'load_json' );
		} else {
			$json_dirs = array( trailingslashit( get_template_directory() ) . 'acf-json' );
		}

		// Bail if no JSON directories are set.
		if ( empty( $json_dirs ) ) {
			return;
		}

		// Loop through the JSON file directories.
		foreach ( $json_dirs as $dir ) {
			$this->maybe_update_field_groups_from_json( $dir );
			$this->maybe_trash_field_group_from_database( $dir );
		}
	}

	/**
	 * Update from json
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @param string $json_dir The directory to the field group JSON files.
	 *
	 * @return void
	 */
	public function maybe_update_field_groups_from_json( $json_dir ) {

		// Bail early if no field groups exist.
		if ( ! $this->get_json_field_groups( $json_dir ) ) {

			return;
		}

		$sync        = $this->get_json_field_groups( $json_dir );
		$url         = 'edit.php?post_type=acf-field-group';
		$current_url = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		// disable filters to ensure ACF loads raw data from DB.
		acf_disable_filters();
		acf_enable_filter( 'local' );

		// disable JSON - this prevents a new JSON file being created and causing a 'change' to theme files - solves git anoyance.
		acf_update_setting( 'json', false );

		if ( ! empty( $sync ) ) {
			foreach ( $sync as $key => $v ) {

				// Append the fields to the array.
				if ( acf_have_local_fields( $key ) ) {
					$sync[ $key ]['fields'] = acf_get_local_fields( $key );
				}
				// Import the field groups.
				$field_group = acf_import_field_group( $sync[ $key ] );

				// New IDs.
				$new_ids[] = $field_group['ID'];
			}

			// Check if on the ACF Filed FGroups page.
			if ( admin_url( $url ) === $current_url ) {
				// Redirect.
				wp_redirect( admin_url( $url . '&acfsynccomplete=' . implode( ',', $new_ids ) ) );
				exit;
			}
		}
	}

	/**
	 * Delete field group from databse.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @param string $json_dir The directory to the field group JSON files.
	 *
	 * @return void
	 */
	public function maybe_trash_field_group_from_database( $json_dir ) {
		$database_keys = $this->get_database_field_group_keys();
		$json_key      = $this->get_json_field_group_keys( $json_dir );
		$diffs         = array_diff( $database_keys, $json_key );

		// Bail early if there are no database or json keys.
		if ( empty( $database_keys ) ) {
			return;
		}

		// Bail earky if array is empty.
		if ( empty( $diffs ) ) {
			return;
		}

		// Loop through the field groups.
		foreach ( $diffs as $key => $value ) {
			if ( isset( $key ) ) {

				// Trash the field groups.
				acf_trash_field_group( $key );
			}
		}
	}

	/**
	 * Get database fields groups.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @return array $keys An array of the field group keys in set in the database.
	 */
	public function get_database_field_group_keys() {
		$keys = array();
		$field_groups = get_posts( array(
			'post_type'					       => 'acf-field-group',
			'posts_per_page'			     => 99,
			'orderby' 					       => 'menu_order title',
			'order' 					         => 'asc',
			'suppress_filters'			   => false,
			'post_status'				       => array( 'publish', 'acf-disabled' ),
			'update_post_meta_cache'	 => false,
		));
		if ( ! empty( $field_groups ) ) {

			// Build array for the post name and IDs.
			$keys = wp_list_pluck( $field_groups, 'post_name', 'ID' );
		}
		return $keys;
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

		// Include the file.php to load WP_Filesystem().
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();
		global $wp_filesystem;
		return $wp_filesystem;
	}

	/**
	 * Get json field group keys.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @param string $json_dir The directory to the field group JSON files.
	 *
	 * @return keys $field_groups An array of the jason field group keys.
	 */
	public function get_json_field_group_keys( $json_dir ) {

		// Bail if no JSON directory is set.
		if ( ! $json_dir ) {
			return;
		}

		$path          = untrailingslashit( $json_dir );
		$wp_filesystem = $this->get_wp_filesystem();
		$field_groups  = array();
		$keys          = array();
		$dir           = opendir( $path );

		// Bail if directory doesn't exist.
		if ( ! file_exists( $path ) ) {
			return;
		}

		// Get the filed group keys.
		while ( false !== ( $file = readdir( $dir ) ) ) {

			// validate type.
			if ( pathinfo( $file, PATHINFO_EXTENSION ) !== 'json' ) {
				continue;
			}

			// read json.
			$json = $wp_filesystem->get_contents( "{$path}/{$file}" );

			// validate json.
			if ( empty( $json ) ) {
				continue;
			}

			// decode.
			$json = json_decode( $json, true );

			$field_groups[] = $json;
		}

		// Build an array of the field group keys.
		if ( ! empty( $field_groups ) ) {

			$keys = wp_list_pluck( $field_groups, 'key' );
		}

		return $keys;
	}

	/**
	 * Is json update.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @param string $json_dir The directory to the field group JSON files.
	 *
	 * @return array $sync An Array of json group fields that have been updated.
	 */
	public function get_json_field_groups( $json_dir ) {

		// Bail if no JSON directory is set.
		if ( ! $json_dir ) {
			return;
		}

		// Return empty array if no field groups exist.
		if ( count( glob( trailingslashit( $json_dir ) . '/*.json', GLOB_BRACE ) ) < 1 ) {
			return array();
		}

		$groups = acf_get_field_groups();
		$sync   = array();

		// bail early if no field groups.
		if ( empty( $groups ) ) {
			return false;
		}

		// find JSON field groups which have not yet been imported.
		foreach ( $groups as $group ) {
			$local    = acf_maybe_get( $group, 'local', false );
			$modified = acf_maybe_get( $group, 'modified', 0 );
			$private  = acf_maybe_get( $group, 'private', false );

			// ignore DB / PHP / private field groups.
			if ( 'json' === $local || ! $private ) {
				if ( ! $group['ID'] ) {
					$sync[ $group['key'] ] = $group;
				} elseif ( $modified && $modified > get_post_modified_time( 'U', true, $group['ID'], true ) ) {
					$sync[ $group['key'] ] = $group;
				}
			}
		}

		// bail if no sync needed.
		if ( empty( $sync ) ) {
			return false;
		}

		return $sync;
	}
}
