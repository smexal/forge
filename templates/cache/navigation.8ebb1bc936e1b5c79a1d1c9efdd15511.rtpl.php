<?php if(!class_exists('raintpl')){exit;}?><nav class="navbar navbar-default <?php if( $sticky ){ ?>navbar-fixed-top<?php } ?>">
    <div class="container-fluid">
    <?php $counter1=-1; if( isset($panels) && is_array($panels) && sizeof($panels) ) foreach( $panels as $key1 => $value1 ){ $counter1++; ?>
    <ul class="nav navbar-nav <?php if( $value1["position"]=='right' ){ ?>navbar-right<?php } ?>">
        <?php $counter2=-1; if( isset($value1["items"]) && is_array($value1["items"]) && sizeof($value1["items"]) ) foreach( $value1["items"] as $key2 => $value2 ){ $counter2++; ?>
            <?php echo $value2;?>
        <?php } ?>
    </ul>
    <?php } ?></div>
</nav>