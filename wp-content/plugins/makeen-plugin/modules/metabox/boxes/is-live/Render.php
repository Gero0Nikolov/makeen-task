<div 
    id="<?php echo $name; ?>"
    class="makeen-task-metabox-container"
>

    <label
        for="<?php echo $checkbox['name']; ?>"
        class="makeen-task-metabox-field"
    >

        <span
            class="makeen-task-metabox-field-label"
        >

            <?php echo $checkbox['label']; ?>
        </span>
    
        <input
            type="checkbox"
            id="<?php echo $checkbox['name']; ?>"
            name="<?php echo $checkbox['name']; ?>"
            class="makeen-task-metabox-field-checkbox"
            value="<?php echo $checkbox['value']; ?>"
            <?php echo ($checkbox['value'] === 'on' ? 'checked="checked"' : ''); ?>
        />
    </label>
</div>