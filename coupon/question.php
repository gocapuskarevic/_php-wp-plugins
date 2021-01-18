<?php

require( '../../../wp-load.php' );
if(isset($_POST['submit'])){
    $qu=$_POST['question'];
    global $wpdb;
    

    $res=$wpdb->get_results('UPDATE wp_options SET option_value = CONCAT(option_value, "'.$qu.'__") WHERE option_name = "pitanja"');                                              
	wp_safe_redirect($_SERVER['HTTP_REFERER']);

}else wp_safe_redirect('barcode');


?>