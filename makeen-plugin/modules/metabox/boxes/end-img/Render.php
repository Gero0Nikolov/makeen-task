<div 
    id="<?php echo $name; ?>"
    class="makeen-task-metabox-container"
>

    <label
        for="<?php echo $url['name']; ?>"
        class="makeen-task-metabox-field"
    >

        <span
            class="makeen-task-metabox-field-label"
        >

            <?php echo $url['label']; ?>
        </span>
    
        <input
            type="url"
            id="<?php echo $url['name']; ?>"
            name="<?php echo $url['name']; ?>"
            class="makeen-task-metabox-field-input"
            value="<?php echo $url['value']; ?>"
        />
    </label>
</div>