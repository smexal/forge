<?php if(!class_exists('raintpl')){exit;}?><nav class="navbar navbar-default <?php if( $sticky ){ ?>navbar-fixed-top<?php } ?>">
    <div class="container-fluid">
    <?php $counter1=-1; if( isset($panels) && is_array($panels) && sizeof($panels) ) foreach( $panels as $key1 => $value1 ){ $counter1++; ?>
    <ul class="nav navbar-nav <?php if( $value1["position"]=='right' ){ ?>navbar-right<?php } ?>">
        <?php echo $value1["content"];?>
    </ul>
    <?php } ?></div>
</nav>