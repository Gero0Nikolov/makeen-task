<?php
/*
Plugin Name: WM Shortcodes
Description: Makeen Task No. 2;
Version: 1.0
Author: Gero Nikolov
Author URI: https://geronikolov.com
License: GPLv2
*/

namespace MakeenTask;

class MakeenTaskPlugin {

    private $config;
    private $modules_count;
    private $modules_base_config;
    private $modules;
    private $custom_columns;

    public static $styles_config;
    public static $scripts_config;

    function __construct() {

        // Set Default Config
        $this->config = [
            'resource_version' => date( 'YmdHis' ),
            'post_type' => [
                'id' => 'mak_wm',
                'name' => __( 'WM Shortcodes' ),
                'singular_name' => __( 'WM Shortcode' ),
                'slug' => 'wm-shortcodes',
                'taxonomies' => [],
                'has_archive' => false,
                'supports' => ['title'],
            ],
            'base' => [
                'path' => dirname( __FILE__ ),
                'url' => plugin_dir_url( __FILE__ ),
                'namespace' => 'MakeenTask',
                'modules' => [
                    'path' => (
                        dirname( __FILE__ ) .'/'.
                        'modules'
                    ),
                    'url' => (
                        plugin_dir_url( __FILE__ ) .
                        'modules'
                    ),
                ],
                'assets' => [
                    'path' => (
                        dirname( __FILE__ ) .'/'.
                        'assets/dist'
                    ),
                    'url' => (
                        plugin_dir_url( __FILE__ ) .
                        'assets/dist'
                    ),
                ],
                'shortcode' => 'wm-custom-shortcode',
            ],
            'autoload' => [
                'metabox' => 'metabox',
                'shortcode' => 'shortcode',
            ],
        ];

        // Init Modules Count
        $this->modules_count = count( $this->config['autoload'] );

        // Init Modules Base Config
        $this->modules_base_config = [
            'post_type' => [
                'id' => $this->config['post_type']['id'],
                'slug' => $this->config['post_type']['slug'],
            ],
            'base' => $this->config['base'],
        ];

        // Init Default Modules Container
        $this->modules = [];

        // Init Custom Columns
        $this->custom_columns = [
            'mtp_wm_shortcode_column' => [
                'position' => 2,
                'label' => __( 'WM Shortcode' ),
                'render' => function( $post_id ) {

                    $data = '';
                    $main_plugin_instance = self::get_main_instance();
                    
                    if ( empty( $main_plugin_instance ) ) { return $data; }

                    $metabox_module_controller = $main_plugin_instance->get_module(
                        'metabox',
                        'metabox'
                    );

                    $shortcode_template = $metabox_module_controller->prepare_template_tags(
                        $metabox_module_controller->get_shortcode_template(),
                        [
                            'post_id' => $post_id
                        ]
                    );

                    $shortcode_length = strlen( $shortcode_template );
                    $shortcode_box_width = ( $shortcode_length * 8.6 );

                    $data = '
                    <input
                        type="text"
                        readonly="readonly"
                        id="mtp-wm-shortcode-column-'. $post_id .'"
                        name="mtp_wm_shortcode_column_'. $post_id .'"
                        value="'. htmlentities( $shortcode_template ) .'"
                        style="
                            display: block;
                            width: auto;
                            max-width: 100%;
                            min-width: '. $shortcode_box_width .'px;
                            text-align: center;
                            font-weight: bold;
                            margin: 0;
                        "
                    />
                    ';

                    return $data;
                },
            ],
        ];

        // Init Styles Config
        self::$styles_config = [
            'path' => (
                $this->config['base']['assets']['path'] 
                .'/style'
            ),
            'url' => (
                $this->config['base']['assets']['url'] 
                .'/style'
            ),
        ];

        // Init Scripts Config
        self::$scripts_config = [
            'path' => (
                $this->config['base']['assets']['path'] 
                .'/script'
            ),
            'url' => (
                $this->config['base']['assets']['url'] 
                .'/script'
            )
        ];

        // Init Modules Autoload
        add_action( 'init', [$this, 'mtp_autoload_modules'], 3 );

        // Add Public Resources
        add_action( 'wp_enqueue_scripts', [$this, 'mtp_add_public_resources'] );

        // Add Admin Resources
        add_action( 'admin_enqueue_scripts', [$this, 'mtp_add_admin_resources'] );

        // Init Post Type
        add_action( 'init', [$this, 'mtp_init_post_type'], 2 );

        // Init the Post Type Custom Columns
        add_filter( 'manage_'. $this->config['post_type']['id'] .'_posts_columns', [$this, 'mtp_manage_columns'] );

        // Populate the Post Type Custom Columns Data
        add_action( 'manage_posts_custom_column', [$this, 'mtp_populate_columns'], 10, 2 );
    }

