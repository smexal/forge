<?php if(!class_exists('raintpl')){exit;}?><td <?php if( $id ){ ?>id="<?php echo $id;?>"<?php } ?> <?php if( $class ){ ?>class="<?php echo $class;?>"<?php } ?>>
  <?php echo $content;?>
</td>