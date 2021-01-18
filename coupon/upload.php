<?php
require( '../../../wp-load.php' );

function testCsv($fileExt){
    $csv_mimetypes = array(
        'text/csv',
        'text/plain',
        'application/csv',
        'text/comma-separated-values',
        'application/excel',
        'application/vnd.ms-excel',
        'application/vnd.msexcel',
        'text/anytext',
        'application/octet-stream',
        'application/txt',
    );
    
    if (in_array($fileExt, $csv_mimetypes)) {
    return true;
    }else return false;
}


if(current_user_can( 'administrator' )){

global $wpdb;
$file=$_FILES['doc']['tmp_name'];

$ext=$_FILES['doc']['type'];

$fn = fopen($file,"r");
$isCsvValid=testCsv($ext);
    try{
        if($isCsvValid) {
            while (!feof($fn)) {
                if(ctype_alnum(trim($tmp=fgets($fn)))&&strlen($tmp)==7){
                    $wpdb->insert("wp_barcodes",array("code"=>$tmp));
                }else{
                    $isCsvValid=false;
                    break;
                }
            }
        }

    }finally{
        fclose($fn); 
    }
	
	$location = $_SERVER['HTTP_REFERER'];
	$location.="&format=" . ($isCsvValid ? 'true' : 'false');
	wp_safe_redirect($location);



}else wp_safe_redirect('barcode');

