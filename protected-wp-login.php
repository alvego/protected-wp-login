<?php
/*
Plugin Name: Protected wp-login
Plugin URI: http://www.themext.com/protected-wp-login
Description: This plugin protects your admin's login page (secure key in url).
Author: Alex Tim
Version: 2.1
Author URI: http://www.themext.com/
*/
if ( is_admin() ) {
  /* Translations Page */
  load_plugin_textdomain( 'protected-wp-login', false, dirname(plugin_basename( __FILE__ )). '/languages/');
  
  /* Configuration Page */
  function protected_wp_login_admin_menu()
  {   
      add_options_page(
          __( 'Protected wp-login options', 'protected-wp-login' ),
          __( 'Protected wp-login', 'protected-wp-login' ),
          'manage_options',
          'protected-wp-login',
          'protected_wp_login_options'
      );
  }
  add_action( 'admin_menu','protected_wp_login_admin_menu' );
  
  // Processing the options
  function protected_wp_login_options(){
      // Security check
      if ( 
          function_exists( 'current_user_can' )
          && !current_user_can( 'manage_options' )
      ) {
          die( __( 'Cheatin&#8217; uh?' ) );
      }
      
      function protected_wp_login_secure_key_state(){ 
        return ( false === get_option('protected_wp_login_secure_key_enable') ) 
            ? ( '' === get_option('protected_wp_login_secure_key') ? '0' : '1' ) 
            : '1'; // for backward compatibility (v1.0)
      }
      
      /* Declare options */
      $protected_wp_login_options = array(
        'protected_wp_login_secure_key_enable' => array(
          'type' => 'checkbox',
          'default' =>  'protected_wp_login_secure_key_state',
          'title' => __( 'Enable protection:', 'protected-wp-login' )
        ),
        'protected_wp_login_secure_key' => array(
          'type' => 'text',
          'default' => '', 
          'title'=> __( 'Your secure key:', 'protected-wp-login' ),
        ),
        'protected_wp_login_secure_key_hide_loginform' => array(
          'type' => 'checkbox',
          'default' => '0',
          'title' => __( 'Enable stealth mode:', 'protected-wp-login' ),
          'desc' => __('Totally hide wp-login.php, if `sk` param not defined in GET', 'protected-wp-login')
        )
      );

      // Save changes
      if ( $_POST['action'] == 'protected_wp_login_update' ) {
          check_admin_referer( 'protected_wp_login-update-options' );
          
          
          foreach( $protected_wp_login_options as $option_name => $option_info ) {
            update_option( $option_name , $_POST[$option_name] );
          }

          if (
              isset( $_POST['protected_wp_login_secure_key_enable'] ) 
              && ( '0' !== $_POST['protected_wp_login_secure_key_enable'] )
          ) {
              $_POST['notice'] = __( 'Settings saved.' ) . ' ' . __( 'Use this link to login:', 'protected-wp-login' ) 
                  . '<br /><code> '
                  . esc_url( site_url( 'wp-login.php' ) )
                  . '/wp-login.php<strong>?sk'
                  . (( '' !== $_POST['protected_wp_login_secure_key']  ) ? '='  . esc_attr( $_POST['protected_wp_login_secure_key'] ) : '')
                  . '</strong></code>';
          } else {
              $_POST['notice'] = __( 'Settings saved.' ) . ' ' . __( 'Protection disabled.', 'protected-wp-login' );
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
          <?php screen_icon(); ?>
          <h2><?php _e( 'Protected wp-login options', 'protected-wp-login' ); ?></h2>
          <form method='post' action=''>
          <?php if( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'protected_wp_login-update-options'); } ?>
              <table class='form-table'>
                  <tbody>
                      <?php 
                      foreach( $protected_wp_login_options as $option_name => $option_info ) { 
                        $val = get_option($option_name);
                        if ( false ===$val ) {
                          $val = $option_info['default'];
                          if ( function_exists($val) ) {
                            $val = call_user_func($val);
                          }
                        }
                      ?>
                       <tr valign='top'>
                          <th scope='row'>
                              <label for='<?php echo esc_attr( $option_name ); ?>'><?php echo $option_info['title']; ?></label>
                          </th>
                          <td>
                            
                             <?php switch ( $option_info['type'] ) {
                                      case "checkbox":
                                        $checked = ( $val ? 'checked="checked" ' : '' );
                                        echo '<input type="hidden" name="' . $option_name . '" value="0"/>' . "\n";
                                        echo '<input type="checkbox" name="' . $option_name . '" value="1" id="' . $$option_name. '" ' . $checked . '/>' . "\n";
                                        break;
                                      default:
                                        echo '<input	name="' . $option_name. '" id="' . $option_name. '" type="' . esc_attr( $option_info['type'] ) . '" value="' . esc_attr( $val ) . '" class="regular-text" />' . "\n";
                                    }
                              ?>
                              <small><?php echo $option_info['desc']; ?></small>  
                          </td>
                      </tr>
                      <?php } ?>
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
  
}

if ( '1' === get_option('protected_wp_login_secure_key_enable')
      || (false === get_option('protected_wp_login_secure_key_enable') && '' !== get_option( 'protected_wp_login_secure_key' )) // for backward compatibility (v1.0)
) {
		if ('1' === get_option('protected_wp_login_secure_key_hide_loginform') 
			&& !isset($_REQUEST['sk']) && 'wp-login.php' == str_replace(str_replace('\\', '/', ABSPATH), '',str_replace('\\', '/', ''.$_SERVER['SCRIPT_FILENAME']))
		) {
     $action = isset($_GET['key']) ? 'resetpass': isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
		 if  ('login' === $action || (!in_array( $action, array( 'postpass', 'logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register' ), true ) && false === has_filter( 'login_form_' . $action ))){
				exit();
		 }
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
  function protected_wp_login_authenticate( $user ) {
      if (
          isset( $_POST['log'] ) && isset( $_POST['pwd'] ) // login form has been submitted
          &&  (
            !isset( $_POST['sk'] ) // not exists or invalid
            || get_option( 'protected_wp_login_secure_key' ) !== $_POST['sk']
          )
      ) {
          return new WP_Error( 'authentication_failed', __( 'Cheatin&#8217; uh?' ) );
      }
      return $user;
  }
  /* Attach plugin actions */
  function protected_wp_login(){
      add_action( 'login_form', 'protected_wp_login_form', 1000 );
      add_action( 'authenticate', 'protected_wp_login_authenticate', 1000, 1 );
  }
  add_action( 'login_init', 'protected_wp_login', 1000 );
}
?>