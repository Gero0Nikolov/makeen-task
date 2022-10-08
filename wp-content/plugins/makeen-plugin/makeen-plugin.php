<?php
/*
Plugin Name: Makeen Task: Plugin
Description: Makeen Plugin, Task No. 2;
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
            ],
            'autoload' => [
                'metabox' => 'metabox',
                //'shortcode' => 'ShortcodeController',
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

        // Init Modules Autoload
        add_action( 'init', [$this, 'mtp_autoload_modules']);

        // Add Admin Resources
        add_action( 'admin_enqueue_scripts', [$this, 'mtp_add_admin_resources'] );

        // Init Session
        add_action( 'init', [$this, 'mtp_start_session'] );

        // Init Post Type
        add_action( 'init', [$this, 'mtp_init_post_type'] );
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
                'capability_type' => 'post',
                'show_ui' => true,
            ] 
        );
    }

    function mtp_add_admin_resources() {

        $styles = [
            'path' => (
                $this->config['base']['assets']['path'] 
                .'/style'
            ),
            'url' => (
                $this->config['base']['assets']['url'] 
                .'/style'
            )
        ];

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
    }

    function mtp_start_session() {

        self::start_session();
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
    
    public static function dd( $data, $should_die = true ) {

        echo '<pre>';
        var_dump( $data );
        echo '</pre>';

        if ( $should_die ) {
            die( '' );
        }
    }
}

$MakeenTaskPlugin = new MakeenTaskPlugin;