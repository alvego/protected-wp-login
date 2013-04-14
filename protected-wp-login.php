<?php
/*
Plugin Name: Protected wp-login
Plugin URI: http://www.themext.com/protected-wp-login
Description: This plugin protect your admin's login page (secure key in url).
Author: alvego, infous
Version: 1.0
Author URI: http://www.themext.com/
*/


/* Translations Page */
function protected_wp_login_translation_file() {
  if (is_admin()) {
    load_plugin_textdomain( 'protected-wp-login', false, dirname(plugin_basename( __FILE__ )). '/languages/');
  }
}
add_action('plugins_loaded', 'protected_wp_login_translation_file');

/* Configuration Page */
function protected_wp_login_add_option_page()
{   
    add_options_page(
        __( 'Protected wp-login options', 'protected-wp-login' ),
        __( 'Protected wp-login', 'protected-wp-login' ),
        'manage_options',
        'protected-wp-login',
        'protected_wp_login_options'
    );
}
add_action( 'admin_menu','protected_wp_login_add_option_page' );

// Processing the options
function protected_wp_login_options(){
    // Security check
    if ( 
        function_exists( 'current_user_can' )
        && !current_user_can( 'manage_options' )
    ) {
        die( __( 'Cheatin&#8217; uh?' ) );
    }
    
    // Save changes
    if ( $_POST['action'] == 'protected_wp_login_update' ) {

        check_admin_referer( 'protected_wp_login-update-options' );
        
        update_option( 'protected_wp_login_secure_key' , $_POST['protected_wp_login_secure_key'] );
        
        if (
            isset( $_POST['protected_wp_login_secure_key'] ) 
            && ( '' !== $_POST['protected_wp_login_secure_key'] )
        ) {
            $_POST['notice'] = __( 'Use this link to login:', 'protected-wp-login' ) 
                . '<br /><code> '
                . esc_url( site_url( 'wp-login.php' ) )
                . '/wp-login.php<strong>?sk='
                . esc_attr( $_POST['protected_wp_login_secure_key'] ) 
                . '</strong></code>';
        } else {
            $_POST['notice'] = __( 'Your secure key is empty. Plugin now disabled.', 'protected-wp-login' );
        }
    }
    // Show notice
    if( $_POST['notice'] ) 
    { 
        ?>
        <div id='message' class='updated fade'><p><?php echo $_POST['notice']; ?></p></div> 
        <?php 
    } 
    // Show options
    ?>
    <div class='wrap'>
        <div id="icon-options-general" class="icon32"><br></div>
        <h2><?php _e( 'Protected wp-login options', 'protected-wp-login' ); ?></h2>
        <form method='post' action=''>
        <?php if( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'protected_wp_login-update-options'); } ?>
            <table class='form-table'>
                <tbody>
                     <tr valign='top'>
                        <th scope='row'>
                            <label for='protected_wp_login_secure_key'><?php _e( 'Your secure key:', 'protected-wp-login' ); ?></label>
                        </th>
                        <td>
                            <input name='protected_wp_login_secure_key' id='protected_wp_login_secure_key' value='<?php echo esc_attr( get_option( 'protected_wp_login_secure_key' ) );?>' type='text' />
                            <small><?php _e( 'Tip: Leave blank to disable plugin.', 'protected-wp-login' ); ?></small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope='row'></th>
                        <td>
                            <input name='Submit' class='button button-primary' value='<?php _e( 'Save Changes' ); ?>' type='submit' />
                            <input name='action' value='protected_wp_login_update' type='hidden' />
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
    <?php
}

/* Forwarding GET param to POST for safe LoginForm redirect */
function protected_wp_login_form() { 
    if ( isset( $_REQUEST['sk'] ) ) { 
        ?>
        <input type='hidden' name='sk' value='<?php echo esc_attr( $_REQUEST['sk'] ); ?>' />
        <?php 
    }
}

/* Authenticate check */
function protected_wp_login_check( $user ) {
    if (
        isset( $_POST['log'] ) && isset( $_POST['pwd'] ) // login form has been submitted
        &&  (
          !isset( $_POST['sk'] ) // not exists or invalid
          || $_POST['sk'] !== get_option( 'protected_wp_login_secure_key' )
        )
    ) {
        sleep( 3 ); // froze process on 3 seconds ( anti brute force )
        return new WP_Error( 'authentication_failed', __( 'Cheatin&#8217; uh?' ) );
    }
    return $user;
}

/* Attach plugin actions */
function protected_wp_login(){
    if( '' !== get_option('protected_wp_login_secure_key') )
    {
        add_action( 'login_form', 'protected_wp_login_form', 1000 );
        add_action( 'authenticate', 'protected_wp_login_check', 1000, 1 );
    }
}
add_action( 'login_init', 'protected_wp_login', 1000 );

?>