<?php if(!class_exists('raintpl')){exit;}?><div class="padded">
    <div class="row heading-row">
        <div class="col-lg-8"><h2><?php echo $title;?></h2></div>
        <div class="col-lg-4 align-right">
            <?php if( $add_permission ){ ?>
                <a href="javascript://" data-open="<?php echo $add_url;?>" class="btn btn-default open-overlay btn-sm"><?php echo $new_user;?></a>
            <?php } ?>
        </div>
    </div>
    <?php echo $table;?>
</div>