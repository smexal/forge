<?php if(!class_exists('raintpl')){exit;}?><div class="padded">
    <h2><?php echo $title;?></h2>
    <?php if( $bar ){ ?>
      <div class="bar"><?php echo $bar;?></div>
    <?php } ?>
    <div class="update-container" data-url="<?php echo $url;?>" <?php if( $targeturl ){ ?>data-finished-target="<?php echo $targeturl;?>"<?php } ?>></div>
</div>
