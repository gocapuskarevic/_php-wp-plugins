<?php
require( '../../../wp-load.php' );

if(current_user_can( 'administrator' )){
    global $wpdb;

        if(isset($_POST['submit'])){
            if(!is_user_logged_in()) die('Good Bay');

            //Fetch all users and their coupon
            $data=$wpdb->get_results("SELECT (select Replace( (select REPLACE(   code,CHAR(10),'')),char(13),''))
            as code,fullname,email,date FROM wp_barcode_user");

            //Making file in which the data will be stored
            $fname="Exported-data.xls";

            //Headers
        
                header("Content-Type: application/vnd.ms-excel");
                header("Content-Disposition: attachment; filename=\"$fname\"");
                
                $isPrintHeader = false;
                foreach ($data as $row) {
                    $row=get_object_vars($row);
                    if (! $isPrintHeader) {
                        echo implode("\t", array_keys($row)) . "\n";
                        $isPrintHeader = true;
                    }
                    echo implode("\t", array_values($row))."\n";
                }
                exit();
        }else{
            wp_safe_redirect('barcode');
        }

}else 
    wp_safe_redirect('barcode');


?>