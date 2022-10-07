<?php

use Mpdf\Tag\Em;

class MAEX_MakeenModule extends ET_Builder_Module {

	public $slug       = 'maex_makeen_module';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'Gero Nikolov',
		'author_uri' => 'https://geronikolov.com',
	);

	public function init() {
		$this->name = esc_html__( 'Makeen Module', 'maex-makeen-extension' );
	}

	public function get_fields() {

		$fields = [
			'starting_point' => [
				'default' => 0,
				'type' => 'maex_number',
				'label' => 'Starting Point',
				'option_category' => 'basic_option',
				'description' => 'Starting Point value will be console logged.',
				'toggle_slug' => 'numbers',
				'min_value' => 0,
			],
			'trim_start' => [
				'default' => 0,
				'type' => 'maex_number',
				'label' => 'Trim Start',
				'option_category' => 'basic_option',
				'description' => 'Trim Start value will be console logged.',
				'toggle_slug' => 'numbers',
			],
			'trim_end' => [
				'default' => 0,
				'type' => 'maex_number',
				'label' => 'Trim End',
				'option_category' => 'basic_option',
				'description' => 'Trim End value will be console logged.',
				'toggle_slug' => 'numbers',
			],
			'start_img' => [
				'default' => '',
				'type' => 'maex_url',
				'label' => 'Start Image',
				'option_category' => 'basic_option',
				'description' => 'Start Image value will be console logged.',
				'toggle_slug' => 'image',
			],
			'end_img' => [
				'default' => '',
				'type' => 'maex_url',
				'label' => 'End Image',
				'option_category' => 'basic_option',
				'description' => 'End Image value will be console logged.',
				'toggle_slug' => 'image',
			],
			'src' => [
				'default' => '',
				'type' => 'maex_url',
				'label' => 'Source (SRC)',
				'option_category' => 'basic_option',
				'description' => 'Source (SRC) value will be console logged.',
				'toggle_slug' => 'image',
			],
			'has_cc' => [
				'type' => 'maex_checkbox',
				'option_category' => 'basic_option',
				'description' => 'Has CC value will be console logged.',
				'toggle_slug' => 'admin_label',
				'checkboxConfig' => [
					'label' => 'Has CC',
				],
			],
			'is_live' => [
				'type' => 'maex_checkbox',
				'option_category' => 'basic_option',
				'description' => 'Is Live value will be console logged.',
				'toggle_slug' => 'admin_label',
				'checkboxConfig' => [
					'label' => 'Is Live',
				],
			],
			'formidable_forms' => [
				'type' => 'select',
				'label' => 'Formidable Forms',
				'option_category' => 'basic_option',
				'description' => 'Formidable Forms value will be rendered as Shortcode.',
				'toggle_slug' => 'admin_label',
				'options' => $this->get_formidable_forms(),
			],
		];

		return $this->prepare_fields( $fields );
	}

	public function render( $attrs, $content = null, $render_slug ) {

		$fields_map = [
			'starting_point',
			'trim_start',
			'trim_end',
			'start_img',
			'end_img',
			'src',
			'has_cc',
			'is_live',
			'formidable_forms',
		];

		$fields_key_value = [];
		foreach ( $fields_map as $index => $field_name ) {

			$fields_key_value[ $field_name ] = (
				isset( $this->props[ $field_name ] ) ?
				$this->props[ $field_name ] :
				null
			);
		}

		wp_enqueue_script( 'maex-main-script', get_stylesheet_directory_uri() .'/main.js', [ 'jquery' ], MAEX_RESOURCE_VERSION, true );
        wp_localize_script( 'maex-main-script', 'maexMainConfigObject', [
            'fields' => $fields_key_value,
        ]);

		add_action( 'get_footer', [ $this, 'load_maex_main_style' ] );

		$formidable_forms_plugin_path = 'formidable/formidable.php';
		$is_formidable_forms_plugin_active = is_plugin_active( $formidable_forms_plugin_path );

		if ( 
			!$is_formidable_forms_plugin_active ||
			$this->props[ 'formidable_forms' ] === '0'
		) { return '<div class="maex_makeen_module"></div>'; }

		$formidable_shortcode = '[formidable id='. $this->props[ 'formidable_forms' ] .' title=true description=true]';

		return (
			'<div class="maex_makeen_module">'.
			do_shortcode( $formidable_shortcode ) .
			'</div>'
		);
	}

	public function prepare_fields( $fields ) {

		if ( empty( $fields ) ) { return $fields; }

		$values_to_convert = [
			'label' => true,
			'description' => true,
			'options' => true,
			'checkboxConfig' => true,
		];

		$prepared = [];
		foreach ( $fields as $field_key => $field_value ) {

			foreach ( $values_to_convert as $key => $should_convert ) {

				if ( 
					empty( $should_convert ) ||
					empty( $field_value[ $key ] )
				) { continue; }

				if ( !is_array( $field_value[ $key ] ) ) {

					$field_value[ $key ] = esc_html__( 
						$field_value[ $key ], 
						'maex-makeen-extension' 
					);
				} else {

					$prepared_sub_field = [];
					foreach ( $field_value[ $key ] as $sub_key => $sub_value ) {

						$prepared_sub_field[ $sub_key ] = esc_html__( 
							$sub_value, 
							'maex-makeen-extension' 
						);
					}

					$field_value[ $key ] = $prepared_sub_field;
				}
			}

			$prepared[ $field_key ] = $field_value;
		}

		return $prepared;
	}

	public function get_formidable_forms() {
		$options = [
			'0' => 'None',
		];

		$formidable_forms_plugin_path = 'formidable/formidable.php';
		$is_formidable_forms_plugin_active = is_plugin_active( $formidable_forms_plugin_path );

		if ( !$is_formidable_forms_plugin_active ) { return $options; }

		global $wpdb;

		$formidable_forms_table = (
			$wpdb->prefix .
			'frm_forms'
		);

		$formidable_forms_query = str_replace(
			[
				'\'',
			],
			[
				'',
			],
			$wpdb->prepare(
				'SELECT id, name FROM %s WHERE status="published" ORDER BY created_at DESC',
				[
					$formidable_forms_table
				]
			)
		);

		$formidable_forms = $wpdb->get_results( $formidable_forms_query, ARRAY_A );
		
		if ( empty( $formidable_forms ) ) { return $options; }

		foreach ( $formidable_forms as $form_index => $form_object ) {

			$options[ $form_object[ 'id' ] ] = $form_object[ 'name' ];
		}

		return $options;
	}

	public function load_maex_main_style() {
		wp_enqueue_style( 'maex-main-style', get_stylesheet_directory_uri() .'/makeen-extension/includes/modules/MakeenModule/style.css', [], MAEX_RESOURCE_VERSION, 'all');
	}
}

new MAEX_MakeenModule;
