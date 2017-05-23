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
	 *
	 * @param string $acf_json_dir The ACF JSON directory path.
	 */
	public function __construct( $acf_json_dir ) {
		$this->acf_json_dir = $acf_json_dir;
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

		$this->maybe_update_field_groups_from_json();
		$this->maybe_trash_field_group_from_database();
	}

	/**
	 * Update from json
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @return void
	 */
	public function maybe_update_field_groups_from_json() {

		// Bail early if no field groups exist.
		if ( empty( $this->get_json_field_groups() ) ) {

			return;
		}

		$sync = $this->get_json_field_groups();

		// disable filters to ensure ACF loads raw data from DB.
		acf_disable_filters();
		acf_enable_filter( 'local' );

		// disable JSON - this prevents a new JSON file being created and causing a 'change' to theme files - solves git anoyance.
		acf_update_setting( 'json', false );

		foreach ( $sync as $key => $v ) {

			// append fields.
			if ( acf_have_local_fields( $key ) ) {
				$sync[ $key ]['fields'] = acf_get_local_fields( $key );
			}
			// import.
			acf_import_field_group( $sync[ $key ] );
		}

		// Disable the sync group fields table filter.
		add_filter( 'views_edit-acf-field-group', '__return_false' );
	}

	/**
	 * Delete field group from databse.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @return void
	 */
	public function maybe_trash_field_group_from_database() {

		$database_keys = $this->get_database_field_group_keys();
		$json_key      = $this->get_json_field_group_keys();
		$diffs         = array_diff( $database_keys, $json_key );

		// Bail early if there are no database or json keys.
		if ( empty( $database_keys ) ) {
			return;
		}

		// Bail earky if array is empty.
		if ( empty( $diffs ) ) {
			return;
		}

		foreach ( $diffs as $key => $value ) {

			if ( isset( $key ) ) {

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
	 * @return keys $field_groups An array of the jason field group keys.
	 */
	public function get_json_field_group_keys() {

		$path          = untrailingslashit( $this->acf_json_dir );
		$wp_filesystem = $this->get_wp_filesystem();

		if ( file_exists( $path ) ) {

			$field_groups = array();
			$keys         = array();
			$dir          = opendir( $path );

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

			if ( ! empty( $field_groups ) ) {

				$keys = wp_list_pluck( $field_groups, 'key' );
			}

			return $keys;
		}
	}

	/**
	 * Is json update.
	 *
	 * @since 0.1.0
	 * @author Jason Witt
	 *
	 * @return array $sync An Array of json group fields that have been updated.
	 */
	public function get_json_field_groups() {

		// Return empty array if no field groups exist.
		if ( count( glob( trailingslashit( $this->acf_json_dir ) . '/*.json', GLOB_BRACE ) ) < 1 ) {
			return array();
		}

		$groups = acf_get_field_groups();
		$sync   = array();

		// bail early if no field groups.
		if ( empty( $groups ) ) {
			return;
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
			return;
		}

		return $sync;
	}
}
