<?php if(!class_exists('raintpl')){exit;}?><table class="table table-striped table-hover" <?php if( $id ){ ?>id="<?php echo $id;?>"<?php } ?>>
<tr>
<?php $counter1=-1; if( isset($th) && is_array($th) && sizeof($th) ) foreach( $th as $key1 => $value1 ){ $counter1++; ?>
     <th <?php if( $value1["id"] ){ ?>id="<?php echo $value1["id"];?>"<?php } ?> <?php if( $value1["class"] ){ ?>class="<?php echo $value1["class"];?>"<?php } ?>>
       <?php echo $value1["content"];?>
     </th>
<?php } ?>
</tr>
<?php $counter1=-1; if( isset($td) && is_array($td) && sizeof($td) ) foreach( $td as $key1 => $value1 ){ $counter1++; ?>
<tr>
<?php $counter2=-1; if( isset($value1) && is_array($value1) && sizeof($value1) ) foreach( $value1 as $key2 => $value2 ){ $counter2++; ?>
     <td <?php if( $value2["id"] ){ ?>id="<?php echo $value2["id"];?>"<?php } ?> <?php if( $value2["class"] ){ ?>class="<?php echo $value2["class"];?>"<?php } ?>>
       <?php echo $value2["content"];?>
     </td>
<?php } ?>
</tr>
<?php } ?>
</table>