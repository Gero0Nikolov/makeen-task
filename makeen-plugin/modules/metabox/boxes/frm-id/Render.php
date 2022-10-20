<div 
    id="<?php echo $name; ?>"
    class="makeen-task-metabox-container"
>

    <label
        for="<?php echo $select['name']; ?>"
        class="makeen-task-metabox-field"
    >

        <span
            class="makeen-task-metabox-field-label"
        >

            <?php echo $select['label']; ?>
        </span>

        <select 
            id="<?php echo $select['name']; ?>"
            name="<?php echo $select['name']; ?>"
            class="makeen-task-metabox-field-select"
        >

            <?php
            if ( !empty( $select['options'] )) {
                $select['value'] = intval($select['value']);

                foreach ( $select['options'] as $option_id => $option_label ) {
                    ?>

                    <option 
                        value="<?php echo $option_id; ?>"
                        <?php echo ( $option_id === $select['value'] ? 'selected=selected' : '' ); ?>
                    >

                        <?php echo $option_label; ?>
                    </option>
                    <?php
                }
            }
            ?>
        </select>
    </label>
</div>
