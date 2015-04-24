<?php if(!class_exists('raintpl')){exit;}?><li <?php if( $active ){ ?>class="active"<?php } ?>>
    <a <?php if( $icon ){ ?>class="icon"<?php } ?> href="<?php echo $url;?>" title="<?php echo $name;?>">
    <?php if( $icon ){ ?>
    <span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span>
    <?php }else{ ?>
        <?php echo $name;?>
    <?php } ?>
    </a>
</li>