<?php
/**
 * Base Plugin File Test
 *
 * Test the base plugin files
 *
 * @package    ACF_Auto_JSON_Sync
 * @subpackage ACF_Auto_JSON_Sync/Tests
 * @author     Jason Witt <contact@jawittdesigns.com>
 * @copyright  Copyright (c) 2017, Jason Witt
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 0.1.1
 */

/**
 * Base Plugin File Test
 *
 * @author Jason Witt
 * @since 0.1.1
 */
class Test_ACF_Auto_JSON_Sync extends Base_UnitTestCase {

	/**
	 * SetUp.
	 *
	 * @author Jason Witt
	 * @since 0.1.1
	 *
	 * @return void
	 */
	public function setUp() {
		$this->file       = plugin_dir_path( __DIR__ ) . 'advanced-custom-fields-auto-json-sync.php';
		$this->class_name = 'ACF_Auto_JSON_Sync';
		$this->methods    = array(
			'plugin_classes',
			'_activate',
			'init',
		);
		$this->proprties  = array(
			'url',
			'path',
			'basename',
			'plugin_prefix',
			'acf_json_dir',
			'directory_structure',
			'update_field_groups',
		);
	}

	/**
	 * Test that our main helper function is an instance of our class.
	 *
	 * @author Jason Witt
	 * @since 0.1.1
	 *
	 * @return void
	 */
	function test_get_instance() {
		$this->assertInstanceOf( $this->class_name, afc_ajs() );
	}

	/**
	 * Test Plugin Classes.
	 *
	 * @author Jason Witt
	 * @since 0.1.1
	 *
	 * @return void
	 */
	public function test_plugin_classes() {
		$this->assertInstanceOf( ACF_AJS_Directory_Structure::class, new ACF_AJS_Directory_Structure( afc_ajs() ) );
		$this->assertInstanceOf( ACF_AJS_Update_Field_Groups::class, new ACF_AJS_Update_Field_Groups( afc_ajs() ) );
	}

	/**
	 * Test Meets requirements.
	 *
	 * @author Jason Witt
	 * @since 0.1.1
	 *
	 * @return void
	 */
	public function test_meets_requirements() {
		$this->assertTrue( is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) );
	}
}
