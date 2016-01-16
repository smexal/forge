<?php if(!class_exists('raintpl')){exit;}?><div class="progress">
  <div class="progress-bar progress-bar-striped active" role="progressbar" 
    aria-valuenow="<?php echo $current;?>" 
    aria-valuemin="<?php echo $min;?>" 
    aria-valuemax="<?php echo $max;?>" 
    style="width: <?php echo $current;?>%"
    id="<?php echo $id;?>">
    <span class="sr-only"><?php echo $text;?></span>
  </div>
</div>