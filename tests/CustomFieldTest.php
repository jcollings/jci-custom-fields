<?php 
/**
 * Custom Fields Tests
 */
class CustomFieldTest extends WP_UnitTestCase{

    var $importer;

	public function setUp(){
        parent::setUp();
        $this->importer = $GLOBALS['jcimporter'];        
    }

    /**
	 * @group jci_custom_fields
	 *
	 * Test to see if custom fields works at the most basic level
	 */
    public function testBasicPostCustomField(){

    	$post_id = create_csv_importer( null, 'post', $this->importer->plugin_dir . '/tests/data/data-posts.csv', array(
			'post' => array(
				'post_title'   => '{0}',
				'post_name'    => '{1}',
				'post_excerpt' => '{3}',
				'post_content' => '{2}',
				// 'post_author' => '',
				'post_status'  => '{4}',
				// 'post_date' => '',
			)
		) );

    	// add custom field
		$result = ImporterModel::setImporterMeta( $post_id, array(
			'_import_settings',
			'_custom_fields',
			'post'
		), array('custom_field' => '123') );

		ImporterModel::clearImportSettings();
		$this->importer->importer = new JC_Importer_Core( $post_id );
		$import_data              = $this->importer->importer->run_import( 1 );

		$result = array_shift($import_data);
		$this->assertEquals('123', $result['post']['custom_field']);
    }

    /**
	 * @group jci_custom_fields
	 *
	 * Test to see if custom fields work with data parsing
	 */
    public function testParsedPostCustomField(){

    	$post_id = create_csv_importer( null, 'post', $this->importer->plugin_dir . '/tests/data/data-posts.csv', array(
			'post' => array(
				'post_title'   => '{0}',
				'post_name'    => '{1}',
				'post_excerpt' => '{3}',
				'post_content' => '{2}',
				// 'post_author' => '',
				'post_status'  => '{4}',
				// 'post_date' => '',
			)
		) );

    	// add custom field
		$result = ImporterModel::setImporterMeta( $post_id, array(
			'_import_settings',
			'_custom_fields',
			'post'
		), array('custom_field' => '{1}') );

		ImporterModel::clearImportSettings();
		$this->importer->importer = new JC_Importer_Core( $post_id );
		$import_data              = $this->importer->importer->run_import( 1 );

		$result = array_shift($import_data);
		$this->assertEquals('slug', $result['post']['custom_field']);
    }
}