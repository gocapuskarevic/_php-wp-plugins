<?php
if(!defined('ABSPATH')) die('Forbidden');
if(current_user_can( 'manage_options' )){
   global $wpdb;

   ?>

<h1>Pitanja za anketu</h1>
<form action="<?php echo get_template_directory_uri().'/../../plugins/coupon/question.php'; ?>" method="post">
<?php
settings_fields('forma_settings');
do_settings_sections('forma');

submit_button('Sačuvaj pitanje','','submit');
?>
</form>
<h2>Postojeća pitanja</h2>
<?php
$arr= $wpdb->get_results('SELECT option_value FROM wp_options WHERE option_name = "pitanja"');


for($i=0;$i<sizeof($arr);$i++){
   $arr= get_object_vars($arr[$i]);
}
$finn=explode("__",$arr['option_value']);
unset($arr);
?>

<form action="<?php echo get_template_directory_uri().'/../../plugins/coupon/delete.php'; ?>" method="post" id="delete" name="delete">
<?php
for($i=0;$i<sizeof($finn);$i++){
   if($finn[$i]!='')
    echo '<p><input type="checkbox" name="'.$finn[$i].'">'.$finn[$i].'</p>';
 }

 submit_button('Obriši pitanje','','delete');
?>

</form>
<?php

}else{
   wp_safe_redirect('barcode');
   exit();
}


