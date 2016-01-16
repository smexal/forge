<?php if(!class_exists('raintpl')){exit;}?><div class="ajax-content">
<?php echo $content;?>
<?php if( $messages ){ ?>
<div class="message-container">
<?php $counter1=-1; if( isset($messages) && is_array($messages) && sizeof($messages) ) foreach( $messages as $key1 => $value1 ){ $counter1++; ?>
    <div class="alert alert-<?php echo $value1["type"];?>" role="alert"><?php echo $value1["text"];?></div>
<?php } ?>
</div>
<?php } ?>
</div>
