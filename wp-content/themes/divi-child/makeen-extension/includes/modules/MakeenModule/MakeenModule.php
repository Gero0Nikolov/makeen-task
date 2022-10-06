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
		];


		return $this->prepare_fields($fields);
	}

	public function render( $attrs, $content = null, $render_slug ) {
		// return
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
}

new MAEX_MakeenModule;
