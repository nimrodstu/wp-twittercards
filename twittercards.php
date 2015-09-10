<?php
/**
 * Plugin Name: wp twitter cards by nimrodstu
 * Description: adds twitter cards
 * Version: 0.1
 * Author: nimrodstu
 * Author URI: http://nimrodstu.com
 * License: GPL2
 */


//new menu item
function wptcbn_menu() {
	add_options_page( 'WP Twitter Card Options', 'WP Twitter Cards', 'manage_options', 'twitter-identifier', 'wptcbn_options' );
}
add_action( 'admin_init', 'wptcbn_settings' );

//new page options
function wptcbn_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	} ?>
	<div class="wrap">
	<h2>Twitter Card Options</h2>
		<form method="post" action="options.php">
		    <?php settings_fields( 'wptcbn-settings-group' ); ?>
		    <?php do_settings_sections( 'wptcbn-settings-group' ); ?>
		    <table class="form-table">
		        <tr valign="top">
			        <th scope="row">Twitter Card Type</th>
			        <td>
			        	<select name="twitter_card" value="<?php esc_attr( get_option('twitter_card') ); ?>">
							<option value="summary" <?php selected( 'summary' == get_option('twitter_card') ); ?>>Summary Card</option>
							<option value="summary_large_image" <?php selected( 'summary_large_image' == get_option('twitter_card') ); ?>>Summary Card with Large Image</option>
					    </select>
			        </td>
		        </tr>
		        <tr valign="top">
			        <th scope="row">Twitter Author Username</th>
			        <td><input type="text" name="twitter_name" value="<?php echo esc_attr( get_option('twitter_name') ); ?>" /></td>
		        </tr>
		        <tr valign="top">
			        <th scope="row">Twitter Site Username</th>
			        <td><input type="text" name="twitter_site" value="<?php echo esc_attr( get_option('twitter_site') ); ?>" /></td>
		        </tr>
				<tr valign="top">
					<th scope="row">Default Image</th>
					<td><label for="twitter_default_image">
					    <input id="twitter_default_image" type="text" size="36" name="twitter_default_image" value="<?php echo esc_attr( get_option('twitter_default_image') ); ?>" /> 
					    <input id="upload_image_button" class="button" type="button" value="Upload Image" />
					    <br />Enter a URL or upload an image to be used if no featured image
					</label></td>
				</tr>
				<tr valign="top"><br />
			        <th scope="row">Twitter Usernames</th>
			    	<td><input type="checkbox" name="twitter_field" value="false"<?php if (get_option('twitter_field')==true) echo 'checked="checked" '; ?>>Add Twitter username field to user profiles (multiple authors)</td>
				</tr>
		    </table>
		    <?php submit_button(); ?>
		</form>
	</div>
<?php }
add_action( 'admin_menu', 'wptcbn_menu' );

//register required fields
function wptcbn_settings() {
	register_setting( 'wptcbn-settings-group', 'twitter_card' );
	register_setting( 'wptcbn-settings-group', 'twitter_name' );
	register_setting( 'wptcbn-settings-group', 'twitter_site' );
	register_setting( 'wptcbn-settings-group', 'twitter_default_image' );
	register_setting( 'wptcbn-settings-group', 'twitter_field' );
}

//load javascript to pages head
function wptcbn_head() { ?>
	<?php
	if(is_single() || is_page()) {
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$twitter_card    = esc_attr( get_option('twitter_card') );
				$twitter_url    = get_permalink();
			   	$twitter_title  = get_the_title();
			   	$excerpt = strip_tags(get_the_excerpt());
			 	$twitter_desc   = $excerpt;
			   	$twitter_thumbs = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), full );
			    $twitter_thumb  = $twitter_thumbs[0];
			    if(!$twitter_thumb) {
			      	$twitter_thumb = esc_attr( get_option('twitter_default_image') );
			    }
			    if (get_option('twitter_field')==true){
				  	$twitter_name   = str_replace('@', '', get_the_author_meta('twittercard_twitter'));
				}
			  	if(!$twitter_name) {
			      	$twitter_name = str_replace('@', '', esc_attr( get_option('twitter_name') ));
			    }
			    $twitter_site = str_replace('@', '', esc_attr( get_option('twitter_site') ));
				?>
				<meta name="twitter:card" content="<?php echo $twitter_card; ?>" />
				<meta name="twitter:url" content="<?php echo $twitter_url; ?>" />
				<meta name="twitter:title" content="<?php echo $twitter_title; ?>" />
				<meta name="twitter:description" value="<?php echo $twitter_desc; ?>" />
				<meta name="twitter:image" content="<?php echo $twitter_thumb; ?>" />
				<meta name="twitter:site" content="@<?php echo $twitter_site; ?>" />
				<meta name="twitter:creator" content="@<?php echo $twitter_name; ?>" />
			<?php } // end while
		} // end if
	} 
}
add_action('wp_head', 'wptcbn_head');

//add scripts
function wptcbn_scripts() {
    if (isset($_GET['page']) && $_GET['page'] == 'twitter-identifier') {
        wp_enqueue_media();
        wp_register_script('twittercard-js', WP_PLUGIN_URL.'/twittercards/twittercard.js', array('jquery'));
        wp_enqueue_script('twittercard-js');
    }
}
add_action('admin_enqueue_scripts', 'wptcbn_scripts');

//if checked add twitter field to user profiles
if (get_option('twitter_field')==true){
	add_action( 'show_user_profile', 'wptcbn_extra_user_profile_fields' );
	add_action( 'edit_user_profile', 'wptcbn_extra_user_profile_fields' );
	add_action( 'personal_options_update', 'wptcbn_save_extra_user_profile_fields' );
	add_action( 'edit_user_profile_update', 'wptcbn_save_extra_user_profile_fields' );
}
 
//only user can change twitter field
function wptcbn_save_extra_user_profile_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
		update_user_meta( $user_id, 'twittercard_twitter', $_POST['twittercard_twitter'] );
}

//add field to profile
function wptcbn_extra_user_profile_fields( $user ) { ?>
	<h3>Twitter</h3>
	<table class="form-table">
		<tr>
		<th><label for="twittercard_twitter">Twitter User Name</label></th>
		<td>
		<input type="text" id="twittercard_twitter" name="twittercard_twitter" size="20" value="<?php echo esc_attr( get_the_author_meta( 'twittercard_twitter', $user->ID )); ?>">
		<span class="description">Please enter your Twitter Account User name, eg: nimrodstu</span>
		</td>
		</tr>
	</table>
<?php } ?>