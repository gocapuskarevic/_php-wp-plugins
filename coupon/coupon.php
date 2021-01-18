<?php
/*
Plugin name: Coupon
Plugin URI: 
Description:Win a coupon
Version:1.0
Author:Goca P
Autrhor URI:localhost/barcode
License:GPLv2
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

*/

/*

Coupon is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

Coupon is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/



class Coupon{
    
    //constructor for MyPlugin object
    function __construct() {
        register_activation_hook(__FILE__,array($this, 'coupon_tables'));
        //Init Ulaznice section ina dmin panel
        add_action( 'admin_menu',  array( $this, 'custom_menu_page' ) );

        add_action( 'init',  array( $this, 'warrnings' ) );

        //Shortcode for export and import the data
        add_shortcode('ulaznice','all');

        register_setting('forma_settings','forma_settings');

        add_action('admin_init',array($this,'forma_settings'));

        
    }

    
 

   

    
    //Create necessary tables when plugin is initialized
    function coupon_tables(){
        global $wpdb;
        $wpdb->get_results( 'CREATE TABLE IF NOT EXISTS  wp_barcodes(code VARCHAR(10)) CHARACTER SET utf8 COLLATE utf8_unicode_ci');
        $wpdb->get_results('CREATE TABLE IF NOT EXISTS  wp_barcode_user(code VARCHAR(10),fullname VARCHAR(50),email VARCHAR(50),date DATETIME) CHARACTER SET utf8 COLLATE utf8_unicode_ci');

    }

    private function import(){
        echo '<h2>Izaberite dokument koji želite da uploadujete...</h2>';
        echo '<h4>Dozvoljen je iskljucivo .csv format dokumenta.</h4>';
        echo '<form action="'.get_template_directory_uri().'/../../plugins/coupon/upload.php" method="post" enctype="multipart/form-data" name="import">
            <input type="file" name="doc">
            <input type="submit" value="Importuj" name="submit">
            </form>';
    }

    private function export(){
        echo '<h2>Kliknite na dugme ispod da biste preuzeli tabelu sa podacima.</h2>';
        echo '<form action="'.get_template_directory_uri().'/../../plugins/coupon/export.php" method="post">
            
            <input type="submit" value="Preuzmi podatke" name="submit">
            </form>';       
    }

    //Wrapp two main function
    public function all(){

        $this->import();
        $this->export();
        
    }

    public function custom_menu_page() {
        add_menu_page(
            __( 'Ulaznice', 'ulaznice' ),
            'Ulaznice',
            'manage_options',
            'ulaznice',
            array($this,'all'),
            'dashicons-media-text'
        );
        //Add submenu page for making form
        add_submenu_page(
            'ulaznice',
            __('Forma','forma'),
            __('Forma','forma'),
            'manage_options',
            'forma',
            array($this,'makeForm0')
    
        );
    }

    public function forma_settings(){

        if(!get_option('pitanja'))
        add_option('pitanja');
       
        add_settings_section(
    
            //Unique identifier
            'forma_settings',
            //Title
            __('Dodavanje polja u formu'),
            //Callback
            'makeForm',
            //On which page add section
            'forma'
        );
    
        add_settings_field(
            //Unique identifier
            'forma_text',
            //Title
            __('Unesite anketno pitanje'),
            //Callback func
            array($this,'makeInput'),
            //On page
            'forma',
            //Section to go in
            'forma_settings'
        );
    
    
    }

    public function makeInput(){
        echo '<input type="text" name="question" required>';
    }
    
//Calling submenu page add,delete questions
    public function makeForm0(){

    print '<div class="wrap">';
    do_action( 'sd_settings_tab' );

    $file = plugin_dir_path( __FILE__ ) . "forma.php";

    if ( file_exists( $file ) )
        require $file;
    

    print '</div>';
    }

    public function warrnings(){
        if(isset($_GET['format'])){
            switch($_GET['format']){
                case 'false':
                echo '<script>alert("Dozvoljen format dokumenta je .csv");</script>';
                break;
                case 'true':
                echo '<script>alert("Podaci su uspešno importovani.");</script>';
                break;
                case 'submit':
                echo '<script>alert("Izaberite opciju \'Preuzimanje podataka\'.");</script>';
                case 'ask':
                echo '<script>alert("Pitanje sacuvano");</script>';
                break;
                
    
            }
        }

        if(isset($_GET["post"])){
            switch($_GET["post"]){
                case "empty":
                echo "<script>alert('Morate popuniti sva polja da biste dobili kupon');</script>";
                break;
                case "email":
                echo "<script>alert('Došlo je do greške prilikom slanja emaila')</script>";
              
            }
          
        }
    }

}

$coupon=new Coupon();


class Frontend_Coupon{

    public function __construct(){
        add_action('wp_dashboard_setup','wpf_registre_widget');
        add_shortcode('forma',array($this,'show'));
        wp_enqueue_style('ulstyle.css',plugins_url().'/coupon/ulstyle.css');
        wp_enqueue_script('custom.js',plugins_url().'/coupon/custom.js',array('jquery'),'',true);
        
    }


     public function show(){
        global $wpdb;
        $remain_coupons=$wpdb->get_var("SELECT code FROM wp_barcodes LIMIT 1");
        $tmp=(is_null($remain_coupons))? "disabled" : "";
        
        
        $arr= $wpdb->get_results('SELECT option_value FROM wp_options WHERE option_name = "pitanja"');
        
        
        for($i=0;$i<sizeof($arr);$i++){
           $arr= get_object_vars($arr[$i]);
        }
        $finn=explode("__",$arr['option_value']);
        unset($arr);
        
        //Button for init anketa
        echo 
            '<div><p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which dont look even slightly believable.</p>
            <button onclick="popup();">Popuni anketu</button>
            
            </div>';
        
        
        //Div for pop up
            echo '
            <div id="take_coupon">
            <form action="" method="post" onsubmit="return checkName(this);" id="formica">
        ';
            for($i=0;$i<sizeof($finn);$i++){
                if($finn[$i]!=''){
                    echo '<p>'.$finn[$i].'</p>';
                    echo '<label for="'.$i.'ans"><input type="text" name="'.$i.'ans" id="'.$i.'ans" required placeholder="Obavezno*"></label>';
                }
                
             }
            
            echo '<p>Ime i prezime: </p>
            <label for="name">
                <input type="text" id="name" name="name" placeholder="Obavezno*" required>
            </label>
            <p> Email adresa:</p>
            <label for="email">
                <input type="email" id="email" name="email" placeholder="Obavezno*" required>
            </label>
            <label for="sub">
                <input type="submit" value="PREUZMI" id="sub" name="submit" '.$tmp.' onclick="return checkName();">
            </label>
            <label for="set">
            <input type="text" value="Vaš kupon" id="set">
            </label>
            
            <label for="reset">
            <input type="reset" value="Resetuj podatke" id="reset">
            </label>
            <button onclick="popup(); return false;">Zatvori</button>
            
            <div style="visibility:hidden" id="hd">
            <p>U predvidjeno polje unesite i ime i prezime</p></div>
            
            </form>
           
           
            </div>';
            
        }


    public function for_ajax(){
        wp_enqueue_script('for_ajax.js',plugins_url().'/coupon/for_ajax.js',array('jquery'),'',true);
        wp_localize_script('for_ajax.js','postdata',array(
            'ajax_url'=>admin_url('admin_ajax.php'),
        ));
        add_action('wp_ajax_nopriv_insert',array($this,'insert'));

    }

}

$frontend=new Frontend_Coupon();
$frontend->for_ajax();

//Custom widget wp_forma

class wp_forma extends WP_Widget{

	public function __construct(){

		$options = array(
			'description' => 'Manage with the data and allow users to win a coupon',
		);
	
		parent::__construct(
			'wp_forma', 'Coupons', $options
		);

	}

	public function widget($args,$instance){
	
		echo do_shortcode( '[forma]' );
		

	}
}

function reg_widget(){
	register_widget('wp_forma');
}

add_action('widgets_init','reg_widget');