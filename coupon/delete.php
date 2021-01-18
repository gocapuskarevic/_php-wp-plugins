<?php
require( '../../../wp-load.php' );
global $wpdb;

   $arr= $wpdb->get_results('SELECT option_value FROM wp_options WHERE option_name = "pitanja"');


    for($i=0;$i<sizeof($arr);$i++){
       $arr= get_object_vars($arr[$i]);
    }
    //This is array where are all values,before deleting specific value
    $finn=explode("__",$arr['option_value']);
    unset($arr);
    $forDelete=array();
    

    foreach($_POST as $key=>$value) {
        
        if($value=="on"){
            array_push($forDelete,$key);
        }
    }
    
  
        for($i=0;$i<sizeof($forDelete);$i++){
            if(($key = array_search(str_replace('_',' ',$forDelete[$i]), $finn))!==false){
                unset($finn[$key]);
            }
        }
        print_r($finn);
        echo '<br>';
        $tmp=$wpdb->update('wp_options',array('option_value'=>''),array('option_name'=>'pitanja'));
        $finally=array_values($finn);
       
        for($i=0;$i<sizeof($finally);$i++){
            
            $wpdb->get_results('UPDATE wp_options SET option_value = CONCAT(option_value, "'.$finally[$i].'__") WHERE option_name = "pitanja"');
        }

    wp_safe_redirect($_SERVER['HTTP_REFERER']);


?>