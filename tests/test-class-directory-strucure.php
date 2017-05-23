<?php
/**
 * Directory Structure Test
 *
 * @package    ACF_Auto_JSON_Sync
 * @subpackage ACF_Auto_JSON_Sync/Tests
 * @author     Jason Witt <contact@jawittdesigns.com>
 * @copyright  Copyright (c) 2017, Jason Witt
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 0.1.1
 */

/**
 * Directory Structure Test
 *
 * @author Jason Witt
 * @since 0.1.1
 */
class Test_ACF_AJS_Directory_Structure extends Base_UnitTestCase {

	/**
	 * SetUp.
	 *
	 * @author Jason Witt
	 * @since 0.1.1
	 *
	 * @return void
	 */
	public function setUp() {
		$this->file       = plugin_dir_path( __DIR__ ) . 'includes/class-directory-structure.php';
		$this->class      = new ACF_AJS_Directory_Structure( afc_ajs() );
		$this->class_name = 'ACF_AJS_Directory_Structure';
		$this->methods    = array(
			'hooks',
			'init',
			'get_wp_filesystem',
			'set_acf_json_save_directory',
			'set_acf_json_load_directory',
			'maybe_create_directories',
			'maybe_create_file',
		);
		$this->proprties  = array(
			'plugin',
		);
	}

	/**
	 * Test Hooks.
	 *
	 * @author Jason Witt
	 * @since 0.1.1
	 *
	 * @return void
	 */
	public function test_hooks() {
		$this->class->init();
		$hooks = array(
			array(
				'hook_name' => 'init',
				'method'    => 'init',
				'priority'  => 10,
			),
			array(
				'hook_name' => 'acf/settings/save_json',
				'method'    => 'set_acf_json_save_directory',
				'priority'  => 10,
			),
			array(
				'hook_name' => 'acf/settings/load_json',
				'method'    => 'set_acf_json_load_directory',
				'priority'  => 10,
			),
		);
		foreach ( $hooks as $hook ) {
			$this->assertEquals( $hook['priority'], has_action( $hook['hook_name'], array( $this->class, $hook['method'] ) ), 'hooks() is not attaching ' . $hook['method'] . '() to ' . $hook['hook_name'] . '!' );
		}
	}

	/**
	 * ACF Directory exists.
	 *
	 * @author Jason Witt
	 * @since 0.1.1
	 *
	 * @return void
	 */
	public function test_does_acf_directory_exist() {
		$this->assertTrue( file_exists( trailingslashit( get_template_directory() ) . 'acf-json' ) );
	}

	/**
	 * Does File Exist.
	 *
	 * @author Jason Witt
	 * @since 0.1.1
	 *
	 * @return void
	 */
	public function test_does_file_exist() {
		$this->assertTrue( file_exists( trailingslashit( get_template_directory() ) . 'acf-json/index.php' ) );
	}
}
