<?php if(!class_exists('raintpl')){exit;}?><!DOCTYPE HTML>
<html>
<?php echo $head;?>
<body>
<div class="content <?php if( $sticky ){ ?>sticky<?php } ?>">
    <?php echo $content;?>
</div>
</body>
</html>