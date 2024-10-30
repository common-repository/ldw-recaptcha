<?php
/*
Plugin Name:	LDW reCAPTCHA
Description:	The most lightweight Google reCAPTCHA v2 comment anti-spam plugin!
Version:    	1.0.0
Author:     	Lake District Walks
Text Domain: 	ldw-recaptcha
Domain Path:	/lang
*/

add_action('init', 'user_and_key_checks');

function user_and_key_checks() {
	// only display recaptcha is user is not logged in and the keys are entered
	$options = get_option( 'ldw_recaptcha_settings' );
	if (!is_user_logged_in() && (strlen($options['ldw_recaptcha_text_field_0']) == 40 && strlen($options['ldw_recaptcha_text_field_1']) == 40)) {
		add_action('wp_enqueue_scripts', 'enqueue_scripts');
		add_action( 'comment_form', 'display_recaptcha' );
		add_filter( 'preprocess_comment', 'verify_captcha' );
	}
}

function enqueue_scripts() {
	wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js', array(), false, true );
	wp_enqueue_script( 'recaptcha-display-buttons', plugin_dir_url(__FILE__) . 'ldw-recaptcha.js', array('jquery'), false, true);
	wp_enqueue_style( 'recaptcha-hide-buttons', plugin_dir_url(__FILE__) . 'ldw-recaptcha.css');
}

function display_recaptcha() {
 	$options = get_option( 'ldw_recaptcha_settings' );
	echo '<div class="g-recaptcha" data-sitekey="' . $options['ldw_recaptcha_text_field_0'] . '" data-callback="recaptcha_callback"></div>';
}

function verify_captcha( $commentdata ) {
	// do not check pingbacks and trackbacks
    if ($comment_data['comment_type'] == '') {
		$options = get_option( 'ldw_recaptcha_settings' );
		if( isset( $_POST['g-recaptcha-response'] ) ) {
			$response = json_decode(wp_remote_retrieve_body( wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?secret=" . $options['ldw_recaptcha_text_field_1'] . "&response=" . $_POST['g-recaptcha-response'] ) ), true );
			if( !$response["success"] ) {
				// mark as spam if failed recaptcha
				add_filter('pre_comment_approved', create_function('$a', 'return "spam";'));
			}
		} else {
			// mark as spam if no recaptcha response
			add_filter('pre_comment_approved', create_function('$a', 'return "spam";'));
		}
	}
	return $commentdata;
}

add_action( 'admin_menu', 'ldw_recaptcha_add_admin_menu' );
add_action( 'admin_init', 'ldw_recaptcha_settings_init' );

function ldw_recaptcha_add_admin_menu(  ) {	
	$page = add_options_page( 'LDW reCAPTCHA', 'LDW reCAPTCHA', 'manage_options', 'ldw_recaptcha', 'ldw_recaptcha_options_page' );
    add_action( 'admin_print_styles-' . $page, 'ldw_recaptcha_admin_styles' );
}

function ldw_recaptcha_admin_styles() {
       wp_enqueue_style( 'ldw_recaptcha_stylesheet' );
}

function ldw_recaptcha_settings_init(  ) { 
    wp_register_style( 'ldw_recaptcha_stylesheet', plugins_url('style.css', __FILE__) );
	register_setting( 'ldw_recaptcha_plugin_page', 'ldw_recaptcha_settings' );
	add_settings_section(
		'ldw_recaptcha_plugin_page_section', 
		__( 'Google reCAPTCHA Keys', 'ldw-recaptcha' ), 
		'ldw_recaptcha_settings_section_callback', 
		'ldw_recaptcha_plugin_page'
	);
	add_settings_field( 
		'ldw_recaptcha_text_field_0', 
		__( 'Site Key', 'ldw-recaptcha' ), 
		'ldw_recaptcha_text_field_0_render', 
		'ldw_recaptcha_plugin_page', 
		'ldw_recaptcha_plugin_page_section' 
	);
	add_settings_field( 
		'ldw_recaptcha_text_field_1', 
		__( 'Secret Key', 'ldw-recaptcha' ), 
		'ldw_recaptcha_text_field_1_render', 
		'ldw_recaptcha_plugin_page', 
		'ldw_recaptcha_plugin_page_section' 
	);
}

function ldw_recaptcha_text_field_0_render(  ) { 
	$options = get_option( 'ldw_recaptcha_settings' );
	?>
	<input type='text' name='ldw_recaptcha_settings[ldw_recaptcha_text_field_0]' value='<?php echo $options['ldw_recaptcha_text_field_0']; ?>' size="50" maxlength="40" pattern="^[a-zA-Z0-9-_]+$" />
	<?php
}

function ldw_recaptcha_text_field_1_render(  ) { 
	$options = get_option( 'ldw_recaptcha_settings' );
	?>
	<input type='text' name='ldw_recaptcha_settings[ldw_recaptcha_text_field_1]' value='<?php echo $options['ldw_recaptcha_text_field_1']; ?>' size="50" maxlength="40" pattern="^[a-zA-Z0-9-_]+$" />
	<?php
}

function ldw_recaptcha_settings_section_callback(  ) { 
	echo __( 'You need to register for free with <a href="https://www.google.com/recaptcha/" target="_blank">Google reCAPTCHA</a> to get your keys, which you must then enter below!', 'ldw-recaptcha' );
}

function ldw_recaptcha_options_page(  ) { 
	?><h1>LDW reCAPTCHA</h1>
	<table style="width: 100%;">
		<tr>
			<td style="width: 50%;">
				<form action='options.php' method='post'>
					<?php
					settings_fields( 'ldw_recaptcha_plugin_page' );
					do_settings_sections( 'ldw_recaptcha_plugin_page' );
					submit_button();
					?>
				</form>
			</td>
			<td>
				<div style="text-align: center;">
					<h4>Thanks for using LDW reCAPTCHA :)</h4>
				</div>
			</td>
		</tr>
	</table>
	<?php
}
?>
