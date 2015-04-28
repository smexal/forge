<?php if(!class_exists('raintpl')){exit;}?><form 
    method="<?php echo $method;?>" 
    <?php if( $action ){ ?>action="<?php echo $action;?>"<?php } ?> 
    class="<?php if( $ajax ){ ?>ajax<?php } ?> <?php if( $horizontal ){ ?>form-horizontal<?php } ?>"
    <?php if( $ajax ){ ?>data-target="<?php echo $ajax_target;?>"<?php } ?>>
    <?php $counter1=-1; if( isset($content) && is_array($content) && sizeof($content) ) foreach( $content as $key1 => $value1 ){ $counter1++; ?>
        <?php echo $value1;?>
    <?php } ?>
</form>