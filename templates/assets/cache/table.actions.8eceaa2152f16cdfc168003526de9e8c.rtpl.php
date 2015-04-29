<?php if(!class_exists('raintpl')){exit;}?><div class="actions">
    <?php $counter1=-1; if( isset($actions) && is_array($actions) && sizeof($actions) ) foreach( $actions as $key1 => $value1 ){ $counter1++; ?>
        <?php if( $value1["icon"] ){ ?>
            <a class="btn <?php if( $value1["ajax"] ){ ?>ajax<?php } ?> btn-default btn-xs" href="<?php echo $value1["url"];?>" title="<?php echo $value1["name"];?>">
                <span class="glyphicon glyphicon-<?php echo $value1["icon"];?>" aria-hidden="true"></span>
            </a>
        <?php }else{ ?>
            <a class="btn <?php if( $value1["ajax"] ){ ?>ajax<?php } ?> btn-default btn-xs"  href="<?php echo $value1["url"];?>"><?php echo $value1["name"];?></a>
        <?php } ?>
    <?php } ?>
</div>