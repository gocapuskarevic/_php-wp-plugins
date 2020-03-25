<?php
class Wiki_user {

    protected $username,$password,$email;


    public static function wiki_registre(){
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 text-center">
                    <h1>Registration form</h1>
                </div>
                <div class="col-12 col-md-6 offset-md-3">

                <?php if( isset( $GLOBALS['wiki_registration_error'] ) && $GLOBALS['wiki_registration_error'] == true) : ?>
                    <div class="col-12 text-center">
                        <div class="">
                            <h2>Error, try again</h2>
                        </div>
                    </div>

                <?php endif; ?>
                
                    <div id="wiki-registration">

                    <!-- Registration for new users-->
                        <form method="POST" action="">
                            <label for="fname">Username : </label>
                            <input type="text" id="fname" name="fname" class="form-control" required><br>


                            <label for="pass1">Password : </label>
                            <input type="password" id="pass1" name="pass1" class="form-control" required><br>


                            <label for="pass2">Password verify : </label>
                            <input type="password" id="pass2" name="pass2" class="form-control" required><br>


                            <label for="email1">Email : </label>
                            <input type="email" id="email1" name="email1" class="form-control" required><br>


                            <label for="email2">Email verify : </label>
                            <input type="email" id="email2" name="email2" class="form-control" required><br>

                            <?php wp_nonce_field('user-registration', 'wp_nonce_wiki_reg'); ?>

                            <input type="submit" name="wiki_registration" value="Send" class="btn btn-dark btn-block">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public static function wiki_login(){
        ?>
        <div class="container-fluid">
            <div class="row">

                <?php if(isset( $GLOBALS['wiki_login_parameters'] )  && $GLOBALS['wiki_login_parameters'] == true ) : ?>
                    <div class="col-12 text-center">
                        <div class="">
                            <h2>Wrong parameters</h2>
                        </div>
                    </div>

                <?php endif; ?>


                <div class="col-12 text-center">
                    <h1>Login form</h1>
                </div>
                <div class="col-12 col-md-6 offset-md-3">
                <!-- Log in form-->
                    <div id="wiki-registration">
                        <form method="POST" action="">
                            <label for="email">Email : </label>
                            <input type="email" id="email" name="email" class="form-control" required><br>


                            <label for="pass">Password : </label>
                            <input type="password" id="pass" name="pass" class="form-control" required><br>

                            <?php wp_nonce_field('user-login', 'wp_nonce_wiki_login'); ?>

                            <input type="submit" name="wiki_login" value="Log In" class="btn btn-dark btn-block">

                        </form>

                        <!-- Registration Button on Log In page-->
                        <form method="POST" action="">
                            
                            <input type="submit" name="registration" value="Registration" class="btn btn-dark btn-block">
                        </form>

                        
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public static function wiki_checklogin(  ){
        $status = 0;

        if( isset($_POST['wp_nonce_wiki_login']) && wp_verify_nonce( $_POST['wp_nonce_wiki_login'], 'user-login' ) ){
            global $wpdb;
            
            if(isset($_POST['email']) && isset($_POST['pass'])){
                $email = esc_sql( $_POST['email'] );
                $pass = esc_sql( $_POST['pass'] );

                $users = $wpdb->get_results( 'SELECT email, pass FROM wp_wiki_users' );
                if( $users ){

                    $wp_hasher = new PasswordHash(8, TRUE);

                    foreach ( $users as $user ){
                        var_dump($wp_hasher->CheckPassword($pass, $user->pass));
                        if($user->email == $email && $wp_hasher->CheckPassword($pass, $user->pass) ){
                            $status=1;
                            break;
                        }
                    }

                    return $status;
                }

                return $status;
                
            } else {

                return $status;
            }

            return $status;
        }
        
        return $status;

    }

    public static function wiki_add_user(){
        if( isset($_POST['wp_nonce_wiki_reg']) && wp_verify_nonce( $_POST['wp_nonce_wiki_reg'], 'user-registration' ) ){
            global $wpdb;
            $status = 1;
            if (isset($_POST['fname']) && isset($_POST['pass1']) && isset($_POST['pass2']) && isset($_POST['email1']) && isset($_POST['email2'])){
                if($_POST['pass1']==$_POST['pass2'] && $_POST['email1']==$_POST['email2']){
                    //escape 
                    $username=esc_sql($_POST['fname']);
                    $pass=esc_sql($_POST['pass1']);
                    $email=esc_sql($_POST['email1']);

                    $emails=$wpdb->get_results('SELECT email FROM wp_wiki_users');
                    foreach($emails as $one_email){
                        if($one_email->email === $email){
                            $status = 0;
                            break;
                        }
                    }

                    if($status){
                        $insert = $wpdb->query( $wpdb->prepare( 'INSERT INTO wp_wiki_users
                                              ( username, email, pass ) 
                                               VALUES ( %s, %s, %s )',
                                               $username,
                                               $email,
                                               wp_hash_password( $pass ) )
                                           );
                        return $insert;

                    } else {
                        return $status = 0;
                    }

                }else{
                    return $status = 0;
                }

            }else{
                return $status = 0;

            }
        }else {
            return $status = 0;
        }
       
    }
}