<?php

include_once 'makeen-extension/makeen-extension.php';

add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );
function enqueue_parent_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}

define( 'MAEX_RESOURCE_VERSION', '1.0.0' );

function set_ajax_object()
{
    $maex_ajax_object = [
        'url' => admin_url( 'admin-ajax.php' ),
    ];

    echo '
    <script type="text/javascript">
        window.maexAjaxObject = '. json_encode( $maex_ajax_object ) .';
    </script>
    ';
}
add_action( 'et_head_meta', 'set_ajax_object' );

add_action( 'wp_ajax_render_formidable_form', 'render_formidable_form' );
add_action( 'wp_ajax_nopriv_render_formidable_form', 'render_formidable_form' );
function render_formidable_form() {

    $formidable_form_id = (
        isset( $_GET[ 'formidable_form_id' ] ) ?
        $_GET[ 'formidable_form_id' ] :
        false
    );

    $html = '';

    $formidable_forms_plugin_path = 'formidable/formidable.php';
    $is_formidable_forms_plugin_active = is_plugin_active( $formidable_forms_plugin_path );

    if ( 
        empty( $formidable_form_id ) || 
        !$is_formidable_forms_plugin_active
    ) { return_ajax_reponse( $html, $is_formidable_forms_plugin_active ); }

    $formidable_shortcode = '[formidable id='. $formidable_form_id .' title=true description=true]';

    $html = do_shortcode( $formidable_shortcode );
    return_ajax_reponse( $html, $is_formidable_forms_plugin_active );
}

function return_ajax_reponse( $data, $is_formidable_forms_plugin_active ) {

    $result = [
        'data' => $data,
        'formidable_forms_plugin_is_active' => $is_formidable_forms_plugin_active,
    ];

    echo json_encode( $result );
    die( '' );
}