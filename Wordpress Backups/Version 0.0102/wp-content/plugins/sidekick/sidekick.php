<?php

/*
Plugin Name: Sidekick
Plugin URL: http://wordpress.org/plugins/sidekick/
Description: Adds a real-time WordPress training walkthroughs right in your Dashboard
Requires at least: 3.8
Tested up to: 4.0
Version: 1.5.5
Author: Sidekick.pro
Author URI: http://www.sidekick.pro
*/

define('SK_LIBRARY_VERSION',6);
define('DEFAULT_ACTIVATION_ID','xxxxxxxx-xxxx-xxxx-xxxx-xxxxfree');

define('SK_DOMAIN','http://pullvod.flowpress.netdna-cdn.com/');
define('SK_DOMAIN_SSL','https://pullvod-flowpress.netdna-ssl.com/');

define('SK_LIBRARY_DOMAIN','http://library.sidekick.pro/');
define('SK_LIBRARY_DOMAIN_SSL','https://library.sidekick.pro/');

if ( ! defined( 'SK_SL_PLUGIN_DIR' ) ) define( 'SK_SL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'SK_SL_PLUGIN_URL' ) ) define( 'SK_SL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
if ( ! defined( 'SK_SL_PLUGIN_FILE' ) ) define( 'SK_SL_PLUGIN_FILE', __FILE__ );
if ( ! function_exists('mlog')) {
	function mlog(){}
}

class Sidekick{

	function __construct(){
		global $SK_FREE_LIBRARY_FILE, $SK_PAID_LIBRARY_FILE;
		$activation_id = get_option("sk_activation_id");

		$protocol = $this->protocol();
		$this->check_versions();

		$SK_FREE_LIBRARY_FILE = SK_DOMAIN_USED . "library/v" . SK_LIBRARY_VERSION . "/releases/xxxxxxxx-xxxx-xxxx-xxxx-xxxxfree/library.js";
		if ($activation_id) {
			$SK_PAID_LIBRARY_FILE = SK_DOMAIN_USED . "library/v" . SK_LIBRARY_VERSION . "/releases/{$activation_id}/library.js";
		}
	}

	function enqueue_required(){
		wp_enqueue_script('jquery'                      , null );
		wp_enqueue_script('underscore'                  , null, array('underscore'));
		wp_enqueue_script('backbone'                    , null, array('jquery','underscore'));
		wp_enqueue_script('jquery-ui-core'				, null, array('jquery') );
		wp_enqueue_script('jquery-ui-position'			, null, array('jquery-ui-core') );
		wp_enqueue_script('jquery-ui-draggable'			, null, array('jquery-ui-core') );
		wp_enqueue_script('jquery-ui-droppable'			, null, array('jquery-ui-core') );
		wp_enqueue_script('jquery-effects-scale'		, null, array('jquery-ui-core') );
		wp_enqueue_script('jquery-effects-highlight'	, null, array('jquery-ui-core') );
	}

	function protocol() {
		if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 || $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
			define('SK_DOMAIN_USED',SK_DOMAIN_SSL);
			define('SK_LIBRARY_DOMAIN_USED',SK_LIBRARY_DOMAIN_SSL);
			return 'https://';
		} else {
			define('SK_DOMAIN_USED',SK_DOMAIN);
			define('SK_LIBRARY_DOMAIN_USED',SK_LIBRARY_DOMAIN);
			return 'http://';
		}
	}

	function enqueue(){
		global $SK_FREE_LIBRARY_FILE, $SK_PAID_LIBRARY_FILE;

		$activation_id = get_option("sk_activation_id");

		if (strpos($_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'], '.sidekick')) {
			$version = time();
		} else {
			$version = date('m-d-y-G');
		}

		if ($activation_id) {
			wp_enqueue_script("sk_paid_library" , $SK_PAID_LIBRARY_FILE,null,$version);
			wp_enqueue_script("sk_free_library" , $SK_FREE_LIBRARY_FILE,array('sk_paid_library'),$version);
		} else {
			wp_enqueue_script("sk_free_library" , $SK_FREE_LIBRARY_FILE,array(),$version);
		}

		wp_enqueue_script('sidekick'   		,SK_DOMAIN_USED . "cdn/platform/wordpress/sidekick.min.js",	array('sk_free_library','backbone','jquery','underscore','jquery-effects-highlight'), get_transient( 'sk_platform_version' ));
		wp_enqueue_script('player'         	,SK_DOMAIN_USED . "cdn/player/wordpress/js/sk.min.js",		array('sidekick')	,get_transient( 'sk_player_version' ));

		wp_enqueue_style('sk-style'    		,SK_DOMAIN_USED . "cdn/player/wordpress/css/sidekick_wordpress.css",	null 				,get_transient( 'sk_player_version' ));

		wp_enqueue_style('wp-pointer');
		wp_enqueue_script('wp-pointer');
	}

	function setup_menu(){
		add_submenu_page( 'options-general.php', 'Sidekick', 'Sidekick', 'activate_plugins','sidekick', array(&$this,'admin_page'));
	}

	function ajax_save(){
		if (isset($_POST['sk_composer_button']) && $_POST['sk_composer_button'] == "true") {
			update_option( 'sk_composer_button', true );
		} elseif (isset($_POST['sk_composer_button']) && $_POST['sk_composer_button'] == "false") {
			delete_option('sk_composer_button');
		}
	}

	function admin_page(){
		global $SK_PAID_LIBRARY_FILE, $SK_FREE_LIBRARY_FILE ;

		if ( empty( $_POST ) || check_admin_referer( 'update_sk_settings' ) ) {

			if (isset($_POST['option_page']) && $_POST['option_page'] == 'sk_license') {

				if (isset($_POST['first_name']) && $_POST['first_name'])
					update_option('sk_first_name',$_POST['first_name']);

				if (isset($_POST['email']) && $_POST['email'])
					update_option('sk_email',$_POST['email']);

				if (isset($_POST['activation_id']) && $_POST['activation_id']){
					$result = $this->activate(true);
				} else {
					delete_option('sk_activation_id');
				}

				if (isset($_POST['sk_composer_button'])) {
					update_option( 'sk_composer_button', true );
				} else {
					delete_option('sk_composer_button');
				}

				if (isset($_POST['sk_track_data'])) {
					update_option( 'sk_track_data', true );
				} else {
					delete_option('sk_track_data');
				}

				update_option( 'sk_activated', true );
				die('<script>window.open("' . get_site_url() . '/wp-admin/options-general.php?page=sidekick","_self")</script>');
			}

			if (isset($_POST['sk_autostart_walkthrough_id']) && $_POST['sk_autostart_walkthrough_id']){
				update_option('sk_autostart_walkthrough_id',$_POST['sk_autostart_walkthrough_id']);
			}
		}

		$activation_id = get_option( 'sk_activation_id' );
		$email         = get_option( 'sk_email' );
		$first_name    = get_option( 'sk_first_name' );
		$sk_track_data = get_option( 'sk_track_data' );
		$current_user  = wp_get_current_user();
		$status        = 'Free';
		$error         = null;

		if (isset($SK_PAID_LIBRARY_FILE) && $activation_id) {
			$_POST['activation_id'] = $activation_id;
			$check_activation       = $this->activate(true);
			$status = 'Checking...';
		}

		if (!$first_name) $first_name = $current_user->user_firstname;
		if (!$email) $email           = $current_user->user_email;

		global $wp_version;
		if (version_compare($wp_version, '3.7', '<=')) {
			$error = "Sorry, Sidekick requires WordPress 3.7 or higher to function.";
		}

		if (!$activation_id) {
			$warn = "You're using the <b>free</b> version of Sidekick, to gain full access to the walkthrough library please <a target='_blank' href='http://www.sidekick.pro/wordpress/modules/wordpress-core-module-premium/?utm_source=plugin&utm_medium=settings&utm_campaign=upgrade_nag'>upgrade</a> to the full module.";
		}

		if(preg_match('/(?i)msie [6-8]/',$_SERVER['HTTP_USER_AGENT'])){
			$error = "Sorry, Sidekick requires Internet Explorer 9 or higher to function.";
		}

		?>

		<?php if (get_option('sk_firstuse') == true): ?>
			<?php delete_option('sk_firstuse') ?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					jQuery('#sidekick #logo').trigger('click');
				});
			</script>
		<?php endif ?>

		<div class="wrap">
			<?php include('libs/admin_page.php') ?>
		</div>
		<?php
	}

	function set_disabled_wts(){
		update_option('sk_disabled_wts',json_encode($_POST['disable_wts']));
	}

	function footer(){
		global $current_user, $SK_FREE_LIBRARY_FILE, $SK_PAID_LIBRARY_FILE;

		require_once('libs/sk_config_data.php');

		$plugin_data              = get_plugin_data(plugin_dir_path( dirname( __FILE__ ) ) . 'sidekick/sidekick.php');
		$sk_config_data           = new sk_config_data;
		$current_user             = wp_get_current_user();
		$sk_just_activated        = get_option( 'sk_just_activated' );
		$sk_track_data            = get_option( 'sk_track_data' );
		$sk_composer_button       = get_option( 'sk_composer_button' );
		$activation_id            = (get_option( "sk_activation_id" ) ? get_option( "sk_activation_id" ) : DEFAULT_ACTIVATION_ID);
		$autostart_walkthrough_id = (get_option('sk_autostart_walkthrough_id') ? get_option('sk_autostart_walkthrough_id') : 'null' );
		$theme                    = wp_get_theme();
		$not_supported_ie         = false;
		$user_email               = '';
		if ($sk_track_data) {
			$user_email = $current_user->user_email;
		}

		$user_role               = $sk_config_data->get_user_role();
		$site_url                = $sk_config_data->get_domain();
		$installed_plugins       = $sk_config_data->get_plugins();
		$disabled_wts            = $sk_config_data->get_disabled_wts();
		$current_url             = $sk_config_data->get_current_url();
		$post_types              = $sk_config_data->get_post_types();
		$taxonomies              = $sk_config_data->get_taxonomies();
		$user_data               = $sk_config_data->get_user_data();
		$comments                = $sk_config_data->get_comments();
		$post_statuses           = $sk_config_data->get_post_statuses();
		$post_types_and_statuses = $sk_config_data->get_post_types_and_statuses();
		$number_of_themes        = $sk_config_data->get_themes();

		// $sk_composer_button = true; // BETA

		delete_option( 'sk_just_activated' );
		if(preg_match('/(?i)msie [6-8]/',$_SERVER['HTTP_USER_AGENT'])) $not_supported_ie = true;

		?>

		<?php if (!$not_supported_ie): ?>

			<script type="text/javascript">

				var sk_config = {
					show_powered_by:          true,
					show_powered_by_link:     true,
					use_native_controls:      false,
					main_soft_name:           'WordPress',
					domain_used:              '<?php echo SK_DOMAIN_USED ?>',
					domain:                   '<?php echo str_replace("http://","",$_SERVER["SERVER_NAME"]) ?>',
					base_url:                 '<?php echo site_url() ?>',
					site_url:                 '<?php echo $site_url ?>',
					main_soft_version:        '<?php echo get_bloginfo("version") ?>',
					plugin_url:               '<?php echo admin_url("admin.php?page=sidekick") ?>',
					current_url:              '<?php echo $current_url ?>',
					theme_version:            '<?php echo $theme->Version ?>',
					installed_theme:          '<?php echo $theme->Name ?>',
					library_free_file:        '<?php echo (isset($SK_FREE_LIBRARY_FILE) ? $SK_FREE_LIBRARY_FILE : '') ?>',
					library_paid_file:        '<?php echo (isset($SK_PAID_LIBRARY_FILE) ? $SK_PAID_LIBRARY_FILE : '') ?>',
					library_version:          '<?php echo (defined("SK_LIBRARY_VERSION") ? SK_LIBRARY_VERSION : '') ?>',
					plugin_version:           '<?php echo $plugin_data["Version"] ?>',
					plugin_version_online:    '<?php echo get_transient("sk_player_version") ?>',
					platform_version:         '<?php echo get_transient("sk_platform_version") ?>',
					track_data:               '<?php echo $sk_track_data ?>',
					user_level:               '<?php echo $user_role ?>',
					user_email:               '<?php echo $user_email ?>',
					activation_id:            '<?php echo $activation_id ?>',
					autostart_walkthrough_id: <?php echo $autostart_walkthrough_id ?>,
					sk_composer_button:       <?php echo ($sk_composer_button ? "true" : "false") ?>,
					just_activated:           <?php echo ($sk_just_activated) ? "true" : "false" ?>,
					show_login:               <?php echo ($sk_just_activated) ? "true" : "false" ?>,
					installed_plugins:        <?php echo $installed_plugins ?>,
					disable_wts:              <?php echo $disabled_wts ?>,
					is_multisite:             <?php echo (is_multisite()) ? "true" : "false" ?>,
					number_of_themes:         <?php echo $number_of_themes ?>,
					<?php echo $post_types ?>
					<?php echo $taxonomies ?>
					<?php echo $user_data ?>
					<?php echo $comments ?>
					<?php echo $post_statuses ?>
					<?php echo $post_types_and_statuses ?>
					sk_autostart_only_once:   true,
					auto_open_root_bucket_id: 79
				}

				var skc_config = {
					url:         '<?php echo SK_DOMAIN_USED ?>cdn/composer/',
					apiUrl:      '<?php echo SK_LIBRARY_DOMAIN_USED ?>api',
					baseSiteUrl: sk_config.base_url
				}

				console.log('Sidekick Config Setup');

			</script>
		<?php endif ?>
		<?php
	}

	function track($data){
		$protocol = $this->protocol();

		$response = wp_remote_post( SK_LIBRARY_DOMAIN_USED . "wp-admin/admin-ajax.php", array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => $data,
			'cookies' => array()
			)
		);
	}

	function activate($return = false){
		mlog("activate");
		if ($_POST['activation_id']) {

			$protocol = $this->protocol();

			$library_file = SK_DOMAIN_USED . "library/v" . SK_LIBRARY_VERSION . "/releases/{$_POST['activation_id']}/library.js";
			$ch = curl_init($library_file);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_exec($ch);
			$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			update_option('sk_activation_id',$_POST['activation_id']);
			if ($retcode == 200) {
				if ($return)
					return 1;
				die(json_encode(array('success' => 1)));
			} else {
				if ($return)
					return $retcode;
				die(json_encode(array('error' => $retcode)));
			}
		} else {
			die(json_encode(array('error' => 'No Activation ID')));
		}
	}

	function activate_plugin(){
		update_option( 'sk_firstuse', true );
		update_option( 'sk_do_activation_redirect', true );
		$data = array(
			'source' => 'plugin',
			'action' => 'track',
			'type' => 'activate'
			);
		$this->track($data);
	}

	function check_versions(){
		$protocol = $this->protocol();
		$url = SK_DOMAIN_USED . "cdn/player/wordpress/version";

		if ( false === ( $player_version = get_transient( 'sk_player_version' ) ) ) {
			if (ini_get('allow_url_fopen')) {
				$player_version = file_get_contents($url);
				set_transient( 'sk_player_version', $player_version, 2 * HOUR_IN_SECONDS );
			} elseif (function_exists('curl_version')){
				$player_version = $this->curl_get_data($url);
				set_transient( 'sk_player_version', $player_version, 2 * HOUR_IN_SECONDS );
			}
		}

		$url = SK_DOMAIN_USED . "cdn/platform/wordpress/version";

		if ( false === ( $platform_version = get_transient( 'sk_platform_version' ) ) ) {
			if (ini_get('allow_url_fopen')) {
				$platform_version = file_get_contents($url);
				set_transient( 'sk_platform_version', $platform_version, 2 * HOUR_IN_SECONDS );
			} elseif (function_exists('curl_version')){
				$platform_version = $this->curl_get_data($url);
				set_transient( 'sk_platform_version', $platform_version, 2 * HOUR_IN_SECONDS );
			}
		}
	}

	function curl_get_data($url){
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	function redirect(){
		if (get_option('sk_do_activation_redirect', false)) {
			delete_option('sk_do_activation_redirect');
			$siteurl = get_site_url();
			wp_redirect($siteurl . "/wp-admin/options-general.php?page=sidekick");
			die();
		}
	}

	function admin_notice() {
		global $current_user ;

		if ( ! get_user_meta($current_user->ID, 'sk_ignore_notice') ) {
			printf ('<div class="updated"><p>Need help with WordPress? Click HELP ME in the bottom left corner to get started! <a href="%1$s">Hide</a></p></div>','?sk_ignore_notice=1');
		}
	}

	function admin_notice_ignore() {
		global $current_user;
		if ( isset($_GET['sk_ignore_notice'])) {
			add_user_meta($current_user->ID, 'sk_ignore_notice', true);
		}
	}

	function deactivate_plugin(){
		$sk_track_data = get_option( 'sk_track_data' );
		if ($sk_track_data) {
			$data = array(
				'source' => 'plugin',
				'action' => 'track',
				'type' => 'deactivate',
				'user' => get_option( "activation_id" )
				);
			$this->track($data);
			?>
			<script type="text/javascript">
				window._gaq = window._gaq || [];
				window._gaq.push(['sk._setAccount', 'UA-39283622-1']);

				(function() {
					var ga_wpu = document.createElement('script'); ga_sk.type = 'text/javascript'; ga_sk.async = true;
					ga_sk.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
					var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga_wpu, s);
				})();
				window._gaq.push(['sk._trackEvent', 'Plugin - Deactivate', '', <?php echo plugin_version ?>, 0,true]);
			</script>
			<?php
		}
		delete_option( 'sk_activation_id' );
		delete_option( 'sk_first_name' );
		delete_option( 'sk_email' );
		delete_option( 'sk_activated' );
	}
}

$sidekick = new Sidekick;
register_activation_hook( __FILE__, array($sidekick,'activate_plugin') );
register_deactivation_hook( __FILE__, array($sidekick,'deactivate_plugin')  );

add_action('admin_menu', array($sidekick,'setup_menu'));
add_action('admin_init', array($sidekick,'redirect'));
add_action('wp_ajax_sk_activate', array($sidekick,'activate'));
add_action('wp_ajax_sk_save', array($sidekick,'ajax_save'));
add_action('admin_notices', array($sidekick,'admin_notice'));
add_action('admin_init', array($sidekick,'admin_notice_ignore'));

if (isset($_POST['disable_wts'])) {
	$sidekick->set_disabled_wts();
}

if (!defined('SK_PLUGIN_DEGBUG'))
	require_once('sk_init.php');

if (!(isset($_GET['tab']) && $_GET['tab'] == 'plugin-information') && !defined('IFRAME_REQUEST')) {
	add_action('admin_footer', array($sidekick,'footer'));
	add_action('customize_controls_print_footer_scripts', array($sidekick,'footer'));
}