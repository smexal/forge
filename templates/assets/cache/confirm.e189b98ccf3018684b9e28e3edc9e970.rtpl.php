<?php if(!class_exists('raintpl')){exit;}?><div class="padded">
    <h2><?php echo $title;?></h2>
    <?php if( $message ){ ?>
        <p><?php echo $message;?></p>
    <?php } ?>
    <hr />
    <a class="btn btn-danger ajax" href="<?php echo $yes["url"];?>">
        <?php echo $yes["title"];?>
    </a>
    <a class="btn btn-default ajax btn-default btn-" href="<?php echo $no["url"];?>">
        <?php echo $no["title"];?>
    </a>    
</div>