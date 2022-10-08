<?php 

$message = 'Load your Formidable Form!';
$showButton = true;

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
            data-id="<?php echo $metaData['frm_id']; ?>"
        >

            <?php echo $metaData['button_label']; ?>
        </button>

        <?php
    }
    ?>

    <div
        class="makeen-task-shortcode-form-container"
    ></div>
</div>