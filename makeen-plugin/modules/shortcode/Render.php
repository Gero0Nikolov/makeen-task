<?php 

$message = 'Load your Formidable Form!';
$showButton = true;

$jsDataObject = [
    'shortcode' => $shortcode,
    'metaData' => $metaData,
    'securityData' => $securityData,
];

if (
    !empty( $formidableFormsData['active'] ) &&
    empty( $metaData['frm_id'] )
) {
    
    $message = 'Form ID is not selected!';
    $showButton = false;
}

if ( empty( $formidableFormsData['active'] ) ) {

    $message = 'Formidable Forms Plugin is inactive!';
    $showButton = false;
}
?>

<div 
    class="makeen-task-shortcode-container"
    data-form-id="<?php echo $metaData['frm_id']; ?>"
    data-shortcode-id="<?php echo $shortcode['id']; ?>"
>

    <h2
        class="makeen-task-shortcode-message"
    >

        <?php echo __( $message ); ?>
    </h2> 

    <?php 
    if ( $showButton ) {
        ?>

        <button
            type="button"
            class="makeen-task-shortcode-button"
            data-form-id="<?php echo $metaData['frm_id']; ?>"
            data-shortcode-id="<?php echo $shortcode['id']; ?>"
        >

            <?php echo $metaData['button_label']; ?>
        </button>

        <?php
    }
    ?>

    <div
        class="makeen-task-shortcode-form-container"
        data-form-id="<?php echo $metaData['frm_id']; ?>"
        data-shortcode-id="<?php echo $shortcode['id']; ?>"
    ></div>
</div>

<script 
    type="text/javascript" 
    data-form-id="<?php echo $metaData['frm_id']; ?>"
    data-shortcode-id="<?php echo $shortcode['id']; ?>"
>

window.addEventListener('load', (event) => {

    window.mtpShortcodeId = '<?php echo $shortcode['id'] ?>';

    if (typeof window.mtpShortcodeDataObject.shortcodes[window.mtpShortcodeId] === 'undefined') {
        
        window.mtpShortcodeDataObject.shortcodes[window.mtpShortcodeId] = <?php echo json_encode( $jsDataObject ); ?>;
    }
});
</script>