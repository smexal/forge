<?php if(!class_exists('raintpl')){exit;}?><form method="<?php echo $method;?>" class="<?php if( $horizontal ){ ?>form-horizontal<?php } ?>">
    <?php $counter1=-1; if( isset($content) && is_array($content) && sizeof($content) ) foreach( $content as $key1 => $value1 ){ $counter1++; ?>
        <?php echo $value1;?>
    <?php } ?>
</form>