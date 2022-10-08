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

    protected function fetch_markup( $params, $render_file ) {

        $html = '';

        if ( !file_exists( $render_file ) ) { return $html; }

        extract( $params );

        ob_start();
        include $render_file;
        $html = ob_get_clean();

        return $html;
    }

    public static function mtp_return_markup( $html = '' ) {

        echo $html;
    }
}