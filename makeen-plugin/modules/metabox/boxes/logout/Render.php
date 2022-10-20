<div 
    id="<?php echo $name; ?>"
    class="makeen-task-metabox-container"
>

    <label
        for="<?php echo $number['name']; ?>"
        class="makeen-task-metabox-field"
    >

        <span
            class="makeen-task-metabox-field-label"
        >

            <?php echo $number['label']; ?>
        </span>

        <input
            type="number"
            id="<?php echo $number['name']; ?>"
            name="<?php echo $number['name']; ?>"
            class="makeen-task-metabox-field-input"
            value="<?php echo $number['value']; ?>"
            <?php echo ( isset( $number['additional']['min'] ) ? 'min='. $number['additional']['min'] : '' ) ?>
            <?php echo ( isset( $number['additional']['max'] ) ? 'max='. $number['additional']['max'] : '' ) ?>
        />
    </label>
</div>
