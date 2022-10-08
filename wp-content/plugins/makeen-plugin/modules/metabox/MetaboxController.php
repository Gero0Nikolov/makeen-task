<?php

namespace MakeenTask\Metabox;

class MetaboxController extends \MakeenTask\MakeenTaskPlugin {

    private $base_config;
    private $config;
    private $boxes_base_config;
    private $boxes_count;
    private $boxes;

    function __construct(
        $modules_base_config
    ) {

        // Init Base Config
        $this->base_config = $modules_base_config;

        // Init Config
        $this->config = [
            'meta_box' => [
                'id' => 'makeen_task_metabox_parent',
                'title' => __( 'Makeen Task WM Shortcode Settings' ),
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
            ],
            'autoload' => [
                'button-label',
            ],
        ];

        // Init Boxes Base Config
        $this->boxes_base_config = $this->config['base'];

        // Init Boxes Count
        $this->boxes_count = count( $this->config['autoload'] );

        // Init Boxes Container
        $this->boxes = [];

        // Init Boxes Autoload
        $autoload_state = $this->autoload_boxes();
        if ( !$autoload_state ) {

            self::dd( 'Error in Meta Boxes Autoload. Not all Meta Boxes were loaded correctly!' );
        }

        // Add Meta Boxes
        add_action( 'add_meta_boxes', [$this, 'mtp_add_meta_box'] );

        // Set Save Post Hook
        add_action( 'save_post', [$this, 'mtp_save_meta'] );
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
        
        $post_type = (
            !empty( $_POST['post_type'] ) ?
            $_POST['post_type'] :
            null
        );

        if (
            empty( $post_type ) ||
            $post_type !== $this->base_config['post_type']['id']
        ) { return $result; }


        $this->dd($_POST);
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

    public static function mtp_return_markup( $html = '' ) {

        echo $html;
    }
}