<?php if(!class_exists('raintpl')){exit;}?><table class="table table-striped table-hover">
<tr>
<?php $counter1=-1; if( isset($th) && is_array($th) && sizeof($th) ) foreach( $th as $key1 => $value1 ){ $counter1++; ?>
<th><?php echo $value1;?></th>
<?php } ?>
</tr>
<?php $counter1=-1; if( isset($td) && is_array($td) && sizeof($td) ) foreach( $td as $key1 => $value1 ){ $counter1++; ?>
<tr>
<?php $counter2=-1; if( isset($value1) && is_array($value1) && sizeof($value1) ) foreach( $value1 as $key2 => $value2 ){ $counter2++; ?>
    <td><?php echo $value2;?></td>
<?php } ?>
</tr>
<?php } ?>
</table>