    private function autoload_modules() {
        $state = false;

        if ( empty( $this->config['autoload'] ) ) { return $state; }

        foreach ( $this->config['autoload'] as $module_namespace => $module_controller ) {

            if (
                empty( $module_namespace ) ||
                empty( $module_controller ) ||
                !empty( $this->modules[ $module_namespace ][ $module_controller ] )
            ) { continue; }

            $module_controller_full = (
                ucfirst( $module_controller ) .
                'Controller'
            );

            $module_path = (
                $this->config['base']['modules']['path'] .'/'.
                $module_namespace .'/'.
                $module_controller_full .'.php'
            );

            if ( !file_exists( $module_path ) ) { continue; }

            require_once $module_path;

            $module_namespace_full = (
                '\\'.
                $this->config['base']['namespace'] .'\\'.
                ucfirst( $module_namespace )
            );

            $module_controller_instance = (
                $module_namespace_full .'\\'.
                $module_controller_full
            );

            if ( empty( $this->modules[ $module_namespace ] ) ) {

                $this->modules[ $module_namespace ] = [];
            }

            $this->modules[ $module_namespace ][ $module_controller ] = new $module_controller_instance( $this->modules_base_config );
        }

        $modules_loaded_count = count( $this->modules );
        if ( $modules_loaded_count === $this->modules_count ) {
            
            $state = true;
        }

        return $state;
    }

    private function prepare_columns_list( $columns ) {

        if ( empty( $this->custom_columns ) ) { return $columns; }

        $columns_index_keys_map = array_keys( $columns );
        foreach ( $this->custom_columns as $column_key => $column_config ) {

            if ( empty( $column_config ) ) { continue; }

            if ( !isset( $column_config['position'] ) ) {

                $columns_index_keys_map[] = $column_key;
                continue;
            }
            
            $columns_index_keys_map = $this->prepare_column_index_keys_map(
                $columns_index_keys_map,
                $column_key,
                $column_config['position']    
            );
        }    

        $columns_list_prepared = [];
        foreach ( $columns_index_keys_map as $index => $key ) {

            if ( isset( $columns[ $key ] ) ) {

                $columns_list_prepared[ $key ] = $columns[ $key ];
            } elseif ( isset( $this->custom_columns[ $key ] ) ) {

                $columns_list_prepared[ $key ] = (
                    !empty( $this->custom_columns[ $key ]['label'] ) ?
                    $this->custom_columns[ $key ]['label'] :
                    ''
                );
            }
        }

        return $columns_list_prepared;
    }

    private function prepare_column_index_keys_map( $map, $column_key, $column_position ) {

        if (
            empty( $map ) ||
            empty( $column_key ) ||
            (
                empty( $column_position ) &&
                $column_position !== 0
            )
        ) { return $map; }

        if ( !isset( $map[ $column_position ] ) ) {

            $map[ $column_position ] = $column_key;
            return $map;
        }

        foreach ( $map as $index => $key ) {

            if ( $index !== $column_position ) { continue; }

            $current_column = [
                'new_position' => ($index + 1),
                'key' => $key,
            ];

            $map[ $index ] = $column_key;

            $map = $this->prepare_column_index_keys_map(
                $map,
                $current_column['key'],
                $current_column['new_position']
            );
        }

        return $map;
    }

    function mtp_autoload_modules() {

        $autoload_state = $this->autoload_modules();

        if ( !$autoload_state ) {

            self::dd( 'Error in Modules Autoload. Not all Modules were loaded correctly!' );
        }
    }

