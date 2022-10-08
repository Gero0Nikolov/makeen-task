<?php

namespace MakeenTask\Metabox;

class TrimStartController extends MetaboxController {

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
                    'trim_start'
                ),
                'params' => [
                    'number' => [
                        'label' => __( 'Trim Start' ),
                        'value' => '',
                        'additional' => [],
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
            'number' => [
                'validator' => function( $value ) {

                    return (
                        is_numeric( $value ) ||
                        empty( $value )
                    );
                },
                'filter' => function( $value ) {
                    
                    $value = (
                        is_numeric( $value ) ?
                        intval( $value ) :
                        (
                            empty( trim( $value ) ) ?
                            '' :
                            $value
                        )
                    );

                    return $value;
                },
                'message' => __( 'Trim Start: Value should be number or empty!' ),
            ],
        ];

        // Init Params Meta Name - Map
        $this->params_meta_name_map = $this->init_params_meta_name_map(
            $this->config['meta_box']['name'],
            $this->config['meta_box']['params']
        );
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