<?php
/*
Plugin Name: Auth Comments
Description: People are required to sign into Facebook to comment.  <a href="http://facebook.com/developers/editapp.php?new">Sign up for an API Key</a>.   The most important thing to remember is to change the callback url to your wordpress folder, everything else isn't very important.
Version: 0
Author: kevin
Author URI: http://kevinx.net/
*/


/*
 * Wrote this in a few hours, it doesn't even work that great.  It adds your name
 * to the author box in the comments, but I haven't yet figured out how to modify
 * the comment form html.  And I still don't know how to edit the comment form
 * data before it gets saved into the db.  So right now, people can just change their
 * names in the form manually. 
 *
 * And there's some bug where it requests a new auth token from Facebook every
 * time the page loads, I don't properly save the auth token.  And you can't really logout can you?
 *
 */
 

add_action('admin_menu'        , 'fb_add_pages');
add_action('admin_notices'     , 'fb_warning');
add_filter('comment_form'      , 'fb_comment_form');

function fb_add_pages(){
  add_options_page('Facebook API Key Config', 'Facebook API', 8, 'fb-config', 'fb_options_page');
}

function fb_warning(){
  if(!get_option('fb_api_key') && !isset($_POST['save']) )
    echo '<div id="message" class="updated"><p><strong>'. _('<a href="'. get_option('siteurl') .'/wp-admin/options-general.php?page=fb-config">Facebook API Key missing.</a>', 'mt_trans_domain' ) .'</strong></p></div>';
}

function fb_comment_form($postId=0){
 if( !get_option('fb_api_key') )
   return;
  
 $key    = get_option('fb_api_key');
 $secret = get_option('fb_api_secret');
    
 require 'client/facebook.php';
 $fb=new Facebook($key, $secret);

 $user = $fb->require_login();
 
 $nfo = $fb->api_client->users_getInfo( array($user), 'name, pic_small'  );
 extract($nfo[0]);
 
 echo '
  <script>
    document.getElementById("author").value="'. $name .'"
    document.getElementById("email").value="anonymous@noko.com"
  </script>
  <img src="'. get_option('siteurl') .'/wp-content/plugins/fbcomments/facebook_login.gif" alt="Facebook authenticated" title="Facebook authenticated" />
 '; 
}

function fb_options_page(){
   
  if(isset($_POST['save'])){
    update_option( 'fb_api_key'   , $_POST['api-key'] ); 
    update_option( 'fb_api_secret', $_POST['api-secret'] ); 
    echo '<div id="message" class="updated"><p><strong>'. _('Options saved!', 'mt_trans_domain' ) .'</strong></p></div>';
  }
  
    $key    = htmlspecialchars( get_option('fb_api_key') );
    $secret = htmlspecialchars( get_option('fb_api_secret') );
  
    echo '<div class="wrap">';
    echo "<h2>" . __( 'Facebook API Settings', 'mt_trans_domain' ) . "</h2>";
    
    ?>

  <form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
  
  <p><span style="display:block;float:left;width:100px;"><?php _e("API Key:", 'mt_trans_domain' ); ?></span>
  <input type="text" name="api-key" value="<?php echo $key; ?>" size="35" maxlength="32">
  </p>
  
  <p><span style="display:block;float:left;width:100px;"><?php _e("Secret:", 'mt_trans_domain' ); ?></span>
  <input type="text" name="api-secret" value="<?php echo $secret; ?>" size="35" maxlength="32">
  </p>
  
  <p class="submit">
  <input type="submit" name="save" value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" />
  </p>
  
  </form>
  </div>

<?php
   
}
  
  
?>