    function mtp_init_post_type() {

        register_post_type(
            $this->config['post_type']['id'],
            [
                'labels' => [
                    'name' => $this->config['post_type']['name'],
                    'singular_name' => $this->config['post_type']['singular_name'],
                ],
                'public' => false,
                'show_in_menu' => true,
                'rewrite' => [
                    'slug' => $this->config['post_type']['slug'],
                ],
                'taxonomies' => $this->config['post_type' ]['taxonomies'],
                'has_archive' => $this->config['post_type']['has_archive'],
                'supports' => (
                    !isset( $this->config['post_type']['supports'] ) ?
                    ['title', 'editor', 'thumbnail'] :
                    $this->config['post_type']['supports'] 
                ),
                'show_ui' => true,
                'capabilities' => [
                    'publish_posts' => 'manage_options',
                    'edit_others_posts' => 'manage_options',
                    'delete_posts' => 'manage_options',
                    'delete_others_posts' => 'manage_options',
                    'read_private_posts' => 'manage_options',
                    'edit_post' => 'manage_options',
                    'delete_post' => 'manage_options',
                    'read_post' => 'manage_options',
                ],
            ] 
        );
    }

    function mtp_add_public_resources() {

        $styles = self::$styles_config;

        $scripts = self::$scripts_config;

        wp_enqueue_script(
            'mtp-public-core-script', 
            (
                $scripts['url'] .
                '/public.js'
            ), 
            ['jquery'], 
            $this->config['resource_version'],
            true
        );
    }

    function mtp_add_admin_resources() {

        $styles = self::$styles_config;

        $scripts = self::$scripts_config;

        wp_enqueue_style(
            'mtp-admin-global-style', 
            (
                $styles['url'] .
                '/admin.css'
            ), 
            [], 
            $this->config['resource_version'], 
            'all'
        );

        wp_enqueue_script(
            'mtp-admin-core-script', 
            (
                $scripts['url'] .
                '/admin.js'
            ), 
            ['jquery'], 
            $this->config['resource_version'],
            true
        );
    }

    function mtp_manage_columns( $columns ) {

        return $this->prepare_columns_list( $columns );
    }

    function mtp_populate_columns( $column, $post_id ) {        

        if (
            !empty( $column ) &&
            !empty( $post_id ) &&
            !empty( $this->custom_columns ) &&
            !empty( $this->custom_columns[ $column ] ) &&
            isset( $this->custom_columns[ $column ]['render'] )
        ) { 

            echo $this->custom_columns[ $column ]['render']( $post_id );
        }
    }

    public function get_module( $namespace, $module ) {

        global $MakeenTaskPlugin;
        
        return (
            !empty( $MakeenTaskPlugin ) &&
            !empty( $MakeenTaskPlugin->modules ) &&
            !empty( $MakeenTaskPlugin->modules[ $namespace ] ) &&
            !empty( $MakeenTaskPlugin->modules[ $namespace ][ $module ] ) ?
            $MakeenTaskPlugin->modules[ $namespace ][ $module ] :
            null
        );
    }

    public function return_ajax_response( $success, $data, $messages ) {

        $result = [
            'success' => $success,
            'data' => $data,
            'messages' => $messages,
        ];
    
        echo json_encode( $result );
        die( '' );
    }

    public static function start_session() {
        
        if ( !session_id() ) {

            session_start();
        }
    }

    public static function manipulate_session( $key, $value, $delete = false ) {
        
        if ( $delete ) {

            if ( isset( $_SESSION[ $key ] ) ) {
             
                unset( $_SESSION[ $key ] );
            }
            
            return true;
        }

        $_SESSION[ $key ] = $value;
        return true;
    }

    public static function get_session( $key ) {

        return (
            isset( $_SESSION[ $key ] ) ?
            $_SESSION[ $key ] :
            null
        );
    }

    public static function is_formidable_forms_plugin_active() {

        $formidable_forms_plugin_path = 'formidable/formidable.php';
        return is_plugin_active( $formidable_forms_plugin_path );
    }

    public static function get_main_instance() {

        global $MakeenTaskPlugin;

        return (
            !empty( $MakeenTaskPlugin ) ?
            $MakeenTaskPlugin :
            null
        );
    }

    public static function dd( $data, $should_die = true, $should_return = false ) {

        if ( !$should_return ) {
            echo '<pre>';
            var_dump( $data );
            echo '</pre>';
        }

        if ( $should_die ) {
            die( '' );
        }

        if ( $should_return ) {
            return $data;
        }
    }
}

$MakeenTaskPlugin = new MakeenTaskPlugin;