<?php

namespace MakeenTask\Metabox;

class MetaboxController extends \MakeenTask\MakeenTaskPlugin {

    private $base_config;
    private $config;
    private $boxes_base_config;
    private $boxes_count;
    private $boxes;
    private $box_validation_message;

    function __construct(
        $modules_base_config
    ) {

        // Init Base Config
        $this->base_config = $modules_base_config;

        // Init Config
        $this->config = [
            'meta_box' => [
                'id' => 'makeen_task_metabox_parent',
                'title' => __( 'WM Shortcode Settings' ),
            ],
            'base' => [
                'path' => (
                    $this->base_config['base']['modules']['path'] .'/'.
                    'metabox/boxes'
                ),
                'url' => (
                    $this->base_config['base']['modules']['url'] .'/'.
                    'metabox/boxes/'
                ),
                'namespace' => (
                    $this->base_config['base']['namespace'] .'\\'.
                    'Metabox'
                ),
                'meta_box' => [
                    'prefix' => 'makeen_task_metabox_',
                    'render' => 'Render.php',
                ],
                'shortcode' => $this->base_config['base']['shortcode'],
                'shortcode_template' => '['. $this->base_config['base']['shortcode'] .' id="[POST_ID]"]',
            ],
            'autoload' => [
                'shortcode',
                'separator',
                'button-label',
                'frm-id',
                'logout',
                'trim-start',
                'trim-end',
                'start-img',
                'end-img',
                'src',
                'has-cc',
                'is-live',
            ],
            'query_var' => [
                'key' => 'mtp_error_flasher',
            ],
        ];

        // Init Boxes Base Config
        $this->boxes_base_config = $this->config['base'];

        // Init Boxes Count
        $this->boxes_count = count( $this->config['autoload'] );

        // Init Boxes Container
        $this->boxes = [];

        // Init Box Validation Message
        $this->box_validation_message = [];

        // Init Boxes Autoload
        $autoload_state = $this->autoload_boxes();
        if ( !$autoload_state ) {

            self::dd( 'Error in Meta Boxes Autoload. Not all Meta Boxes were loaded correctly!' );
        }

        // Add Meta Boxes
        add_action( 'add_meta_boxes', [$this, 'mtp_add_meta_box'] );

        // Set Save Post Hook
        add_action( 'save_post', [$this, 'mtp_save_meta'] );

        // Admin Notices
        add_action( 'admin_notices', [$this, 'mtp_flash_notices'] );

        // Set Flasher Reset
        add_action( 'init', [$this, 'mtp_reset_flasher'] );
    }

    private function autoload_boxes() {
        $state = false;

        if ( empty( $this->config['autoload'] ) ) { return $state; }

        foreach ( $this->config['autoload'] as $index => $box_dir ) {

            if ( 
                empty( $box_dir ) || 
                !empty( $this->boxes[ $box_dir ] )
            ) { continue; }

            $box_controller = (
                str_replace(
                    '-',
                    '',
                    ucwords(
                        $box_dir,
                        '-'
                    )
                ) .
                'Controller'
            );

            $box_controller_path = (
                $this->config['base']['path'] .'/'.
                $box_dir .'/'.
                $box_controller .
                '.php'
            );

            if ( !file_exists( $box_controller_path ) ) { continue; }

            require_once $box_controller_path;

            $box_controller_instance = (
                '\\'.
                $this->config['base']['namespace'] .'\\'.
                $box_controller
            );

            $this->boxes[ $box_dir ] = new $box_controller_instance( $this->boxes_base_config );
        }

        $boxes_loaded_count = count( $this->boxes );

        if ( $boxes_loaded_count === $this->boxes_count ) {

            $state = true;
        }

        return $state;
    }

    function mtp_add_meta_box() {

        add_meta_box(
			$this->config['meta_box']['id'],
			$this->config['meta_box']['title'],
			[$this, 'mtp_render_meta_boxes'],
			$this->base_config['post_type']['id']
		);
    }

    function mtp_reset_flasher() {

        $errors = self::get_cookie( $this->config['query_var']['key'] );

        if ( !empty( $errors ) ) {

            $cookie_set_state = self::manipulate_cookie(
                $this->config['query_var']['key'],
                [],
                true
            );
        }
    }

