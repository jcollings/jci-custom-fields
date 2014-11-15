<?php
/*
Plugin Name: JC Importer - Custom Fields Addon
Plugin URI: http://jamescollings.co.uk/wordpress-plugins/jc-importer/
Description: Add custom fields tab to JC Importer templates
Author: James Collings <james@jclabs.co.uk>
Author URI: http://www.jamescollings.co.uk
Version: 0.0.1
*/

class JCI_Custom_Fields_Template{

	var $plugin_dir = false;
	var $plugin_url = false;

	public function __construct(){

		add_action( 'jci/init', array( $this, 'init' ), 10, 1);	
		$this->plugin_dir = plugin_dir_path( __FILE__ );
		$this->plugin_url = plugins_url( '/', __FILE__ );
	}

	/**
	 * Run after JC Importer has loaded
	 */
	public function init(){

		add_action( 'jci/save_template',  array( $this, 'template_save' ) );
		add_action( 'jci/after_template_fields', array( $this, 'output_fields'), 10, 3 );
		add_action( 'jci/before_import', array( $this, 'before_import'));
		add_action( 'admin_init', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Attach template_group filters on import only
	 * 
	 * @return boid
	 */
	public function before_import(){
		
		add_filter( 'jci/importer/get_groups', array($this, 'get_importer_groups'));
		add_filter( 'jci/template/get_groups', array($this, 'get_template_groups'));
	}

	/**
	 * Enqueue admin stylesheet
	 * @return void
	 */
	public function enqueue_styles(){
		wp_enqueue_style( 'jci-cf-style', $this->plugin_url . '/assets/admin.css');
	}

	/**
	 * Add filter to templates->get_groups(), Add custom fields to template fields
	 * @param  array $groups 
	 * @return array
	 */
	public function get_template_groups($groups){
		
		global $jcimporter;
		$importer_id = $jcimporter->importer->get_ID();

		$custom_fields = ImporterModel::getImporterMetaArr($importer_id, array('_import_settings', '_custom_fields'));
		if(!empty($custom_fields)){
			foreach($custom_fields as $group_id => $group){

				foreach($group as $field => $value){

					if(strlen($field) > 0 ){
						$groups[$group_id]['map'][] = array( 'title' => 'cf' , 'field' => $field);
					}					
				}
			}
		}
		return $groups;
	}

	/**
	 * Add filter to importer->get_template_groups(), Add custom fields to template fields
	 * @param  array $groups 
	 * @return array
	 */
	public function get_importer_groups($groups = array()){

		global $jcimporter;
		$importer_id = $jcimporter->importer->get_ID();

		$custom_fields = ImporterModel::getImporterMetaArr($importer_id, array('_import_settings', '_custom_fields'));
		if(!empty($custom_fields)){
			foreach($custom_fields as $group_id => $group){

				foreach($group as $field => $value){

					if(strlen($field) > 0 ){
						$groups[$group_id]['fields'][$field] = $value;	
					}					
				}
			}
		}	

		return $groups;
	}

	/**
	 * On template save
	 */
	public function template_save($id = 0, $template_type = ''){

		global $jcimporter;
		$importer = $jcimporter->importer;

		$custom_fields = array();
		$post = $_POST['jc-importer_custom-field'];
		foreach($post as $group_id => $data){

			// create default scaffold
			$post[$group_id] = array();

			// populate with field => value
			foreach($data['field'] as $k => $field){
				$custom_fields[$group_id][$field] = $data['value'][$k];
			}
		}

		// save to: _import_settings:custom_fields:group:fields
		$result = ImporterModel::setImporterMeta( $id, array(
			'_import_settings',
			'_custom_fields',
		), $custom_fields );
	}

	/**
	 * Display custom fields in template
	 * 
	 * @param  integer $importer_id 
	 */
	public function output_fields($importer_id = 0, $group_id, $group){
		
		// escape if not post
		if($group['import_type'] !== 'post')
			return;

		$custom_fields = ImporterModel::getImporterMetaArr($importer_id, array('_import_settings', '_custom_fields'));
		?>
		<div class="jci-custom-fields jci-group-section" data-section-id="Custom Fields">
			<?php if(!empty($custom_fields)): ?>
					<div id="custom-fields" class="custom-fields multi-rows">
						<table>
							<thead>
								<tr>
									<th>Field</th>
									<th>Value</th>
									<th>_</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($custom_fields[$group_id] as $field => $value): ?>
								<tr class="multi-row">
									<td><?php echo JCI_FormHelper::text( 'custom-field['.$group_id.'][field][]', array(
									'label'   => false,
									'default' => $field,
									// 'class'   => 'xml-drop jci-group',
									// 'after'   => ' <a href="#" class="jci-import-edit">[edit]</a><span class="preview-text"></span>'
								) ); ?></td>
									<td><?php echo JCI_FormHelper::text( 'custom-field['.$group_id.'][value][]', array(
									'label'   => false,
									'default' => $value,
									'class'   => 'xml-drop jci-group',
									'after'   => ' <a href="#" class="jci-import-edit">[edit]</a><span class="preview-text"></span>'
								) ); ?></td>
								<td class="jci-cf-actions"><a href="#" class="add-row">[+]</a>
								<a href="#" class="del-row">[-]</a></td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						
					</div>
				
			<?php else: ?>
			
			<div id="custom-fields" class="custom-fields multi-rows">
				<table>
					<thead>
						<tr>
							<th>Field</th>
							<th>Value</th>
							<th>_</th>
						</tr>
					</thead>
					<tbody>
						<tr class="multi-row">
							<td><?php echo JCI_FormHelper::text( 'custom-field['.$group_id.'][field][]', array(
							'label'   => false,
							'default' => '',
							// 'class'   => 'xml-drop jci-group',
							// 'after'   => ' <a href="#" class="jci-import-edit">[edit]</a><span class="preview-text"></span>'
						) ); ?></td>
							<td><?php echo JCI_FormHelper::text( 'custom-field['.$group_id.'][value][]', array(
							'label'   => false,
							'default' => '',
							'class'   => 'xml-drop jci-group',
							'after'   => ' <a href="#" class="jci-import-edit">[edit]</a><span class="preview-text"></span>'
						) ); ?></td>
						<td><a href="#" class="add-row">[+]</a>
						<a href="#" class="del-row">[-]</a></td>
						</tr>
					</tbody>
				</table>
			</div>

			<?php endif; ?>
		</div>
		<?php
	}
}

new JCI_Custom_Fields_Template();