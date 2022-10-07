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

    function __construct() {

        // Set Default Config
        $this->config = [
            'post_type' => [
                'id' => 'mak_wm',
                'name' => 'WM Shortcodes',
                'singular_name' => 'WM Shortcode',
                'slug' => 'wm-shortcodes',
                'taxonomies' => [],
                'has_archive' => false,
                'supports' => ['title'],
            ],
        ];

        // Init Post Types
        add_action( 'init', [ $this, 'init_post_types' ] );
    }


    function init_post_types() {

        register_post_type(
            $this->config[ 'post_type' ][ 'id' ],
            [
                'labels' => [
                    'name' => $this->config[ 'post_type' ][ 'name' ],
                    'singular_name' => $this->config[ 'post_type' ][ 'singular_name' ],
                ],
                'public' => false,
                'show_in_menu' => true,
                'rewrite' => [
                    'slug' => $this->config[ 'post_type' ][ 'slug' ],
                ],
                'taxonomies' => $this->config[ 'post_type' ][ 'taxonomies' ],
                'has_archive' => $this->config[ 'post_type' ][ 'has_archive' ],
                'supports' => (
                    !isset( $this->config[ 'post_type' ][ 'supports' ] ) ?
                    ['title', 'editor', 'thumbnail'] :
                    $this->config[ 'post_type' ][ 'supports' ] 
                ),
                'capability_type' => 'post',
                'show_ui' => true,
            ] 
        );
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