    function mtp_render_meta_boxes( $post ) {

        $html = '';

        if ( empty( $this->boxes ) ) { self::mtp_return_markup( $html ); }

        foreach ( $this->boxes as $box_dir => $box_controller ) {

            if (
                empty( $box_dir ) ||
                empty( $box_controller) ||
                !method_exists( $box_controller, 'render' )
            ) { continue; }

            $html .= $box_controller->render( $post );
        }

        self::mtp_return_markup( $html );
    }

    function mtp_save_meta( $post_id ) {

        $result = false;
        
        $data = $_POST;

        $post_type = (
            !empty( $data['post_type'] ) ?
            $data['post_type'] :
            null
        );

        if (
            empty( $post_type ) ||
            $post_type !== $this->base_config['post_type']['id'] ||
            empty( $this->boxes )
        ) { return $result; }

        $errors = [];

        foreach ( $this->boxes as $box_dir => $box_controller ) {

            if (
                empty( $box_dir ) ||
                empty( $box_controller) ||
                !method_exists( $box_controller, 'validate' )
            ) { continue; }

            $data = $box_controller->validate( $data );
        }

        foreach ( $this->boxes as $box_dir => $box_controller ) {

            if (
                empty( $box_dir ) ||
                empty( $box_controller) ||
                !method_exists( $box_controller, 'save' )
            ) { continue; }

            $errors = array_merge(
                $box_controller->save( $data ),
                $errors
            );
        }

        if ( !empty( $errors ) ) {

            $this->set_admin_errors_flash( $errors );
        }

        return true;
    }

    function mtp_flash_notices() {

        $errors = self::get_cookie( $this->config['query_var']['key'] );

        if ( !empty( $errors ) ) {

            $single_error_html = '
            <div class="error">
                <p>[MESSAGE]</p>
            </div>
            ';

            $html = '';

            foreach ( $errors as $error_index => $error_config ) {

                if ( empty( $error_config ) ) { continue; }

                $error_html = $single_error_html;
                foreach ( $error_config as $key => $value ) {

                    $tag = strtoupper( '['. $key .']' );
                    $error_html = str_replace(
                        $tag,
                        $value,
                        $error_html
                    );
                }
                
                $html .= $error_html;
            }
            
            self::mtp_return_markup( $html );
        }
    }

    protected function set_admin_errors_flash( $errors ) {

        $cookie_set_state = self::manipulate_cookie(
            $this->config['query_var']['key'],
            $errors
        );
    }

