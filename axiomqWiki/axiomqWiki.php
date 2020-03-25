<?php
session_start();
/*
Plugin Name: AxiomQ Wiki
Description: A way to group your company's informations and knowledges into one spot.
Version: 1.0
Author: Goca P
 * Licence:Copyright (C) 2020  Goca P
 *
 *This program is free software; you can redistribute it and/or
 *modify it under the terms of the GNU General Public License
 *as published by the Free Software Foundation; either version 2
 *of the License, or (at your option) any later version.
 *
 *This program is distributed in the hope that it will be useful,
 *but WITHOUT ANY WARRANTY; without even the implied warranty of
 *MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *GNU General Public License for more details.
 *
 *You should have received a copy of the GNU General Public License
 *along with this program; if not, write to the Free Software
 *Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


if ( ! defined( 'ABSPATH' ) ) exit;

if( ! defined ('AXIOMQ_WIKI_PLUGIN_DIR') )
    define( 'AXIOMQ_WIKI_PLUGIN_DIR' ,  __DIR__ );

if ( ! defined( 'AXIOMQ_WIKI_PLUGIN_DIR' ) ) exit;


require_once (ABSPATH . 'wp-includes/class-phpass.php');
require('wiki_user.php');



class AxiomQWiki{

    public static $wiki_nonce;
    private static $instance = null;

    public function __construct(){

       
        //when plugin is activated do
        register_activation_hook(__FILE__,array($this,'wiki_create_data'));

        //registre wiki post type categories(taxonomy)
        add_action('init', array($this,'wiki_taxonomy'));
        
        //registre wiki post type
        add_action('init', array($this,'wiki_post_type'));

       //add admin subpage
       add_action( 'admin_menu', array( $this, 'wiki_settings_page' ) );
            
       //add wiki category template page
       add_filter('template_include', array($this,'taxonomy_templates'));

        //add wiki css
        add_action('wp_enqueue_scripts', array($this,'wiki_css'));
        
        //add new user
        add_action('init', array($this,'wiki_after_data_sent'));

        //after logging attempt
        add_action('init', array($this,'wiki_after_logging'));

        //add ajax for user deleting
        add_action( 'admin_footer', array($this,'wiki_javascript_ajax' ));

        //ajax for user deleting
        add_action( 'wp_ajax_wiki_action_delete_wiki_user', array($this,'wiki_action_delete_wiki_user' ));
        
        //add body class
        add_action( 'wp_footer', array($this, 'wiki_js_footer') );
        
    }

    public function wiki_js_footer(){ ?>

        <script>
            document.getElementsByTagName('body')[0].classList.add('wiki-wiki');
            console.log('radis li');
        </script>

        <?php

    }

    public function wiki_javascript_ajax() { ?>
        <script type="text/javascript" >
        jQuery(document).ready(function($) {

            jQuery('.user-delete').on('click',function(){
                var $email = jQuery(this).data('email');

                var data = {
                    'action': 'wiki_action_delete_wiki_user',
                    'userid': jQuery(this).data('id'),
                    'useremail':$email,
                };

                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajaxurl, data, function(response) {
                jQuery('td:contains("'+$email+'")').text('User is deleted');
                jQuery('.user-delete[data-email="'+$email+'"]').remove();
            });

            })
    
            
    
            
        });
        </script> <?php
    }

    public function wiki_action_delete_wiki_user() {
        global $wpdb; // this is how you get access to the database
    
        $id = intval( $_POST['userid'] );

        $result = $wpdb->delete( 'wp_wiki_users', array( 'id' => $id ), '%d' );

        if( $result ) {
            echo 'User '.$_POST['useremail'].'is deleted';
        }else {
            echo $wpdb->show_errors();;
        }
        wp_die(); // this is required to terminate immediately and return a proper response
    }


   /*  if($status){
        $insert = $wpdb->query( $wpdb->prepare( 'INSERT INTO wp_wiki_users
                              ( username, email, pass ) 
                               VALUES ( %s, %s, %s )',
                               $username,
                               $email,
                               wp_hash_password( $pass ) )
                           );
        return $insert; */

    

    public function wiki_after_data_sent(){
        if(isset($_POST['wiki_registration'])){
            $result = Wiki_user::wiki_add_user();
            if( $result ){
                if( isset($GLOBALS['wiki_login_parameters']) ){
                    unset($GLOBALS['wiki_login_parameters'] );
                }
                add_filter('template_include', array($this,'after_success'));
            }else {
                $GLOBALS['wiki_registration_error'] = true;
                add_filter('template_include', array($this,'after_failed'));
            }
            
        }  
            
    }

    public function after_success($template){

        $template = AXIOMQ_WIKI_PLUGIN_DIR .'/templates/wiki_login.php';
        return $template;
    }

    public function after_failed($template){
        $template = AXIOMQ_WIKI_PLUGIN_DIR .'/templates/wiki_registration.php';
        return $template;
    }

    public function wiki_after_logging($template){
        if(isset($_POST['wiki_login'])){
            $result = Wiki_user::wiki_checklogin();
            if( $result ){
                //create simple session info
                $_SESSION['wiki_session'] = 'wiki_session';
                add_filter( 'template_include', array( $this, 'wiki_archive_template' ));
            }else{
                $GLOBALS['wiki_login_parameters'] = true;
                add_filter('template_include', array($this,'after_success'));
            }
            
            
        }  
            
    }
    

    //singleton pattern
    public static function getInstance(){
        
        if (self::$instance == null){

            self::$instance = new AxiomQWiki();
            
        }
    
        return self::$instance;
    }

    public function wiki_create_data(){
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . "wiki_users";

        $wiki_sql = "CREATE TABLE $table_name (
            id int(11) AUTO_INCREMENT PRIMARY  KEY,
            username VARCHAR(50),
            email VARCHAR(50),
            pass VARCHAR(50)
        ) $charset_collate;";


        //insert table for users
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $wiki_sql );

        //insert page for search results
        $wiki_search_page = array(
            'post_title'    => wp_strip_all_tags( 'wikisearch' ),
            'post_content'  => 'wikisearch',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page',
          );
      
          // Insert the post into the database
          wp_insert_post( $wiki_search_page );

            //insert page for search results
        $wiki_login_page = array(
            'post_title'    => wp_strip_all_tags( 'wikilogin' ),
            'post_content'  => 'wikilogin',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page',
          );
      
          // Insert the post into the database
          wp_insert_post( $wiki_login_page );
       
    }

    


    public function wiki_post_type(){
        $supports = array(
            'title', // post title
            'author', // post author
            'thumbnail', // featured images
            'custom-fields', // custom fields
            'revisions', // post revisions
            'post-formats', // post formats
            'editor', //add editor
        );
        $labels = array(
            'name' => __('AxiomQ Wiki'),
            'singular_name' => __('AxiomQ Wiki'),
            'menu_name' => 'AxiomQ Wiki',
            'name_admin_bar' => 'AxiomQ Wiki',
            'add_new' => 'Add AxiomQ Wiki',
            'add_new_item' =>'Add AxiomQ Wiki',
            'new_item' => 'New axiomQ wiki',
            'edit_item' => 'Edit axiomQ wiki',
            'view_item' => 'View axiomQ wiki',
            'all_items' => 'AxiomQ Wiki',
            'search_items' => 'Search axiomQ wiki',
            'not_found' => 'No axiomQ wiki found',
        );
        $args = array(
            'supports' => $supports,
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'hierarchical' => false,
            'show_in_nav_menus' => true,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'capability_type' => 'post',
            'show_in_rest' => true,
            'taxonomies' => array('wiki_categories','post_tag'),
        );
        register_post_type('axiomqwiki', $args);
    
    }

    public function wiki_taxonomy(){
        $labels=array(
            'name' => _x( 'Wiki category', 'taxonomy general name' ),
            'singular_name' => _x( 'Wiki category', 'taxonomy singular name' ),
            'search_items' =>  __( 'Search Wiki categories' ),
            'all_items' => __( 'All Wiki categories' ),
            'parent_item' => __( 'Parent Wiki category' ),
            'parent_item_colon' => __( 'Parent Wiki category:' ),
            'edit_item' => __( 'Edit Wiki category' ),
            'update_item' => __( 'Update Wiki category' ),
            'add_new_item' => __( 'Add New Wiki category' ),
            'new_item_name' => __( 'New Wiki category Name' ),
            'menu_name' => __( 'Wiki categories' ),
        );
        
        register_taxonomy('wiki_categories',array('post'), array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true
        )); 
    }

    public function wiki_settings_page() {
        // Add the menu item and page
        $page_title = 'Wiki settings';
        $menu_title = 'Wiki users';
        $capability = 'manage_options';
        $slug = 'wiki_settings';
        $callback = array( $this, 'wiki_list_users' );
        $icon = 'dashicons-admin-plugins';
        $position = 100;
    
        add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $slug, $callback );
    }

    public function wiki_list_users(){
        global $wpdb;
        $results = $wpdb->get_results('SELECT * FROM wp_wiki_users');

        if($results) : ?>
            <table>
                <?php foreach( $results as $result ) : ?>
                    <tr>
                        <td>
                            <?php echo $result->username; ?>
                        </td>

                        <td>
                            <?php echo $result->email; ?>
                        </td>
                        <td>
                            <button class="user-delete" data-id="<?php echo $result->id ?>" data-email="<?php echo $result->email ?>">Delete user</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; 
    }

    


     public function wiki_archive_template($template) {
        if( $_SESSION['wiki_session'] == 'wiki_session' ){
            $template = AXIOMQ_WIKI_PLUGIN_DIR.'/templates/archive-axiomqwiki.php';
        }
            
        
        return $template;
    } 

    


    public function wiki_single($single) {
 
        global $post;

        /* Checks for single template by post type */
        if ( $post->post_type == 'axiomqwiki' && $_SESSION['wiki_session'] == 'wiki_session' ) {
            if ( file_exists( AXIOMQ_WIKI_PLUGIN_DIR . '/templates/single-axiomqwiki.php' ) ) {
                return AXIOMQ_WIKI_PLUGIN_DIR . '/templates/single-axiomqwiki.php';
            }
        }

        return $single;

    }
    

    public function taxonomy_templates( $template ){
        global $post;
        $taxonomy = get_queried_object();
    
        
        if( $post->post_type == 'axiomqwiki' || $taxonomy->taxonomy == 'wiki_categories' || isset($_GET['tag']) ){
            
            if( isset( $_SESSION['wiki_session'] ) && $_SESSION['wiki_session'] == 'wiki_session' ){
              
                
                if( is_tax('wiki_categories')) {
                    $template = AXIOMQ_WIKI_PLUGIN_DIR .'/templates/taxonomy-wiki_categories.php';
                }
                elseif( isset($_POST['search']) ){
                    $template = AXIOMQ_WIKI_PLUGIN_DIR .'/templates/search-result.php';
                }
                elseif(is_single()){
                    $template = AXIOMQ_WIKI_PLUGIN_DIR . '/templates/single-axiomqwiki.php';
                }
                elseif(isset($_GET['tag'])){
                    $template = AXIOMQ_WIKI_PLUGIN_DIR .'/templates/taxonomy-wiki_categories.php';
                }
                elseif($post->post_type == 'axiomqwiki'){
                    $template = AXIOMQ_WIKI_PLUGIN_DIR . '/templates/archive-axiomqwiki.php';
                }
                
               
                    
            }
            elseif(!isset($_POST['wp_nonce_user']) && isset($_POST['registration'])){

                $template = AXIOMQ_WIKI_PLUGIN_DIR .'/templates/wiki_registration.php';
    
            }else{
                $template = AXIOMQ_WIKI_PLUGIN_DIR .'/templates/wiki_login.php';
    
            }
            
        }
        

        return $template;
        
    }


    public function wiki_css(){
        
        wp_enqueue_style('wiki-style', plugin_dir_url( __FILE__ ) . '/inc/wiki-style.css');
        
    }

    public function wiki_body_class($classes){
        $classes[] = 'wiki_wiki';

        return $classes;
    }

    public function wiki_header(){
        ?>
        <div class="margin">
            <h1><a href="<?php echo site_url().'/axiomqwiki'; ?>">AxiomQ Wikipedia</a></h1>
        </div>
        <?php
    }

    public function wiki_main_help(){
        ?>
        <div class="affiliate-style">
            <div id="more" class="section-wrap about-abbreviation background-block">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12 col-md-6 offset-md-3">
                            <form action="" method="POST" >
                                <h3>How can we help you ?</h3>
                                <input type="text" class="form-control" name="search" placeholder="keyword, tag, category etc..." required>
                                <?php wp_nonce_field('form-submit', 'wp_nonce'); ?>
                                <br>
                                <input type="submit" value="Search" name="wiki_search" class="btn btn-dark btn-block">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
/* 
        Wiki_user::wiki_registre(); */
    }

    public function wiki_sidebar(){
        //fetch axiomqwiki posts
        $posts_args=array(
            'post_type'     => 'axiomqwiki',
            'post_status'   => 'publish',
            'order'         => 'DESC',
            'orderby'       => 'date',
            'posts_per_page'=> 5
        );

        $wiki_posts = new WP_Query($posts_args);

          if($wiki_posts->have_posts()) : ?>
            <div class="col-12 col-md-4">
                <div class="row">
                    <h2>Newest : </h2>
                    <?php while($wiki_posts->have_posts()) : $wiki_posts->the_post(); ?>
                        <div class="col-12">
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        </div>
                    <?php endwhile; wp_reset_postdata();?>
                    <div class="col-12">
                        <h2>Still need help?</h2>
                        <p>Ask colleagues</p>
                    </div>
                </div>
            </div>
        
        <?php endif;
    }


    public function wiki_footer(){
        ?>
        <div id="wiki-footer margin">
            <h4>This is footer...</h4>
        </div>
        <?php
    }
    
}

$wiki_wiki = new AxiomQWiki;

