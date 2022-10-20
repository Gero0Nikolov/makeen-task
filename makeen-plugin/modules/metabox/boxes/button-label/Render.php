<div 
    id="<?php echo $name; ?>"
    class="makeen-task-metabox-container"
>

    <label
        for="<?php echo $text['name']; ?>"
        class="makeen-task-metabox-field"
    >

        <span
            class="makeen-task-metabox-field-label"
        >

            <?php echo $text['label']; ?>
        </span>
    
        <input
            type="text"
            id="<?php echo $text['name']; ?>"
            name="<?php echo $text['name']; ?>"
            class="makeen-task-metabox-field-input"
            value="<?php echo $text['value']; ?>"
        />
    </label>
</div>