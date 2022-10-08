<?php

namespace MakeenTask\Metabox;

class ButtonLabelController extends MetaboxController {

    private $base_config;
    private $config;
    private $render_config;

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
                    'button_label'
                ),
                'params' => [
                    'text' => [
                        'label' => __( 'Button Label' ),
                        'value' => __( 'Load Form' )
                    ],
                ],
            ],
        ];

        $this->render_config = [
            'file' => (
                $this->config['base']['path'] .'/'.
                $this->base_config['meta_box']['render']
            ),
            'meta_box' => $this->config['meta_box'],
        ];
    }

    public function render( $post ) {

        $html = '';

        if ( !file_exists( $this->render_config['file'] ) ) { return $html; }

        $metabox_params = [
            'name' => $this->render_config['meta_box']['name'],
        ];

        if ( !empty( $this->render_config['meta_box']['params'] ) ) {
        
            foreach ( $this->render_config['meta_box']['params'] as $param_name => $param_config ) {

                if ( empty( $param_name ) ) { continue; }

                $param_name_meta = (
                    $this->render_config['meta_box']['name'] .'_'.
                    $param_name
                );

                $param_meta_value = get_post_meta(
                    $post->ID,
                    $param_name_meta,
                    true
                );

                $param_value = (
                    !empty( $param_meta_value ) ?
                    $param_meta_value : 
                    $param_config['value']
                );

                $metabox_params[ $param_name ] = [
                    'name' => $param_name_meta,
                    'label' => $param_config['label'],
                    'value' => $param_value,
                ];
            }
        }

        // Fetch Markup
        $html = $this->fetch_markup(
            $metabox_params,
            $this->render_config['file']
        );

        return $html;
    }
}