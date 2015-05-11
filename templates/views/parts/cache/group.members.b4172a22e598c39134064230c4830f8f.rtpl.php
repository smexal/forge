<?php if(!class_exists('raintpl')){exit;}?><div class="padded">
    <h2><?php echo $title;?></h2>
    <?php if( $message ){ ?>
        <div class="alert alert-warning">
            <p><?php echo $message;?></p>
        </div>
    <?php } ?>
    <?php echo $form;?>
    <?php echo $memberlist;?>
</div>
