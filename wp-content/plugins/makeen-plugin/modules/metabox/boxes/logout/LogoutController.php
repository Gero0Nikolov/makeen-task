<?php

namespace MakeenTask\Metabox;

class LogoutController extends MetaboxController {

    private $base_config;
    private $config;
    protected $render_config;
    private $validator_config;
    protected $params_meta_name_map;

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
                    'logout'
                ),
                'params' => [
                    'number' => [
                        'label' => __( 'Logout' ),
                        'value' => 0,
                        'additional' => [
                           'min' => 0,
                        ],
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
                
                    $value = intval( $value );

                    return (
                        is_integer( $value ) &&
                        $value >= 0
                    );
                },
                'filter' => function( $value ) {

                    return intval( $value );
                },
                'message' => __( 'Logout: Value should be equal or greater than 0!' ),
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