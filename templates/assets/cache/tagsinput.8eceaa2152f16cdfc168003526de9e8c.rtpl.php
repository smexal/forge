<?php if(!class_exists('raintpl')){exit;}?><div class="form-group <?php if( $hor ){ ?>row<?php } ?>">
    <label class='control-label <?php if( $hor ){ ?>col-lg-3<?php } ?>' for="<?php echo $id;?>"><?php echo $label;?></label>
    <input
        <?php if( $values ){ ?>data-values="<?php echo $values;?>"<?php } ?>
        class="form-control tags" 
        value="" 
        id="<?php echo $id;?>" 
        name="<?php echo $name;?>" 
        type="text"
        <?php if( $getter ){ ?>data-getter="<?php echo $getter["url"];?>" data-getter-name="<?php echo $getter["name"];?>" data-getter-value="<?php echo $getter["value"];?>"<?php } ?>>
</div>
