<?php if(!class_exists('raintpl')){exit;}?><div class="login container">
    <div class="row">
        <div class="col-lg-6 col-lg-offset-3">
        <div class="well">
            <h2><?php echo $title;?></h2>
            <p><?php echo $text;?></p>
            <?php if( $message ){ ?>
            <div class="alert alert-warning">
                <p><?php echo $message;?></p>
            </div>
            <?php } ?>            
            <?php echo $form;?>
        </div>
    </div>
</div>