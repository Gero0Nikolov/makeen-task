<?php

namespace MakeenTask\Shortcode;

class ShortcodeController extends \MakeenTask\MakeenTaskPlugin {

    private $base_config;
    private $config;
    
    function __construct(
        $modules_base_config
    ) {

        // Init Base Config
        $this->base_config = $modules_base_config;

        // Init Config
        $this->config = [
            'shortcode' => [
                'name' => $this->base_config['base']['shortcode'],
                'params' => [
                    'id',
                ],
                'metaboxes' => [
                    'button_label',
                    'frm_id',
                    'logout',
                    'trim_start',
                    'trim_end',
                    'start_img',
                    'end_img',
                    'src',
                    'has_cc',
                    'is_live',
                ],
            ],
            'convert' => [
                'frm_id' => function( $value ) {

                    return intval( $value );
                },
                'has_cc' => function( $value ) {

                    return $value === 'on';
                },
                'is_live' => function( $value ) {

                    return $value === 'on';
                },
            ]
        ];

        // Add Shortcode
        add_shortcode( $this->config['shortcode']['name'], [$this, 'mtp_generate_shortcode'] );
    }

    function mtp_generate_shortcode( $atts, $content = null ) {

        $metabox_module_controller = $this->get_module( 'metabox', 'metabox' );

        $meta_data = $this->convert_meta_data(
            $metabox_module_controller->fetch_meta_data_by_keys( $atts['id'], $this->config['shortcode']['metaboxes'] )
        );

        $this->dd($meta_data);
    }

    private function convert_meta_data( $meta_data ) {

        if ( empty( $meta_data ) ) { return $meta_data; }

        foreach ( $meta_data as $meta_key => $meta_value ) {

            if ( !isset( $this->config['convert'][ $meta_key ] ) ) { continue; }

            $meta_data[ $meta_key ] = $this->config['convert'][ $meta_key ]( $meta_value );
        }

        return $meta_data;
    }
}