    protected function fetch_markup( $post, $params_meta_name_map, $render_config ) {

        $html = '';

        if ( !file_exists( $render_config['file'] ) ) { return $html; }

        $params = [
            'name' => $render_config['meta_box']['name'],
        ];

        if ( !empty( $render_config['meta_box']['params'] ) ) {
        
            foreach ( $render_config['meta_box']['params'] as $param_name => $param_config ) {

                if ( empty( $param_name ) ) { continue; }

                $param_name_meta = (
                    !empty( $params_meta_name_map[ $param_name ] ) ?
                    $params_meta_name_map[ $param_name ] :
                    (
                        $render_config['meta_box']['name'] .'_'.
                        $param_name
                    )
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

                $params[ $param_name ] = [
                    'name' => $param_name_meta,
                    'label' => $param_config['label'],
                    'value' => $param_value,
                    'options' => (
                        isset( $param_config['options'] ) ?
                        $param_config['options'] :
                        []
                    ),
                    'additional' => (
                        isset( $param_config['additional'] ) ?
                        $param_config['additional'] :
                        []
                    ),
                ];
            }
        }

        extract( $params );

        ob_start();
        include $render_config['file'];
        $html = ob_get_clean();

        return $html;
    }

    protected function init_params_meta_name_map( $metabox_name, $params = [] ) {
        
        $map = [];

        if ( empty( $params ) ) { return $map; }

        foreach ( $params as $param_name => $param_config ) {

            if ( 
                empty( $param_name ) ||
                !empty( $map[ $param_name ] )
            ) { continue; }

            $map[ $param_name ] = (
                $metabox_name .'_'.
                $param_name
            );
        }

        return $map;
    }

    protected function filter_data( $data, $validator, $params_meta_name_map ) {

        $filtered_data = $data;

        if (
            empty( $validator ) ||
            empty( $params_meta_name_map )
        ) { return $filtered_data; }

        foreach ( $params_meta_name_map as $param_name => $param_meta_name ) {

            $data_value = (
                isset( $data[ $param_meta_name ] ) ?
                $data[ $param_meta_name ] :
                null
            );

            if ( $data_value === null ) {
                
                if ( $param_name === 'checkbox' ) {
                    
                    $data[ $param_meta_name ] = 'off';
                    $data_value = $data[ $param_meta_name ];
                } else {

                    continue; 
                }
            }
            
            $data_validator = (
                isset( $validator[ $param_name ] ) &&
                isset( $validator[ $param_name ]['validator'] ) ?
                $validator[ $param_name ]['validator'] :
                null
            );

            $data_filter = (
                isset( $validator[ $param_name ] ) &&
                isset( $validator[ $param_name ]['filter'] ) ?
                $validator[ $param_name ]['filter'] :
                null
            );

            $data_value = (
                $data_filter !== null ?
                $data_filter( $data_value ) :
                $data_value
            );

            if ( 
                $data_validator !== null &&
                !$data_validator( $data_value ) 
            ) {

                $validation_message = (
                    !empty( $validator[ $param_name ]['message'] ) ?
                    $validator[ $param_name ]['message'] :
                    null
                );

                if ( $validation_message !== null ) {

                    $filtered_data[ $param_meta_name ] = [
                        'error' => true,
                        'message' => $validation_message,
                    ];
                } else {

                    unset( $filtered_data[ $param_meta_name ] );
                }
            } else {

                $filtered_data[ $param_meta_name ] = $data_value;
            }
        }
        
        return $filtered_data;
    }

    protected function update_data( $data ) {

        $errors = [];

        if ( empty( $data ) ) { return $errors; }

        $post_id = (
            !empty( $data['post_ID'] ) ?
            intval( $data['post_ID'] ) :
            null
        );

        if ( 
            empty( $post_id ) ||
            empty( $this->params_meta_name_map )
        ) { return $errors; }

        $meta_names_container = array_values( $this->params_meta_name_map );

        foreach ( $data as $param_meta_name => $param_value ) {

            if ( 
                empty( $param_meta_name ) ||
                !in_array( $param_meta_name, $meta_names_container )
            ) { continue; }

            if ( !empty( $param_value['error'] ) ) {

                $errors[ $param_meta_name ] = [
                    'meta_name' => $param_meta_name,
                    'message' => $param_value['message']
                ];
                continue;
            }

            $update_state = update_post_meta(
                $post_id,
                $param_meta_name,
                $param_value
            );
        }

        return $errors;
    }

    public function prepare_template_tags( $template, $tags ) {

        if (
            empty( $template ) ||
            empty( $tags )
        ) { return $template; }

        foreach ( $tags as $tag_key => $tag_value ) {

            $tag = strtoupper(
                '['.
                $tag_key .
                ']'
            );

            $template = str_replace(
                $tag,
                $tag_value,
                $template
            );
        }

        return $template;
    }

    public function fetch_meta_data_by_keys( $post_id, $keys ) {

        $data = [];

        foreach ( $keys as $index => $key ) {

            if ( 
                empty( $key ) ||
                isset( $data[ $key ] )
            ) { continue; }

            $key_searchable = str_replace(
                [
                    '_',
                ],
                [
                    '-',
                ],
                $key
            );

            if ( empty( $this->boxes[ $key_searchable ] ) ) { continue; }

            $box_controller = $this->boxes[ $key_searchable ];
            $box_meta_fields = array_values( $box_controller->params_meta_name_map );
            $box_meta_keys = array_keys( $box_controller->params_meta_name_map );

            if ( 
                empty( $box_meta_fields ) ||
                empty( $box_meta_keys )
            ) { continue; }

            foreach ( $box_meta_fields as $index => $meta_key ) {

                $meta_value = get_post_meta(
                    $post_id,
                    $meta_key,
                    true
                );

                if ( empty( $meta_value ) ) {

                    $meta_value = $box_controller->get_default_value_by_param(
                        $box_meta_keys[ $index ]
                    );
                }
                
                $data[ $key ] = $meta_value;
                break;
            }
        }

        return $data;
    }

    protected function get_default_value_by_param( $param_name ) {

        return (
            isset( $this->render_config ) &&
            isset( $this->render_config['meta_box']['params'][ $param_name ] ) && 
            isset( $this->render_config['meta_box']['params'][ $param_name ]['value'] ) ?
            $this->render_config['meta_box']['params'][ $param_name ]['value'] :
            null
        );
    }

    public static function mtp_return_markup( $html = '' ) {

        echo $html;
    }

    public function get_shortcode_template() {

        return $this->config['base']['shortcode_template'];
    }
}