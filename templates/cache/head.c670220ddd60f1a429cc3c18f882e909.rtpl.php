<?php if(!class_exists('raintpl')){exit;}?><head>
    <a href="/#xxx"></a>
    <?php $counter1=-1; if( isset($styles) && is_array($styles) && sizeof($styles) ) foreach( $styles as $key1 => $value1 ){ $counter1++; ?>
        <link rel="stylesheet" href="/<?php echo $value1;?>">
    <?php } ?>
    <?php $counter1=-1; if( isset($scripts) && is_array($scripts) && sizeof($scripts) ) foreach( $scripts as $key1 => $value1 ){ $counter1++; ?>
    {autoescape="off"}
        <script src="C:/xampp/htdocs/ing-res/templates/<?php echo $value1;?>"></script>
    {/autoescape}
    <?php } ?>    
</head>