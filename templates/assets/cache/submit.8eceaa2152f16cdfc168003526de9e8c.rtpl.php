<?php if(!class_exists('raintpl')){exit;}?><div class="form-group">
<?php if( $hor ){ ?>
  <div class="col-lg-9 col-lg-offset-3">
<?php } ?>
    <button type="submit" class="btn btn-<?php echo $level;?>"><?php echo $text;?></button>
<?php if( $hor ){ ?>
  </div>
<?php } ?>
</div>