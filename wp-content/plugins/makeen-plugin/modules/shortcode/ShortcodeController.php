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
            'nonce' => 'mtp-security-nonce-[SHORTCODE_ID]-[FORM_ID]',
            'shortcode' => [
                'name' => $this->base_config['base']['shortcode'],
                'render' => [
                    'path' => dirname( __FILE__ ),
                    'file' => 'Render.php',
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
            'query_var' => [
                'key' => 'mtp_block_shortcode_until_[SHORTCODE_ID]_[FORM_ID]',
                'hours' => 12,
            ],
            'convert' => [
                'frm_id' => function( $value ) {

                    return intval( $value );
                },
                'logout' => function( $value ) {

                    return intval( $value );
                },
                'trim_start' => function( $value ) {

                    return intval( $value );
                },
                'trim_end' => function( $value ) {

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

        // Add Ajax Loader
        add_action( 'wp_ajax_mtp_get_formidable_form', [$this, 'mtp_get_formidable_form'] );
        add_action( 'wp_ajax_nopriv_mtp_get_formidable_form', [$this, 'mtp_get_formidable_form'] );
    }

    function mtp_generate_shortcode( $atts, $content = null ) {

        $metabox_module_controller = $this->get_module( 'metabox', 'metabox' );

        $meta_data = $this->convert_meta_data(
            $metabox_module_controller->fetch_meta_data_by_keys( $atts['id'], $this->config['shortcode']['metaboxes'] )
        );

        $nonce_key = $metabox_module_controller->prepare_template_tags(
            $this->config['nonce'],
            [
                'shortcode_id' => $atts['id'],
                'form_id' => $meta_data['frm_id'],
            ]
        );

        $mtp_shortcode_data_object = [
            'shortcode' => [
                'id' => $atts['id'],
            ],
            'metaData' => $meta_data,
            'formidableFormsData' => [
                'active' => self::is_formidable_forms_plugin_active(),
            ],
            'securityData' => [
                'action' => 'mtp_get_formidable_form',
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( $nonce_key ),
            ],
        ];

        $mtp_shortcode_data_object_global = [
            'shortcodes' => (object)[],
            'formidableFormsData' => $mtp_shortcode_data_object['formidableFormsData'],
            'securityData' => $mtp_shortcode_data_object['securityData'],
        ];

        unset( $mtp_shortcode_data_object_global['securityData']['nonce'] );

        wp_localize_script(
            'mtp-public-core-script', 
            'mtpShortcodeDataObject',
            $mtp_shortcode_data_object_global
        );

        return $this->render_shortcode_html( $mtp_shortcode_data_object );
    }

    function mtp_get_formidable_form() {

        $nonce = (
            !empty( $_POST['nonce'] ) ?
            sanitize_text_field( $_POST['nonce'] ) :
            null
        );

        $form_id = (
            !empty( $_POST['formId'] ) ?
            intval( $_POST['formId'] ) :
            null
        );

        $shortcode_id = (
            !empty( $_POST['shortcodeId'] ) ?
            intval( $_POST['shortcodeId'] ) :
            null
        );

        $result = [
            'success' => false,
            'data' => [],
            'messages' => [],
        ];

        if (
            empty( $nonce ) ||
            empty( $form_id ) ||
            empty( $shortcode_id )
        ) {

            $this->return_ajax_response(
                false,
                [
                    'form_id' => $form_id,
                    'shortcode_id' => $shortcode_id,
                ],
                [
                    'Check Nonce, Form Id and Shortcode Id!',
                ]
            );
        }

        $metabox_module_controller = $this->get_module( 'metabox', 'metabox' );
        $nonce_key = $metabox_module_controller->prepare_template_tags(
            $this->config['nonce'],
            [
                'shortcode_id' => $shortcode_id,
                'form_id' => $form_id,
            ]
        );

        if ( !check_ajax_referer( $nonce_key, 'nonce', false ) ) {

            $this->return_ajax_response(
                false,
                [
                    'form_id' => $form_id,
                    'shortcode_id' => $shortcode_id,
                ],
                [
                    'Nonce is invalid!',
                ]
            );
        }

        $query_var_key = $metabox_module_controller->prepare_template_tags(
            $this->config['query_var']['key'],
            [
                'shortcode_id' => $shortcode_id,
                'form_id' => $form_id,
            ]
        );

        $block_until = self::get_cookie( $query_var_key );
        if ( !empty( $block_until ) ) {

            $this->return_ajax_response(
                false,
                [
                    'form_id' => $form_id,
                    'shortcode_id' => $shortcode_id,
                ],
                [
                    'Form was already loaded, please wait until '. date( 'Y-m-d H:i:s', $block_until ) .' before trying again!',
                ]
            );
        } else {

            $block_until = strtotime(
                '+'. $this->config['query_var']['hours'] .' hours',
                time()
            );

            $cookie_set_state = self::manipulate_cookie(
                $query_var_key,
                $block_until,
                false,
                $block_until
            );
        }

        $formidable_shortcode = '[formidable id='. $form_id .' title=true description=true]';
        $html = do_shortcode( $formidable_shortcode );
        $this->return_ajax_response(
            true,
            [
                'markup' => $html,
                'form_id' => $form_id,
                'shortcode_id' => $shortcode_id,
            ],
            []
        );
    }

    protected function render_shortcode_html( $data ) {

        $html = '';

        $render_path = (
            $this->config['shortcode']['render']['path'] .'/'.
            $this->config['shortcode']['render']['file']
        );

        if ( !file_exists( $render_path ) ) { return $html; }

        extract( $data );

        ob_start();
        include $render_path;
        $html = ob_get_clean();

        return $html;
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