<?php if(!class_exists('raintpl')){exit;}?><div class="form-group <?php if( $hor ){ ?>row<?php } ?>">
    <label class='control-label <?php if( $hor ){ ?>col-lg-3<?php } ?>' for="<?php echo $id;?>"><?php echo $label;?></label>
<?php if( $hor ){ ?>
    <div class="col-lg-9">
<?php } ?>
    <input class="form-control" id="<?php echo $id;?>" name="<?php echo $name;?>" type="<?php echo $type;?>" 
        <?php if( $noautocomplete ){ ?> readonly onfocus="this.removeAttribute('readonly');"<?php } ?>>
<?php if( $hor ){ ?>
    </div>
<?php } ?>    
</div>