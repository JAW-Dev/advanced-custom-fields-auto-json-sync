<?php
/**
 * Test Update Filed Groups Test
 *
 * @package    ACF_Auto_JSON_Sync
 * @subpackage ACF_Auto_JSON_Sync/Tests
 * @author     Jason Witt <contact@jawittdesigns.com>
 * @copyright  Copyright (c) 2017, Jason Witt
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 0.1.1
 */

/**
 * Test Update Filed Groups Test
 *
 * @author Jason Witt
 * @since 0.1.1
 */
class Test_ACF_AJS_Update_Field_Groups extends Base_UnitTestCase {

	/**
	 * SetUp.
	 *
	 * @author Jason Witt
	 * @since 0.1.1
	 *
	 * @return void
	 */
	public function setUp() {
		$this->file       = plugin_dir_path( __DIR__ ) . 'includes/class-update-field-groups.php';
		$this->class      = new ACF_AJS_Update_Field_Groups( afc_ajs() );
		$this->class_name = 'ACF_AJS_Update_Field_Groups';
		$this->methods    = array(
			'hooks',
			'init',
			'maybe_update_field_groups',
			'maybe_update_field_groups_from_json',
			'maybe_trash_field_group_from_database',
			'get_database_field_group_keys',
			'get_wp_filesystem',
			'get_json_field_group_keys',
			'get_json_field_groups',
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
		);
		foreach ( $hooks as $hook ) {
			$this->assertEquals( $hook['priority'], has_action( $hook['hook_name'], array( $this->class, $hook['method'] ) ), 'hooks() is not attaching ' . $hook['method'] . '() to ' . $hook['hook_name'] . '!' );
		}
	}
}
