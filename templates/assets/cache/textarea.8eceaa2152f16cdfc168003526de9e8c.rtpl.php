<?php if(!class_exists('raintpl')){exit;}?><div class="form-group">
    <label class='control-label' for="<?php echo $id;?>"><?php echo $label;?></label>
    <textarea <?php if( $disabled ){ ?>disabled="disabled"<?php } ?> class="form-control" id="<?php echo $id;?>" name="<?php echo $name;?>"><?php if( $value ){ ?><?php echo $value;?><?php } ?></textarea>
    <?php if( $hint ){ ?><small><?php echo $hint;?></small><?php } ?>
</div>