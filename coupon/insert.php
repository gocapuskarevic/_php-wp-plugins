<?php
define('SUBJECT',"Porudžbina bar-koda");
define("RECEIVER","puskarevicgordana@yahoo.com");
$warrning="";

require( '../../../wp-load.php' );
global $wpdb;


        //Taking values from form
        $name=trim(htmlspecialchars($_POST["name"]));
        $email=trim(htmlspecialchars($_POST["email"]));
//==================================================================
         $answers=array();

            //Take answers from users and put them into array,save for later sending email to client 
            foreach($_POST as $key=>$value) {
                if(strpos($key,'ans')){
                    array_push($answers,prevent($value));
                }
                
            }
        
        //Check if email is already in database
        $re=$wpdb->get_results('SELECT email FROM wp_barcode_user');
        $arr=array();
        for($i=0;$i<sizeof($re);$i++){
            $arr[$i]=$re[$i]->email;
        }
        if(!in_array($email,$arr)){
           

        //Taking a coupon from db
        $coupon=$wpdb->get_var("SELECT code FROM wp_barcodes LIMIT 1");

        //Deleting that coupon
        if(!is_null($coupon)){
            $tmp=$wpdb->delete("wp_barcodes",array("code"=>$coupon));
            if(!$tmp){
                $warrning="Brisanje dodeljenog kupona <b>$coupon</b> iz baze podataka nije registrovano.Obratite se tehničkoj podršci.";
            }
            $time=date("Y/m/d h:i:s");

            //Adding user in db
            $add=$wpdb->insert("wp_barcode_user",array("code"=>$coupon,"fullname"=>$name,"email"=>$email,"date"=>$time));
            $order_num=$wpdb->get_var("SELECT LAST_INSERT_ID()");

            //Preparing email headers
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

           $checkemail=false;

            // Sending mails
             if(!wp_mail($email,SUBJECT,mailForUser($coupon),$headers)){
                //$checkemail=true;
            }
            if(!wp_mail(RECEIVER,SUBJECT,mailForClient($name,$coupon,$time,$warrning,$email,$answers),$headers)){
                //$checkemail=true;
            }
            $check="";
            if($checkemail){
                $check="&post=email";
            }

            echo mailForClient($name,$coupon,$time,$warrning,$email,$answers);
            mailForUser($coupon);

            //echo $coupon;
            //die();
        }else{
            echo 'dbempty';
            /* $location = $_SERVER['HTTP_REFERER'];
            $location.="?post=dbempty";
            wp_safe_redirect($location); */
        }



        } else{
            echo 'exist';
           /*  $location = $_SERVER['HTTP_REFERER'];
            $location.="?post=exist";
            wp_safe_redirect($location); */

        }
        
   
  


    function mailForClient($name,$coupon,$time,$warrning,$email,$an){
        //Take questions from db
        global $wpdb;
        $arr= $wpdb->get_results('SELECT option_value FROM wp_options WHERE option_name = "pitanja"');
        for($i=0;$i<sizeof($arr);$i++){
        $arr= get_object_vars($arr[$i]);
        }
        $finn=explode("__",$arr['option_value']);
        unset($arr);
        //Generate message for client
        $body="<h2>Dodeljen je kupon $coupon</h2>";
        $body.="<p>Dobitnik koda je $name</p>";
        $body.="<p>Email dobitnika je $email</p>";
        $body.="<p>Vreme dodele : $time</p>";
        for($i=0;$i<sizeof($finn);$i++){
            $body.="<p>".$finn[$i]."</p>";
            $body.="<p>".$an[$i]."</p>";
        }
        if($warrning!=""){
            $body.=$warrning;
        }
        return $body;

    }

    function mailForUser($coupon){
        $body="<p>Osvojili ste gratis TEST ULAZNICU $coupon.</p>
        <p>Možete je iskoristiti u narednih 7 dana, svakog dana od 8 do 22 sata.</p> 
        <p>Adresa je Futoški put 93b.</p>
        <p>Za dodatne informacije pozovite 064/1234567</p>";
        return $body;

    }

    function prevent($string){
        return strip_tags(trim($string));
    }
?>