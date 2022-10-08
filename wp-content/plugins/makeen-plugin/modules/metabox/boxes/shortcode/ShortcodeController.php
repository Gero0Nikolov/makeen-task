<?php

namespace MakeenTask\Metabox;

class ShortcodeController extends MetaboxController {

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
            'shortcode' => '[wm-custom-shortcode id="[POST_ID]"]',
            'base' => [
                'path' => dirname( __FILE__ ),
                'url' => plugin_dir_url( __FILE__ ),
                'namespace' => $this->base_config['namespace'],
            ],
            'meta_box' => [
                'name' => (
                    $this->base_config['meta_box']['prefix'] .
                    'shortcode'
                ),
                'params' => [
                    'shortcode' => [
                        'label' => __( 'Shortcode:' ),
                        'value' => '',
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
        $this->validator_config = [];

        // Init Params Meta Name - Map
        $this->params_meta_name_map = $this->init_params_meta_name_map(
            $this->config['meta_box']['name'],
            $this->config['meta_box']['params']
        );
    }

    public function render( $post ) {
        
        global $post;
        
        if ( 
            !empty( $post ) &&
            !empty( $post->ID ) 
        ) {

            $this->render_config['meta_box']['params']['shortcode']['value'] = str_replace(
                [
                    '[POST_ID]',
                ],
                [
                    $post->ID,
                ],
                $this->config['shortcode']
            );
        }

        return $this->fetch_markup(
            $post,
            $this->params_meta_name_map,
            $this->render_config
        );
    }
}