<?php

namespace MakeenTask\Metabox;

class FrmIdController extends MetaboxController {

    private $base_config;
    private $config;
    private $render_config;
    private $validator_config;
    private $params_meta_name_map;

    function __construct(
        $boxes_base_config
    ) {

        // Init Base Config
        $this->base_config = $boxes_base_config;

        // Init Config
        $this->config = [
            'base' => [
                'path' => dirname( __FILE__ ),
                'url' => plugin_dir_url( __FILE__ ),
                'namespace' => $this->base_config['namespace'],
            ],
            'meta_box' => [
                'name' => (
                    $this->base_config['meta_box']['prefix'] .
                    'frm_id'
                ),
                'params' => [
                    'select' => [
                        'label' => __( 'Form ID' ),
                        'value' => 0,
                        'options' => $this->get_formidable_form_ids(),
                    ],
                ],
            ],
        ];

        // Init Render Config
        $this->render_config = [
            'file' => (
                $this->config['base']['path'] .'/'.
                $this->base_config['meta_box']['render']
            ),
            'meta_box' => $this->config['meta_box'],
        ];

        // Init Validator Config
        $this->validator_config = [
            'select' => [
                'validator' => function( $value ) {
                    
                    $valid_options = $this->get_formidable_form_ids();
                    $value = intval( $value );

                    return (
                        is_integer( $value ) &&
                        isset( $valid_options[ $value ] )
                    );
                },
                'filter' => function( $value ) {

                    return intval( $value );
                },
                'message' => __( 'Form ID: Choose proper form ID!' ),
            ],
        ];

        // Init Params Meta Name - Map
        $this->params_meta_name_map = $this->init_params_meta_name_map(
            $this->config['meta_box']['name'],
            $this->config['meta_box']['params']
        );
    }

    private function get_formidable_form_ids() {
        
        $options = [
            0 => __( 'None' ),
        ];

        if ( !self::is_formidable_forms_plugin_active() ) { return $options; }

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

			$options[ $form_object['id'] ] = $form_object['name'];
		}

        return $options;
    }

    public function render( $post ) {

        return $this->fetch_markup(
            $post,
            $this->params_meta_name_map,
            $this->render_config
        );
    }

    public function validate( $data ) {

        return $this->filter_data(
            $data,
            $this->validator_config,
            $this->params_meta_name_map
        );
    }

    public function save( $data ) {

        return $this->update_data( $data );
    }
}