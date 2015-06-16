<?php if(!class_exists('raintpl')){exit;}?><li <?php if( $active ){ ?>class="active"<?php } ?>>
    <a class="<?php if( $icon ){ ?>icon<?php } ?> <?php if( $classes ){ ?><?php echo $classes;?><?php } ?> <?php if( $image ){ ?>image<?php } ?> <?php if( $children ){ ?>dropdown-toggle<?php } ?>" href="<?php echo $url;?>" title="<?php echo $name;?>"
    <?php if( $children ){ ?> data-toggle="dropdown" role="button" aria-expanded="false"<?php } ?>>
    <?php if( $icon ){ ?>
    <span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span>
    <?php }else{ ?>
        <?php if( $image ){ ?>
            <img src="<?php echo $image;?>" alt="<?php echo $name;?>" />
        <?php }else{ ?>
            <?php echo $name;?>
        <?php } ?>
    <?php } ?>
    <?php if( $children ){ ?>
    <span class="caret"></span>
    <?php } ?>
    </a>
    <?php if( $children ){ ?>
    <ul class="dropdown-menu" role="menu">
        <?php echo $children;?>
    </ul>
    <?php } ?>
</li>