<?php

class sk_config_data{
	function get_domain(){
		$site_url = get_site_url();
		if(substr($site_url, -1) == '/') {
			$site_url = substr($site_url, 0, -1);
		}
		$site_url = str_replace(array("http://","https://"),array(""),$site_url);
		return $site_url;
	}

	function get_post_types(){
		global $wpdb;
		$query = "SELECT post_type, count(distinct ID) as count from {$wpdb->prefix}posts group by post_type";
		$counts = $wpdb->get_results($query);

		foreach ($counts as $key => $type) {
			$type->post_type = str_replace('-', '_', $type->post_type);
			$output .= "\n 						post_type_{$type->post_type} : $type->count,";
		}
		return $output;
	}

	function get_themes(){
		$themes = wp_get_themes( array( 'allowed' => true ) );
		return count($themes);
	}

	function get_post_types_and_statuses(){
		global $wpdb;
		$query = "SELECT post_type, post_status, count(distinct ID) as count from wp_posts group by post_type, post_status";
		$counts = $wpdb->get_results($query);

		foreach ($counts as $key => $type) {
			$type->post_type   = str_replace('-', '_', $type->post_type);
			$type->post_status = str_replace('-', '_', $type->post_status);

			$output .= "\n 						post_type_{$type->post_type}_{$type->post_status} : $type->count,";
		}
		return $output;
	}

	function get_taxonomies(){
		global $wpdb;
		$query = "SELECT count(distinct term_taxonomy_id) as count, taxonomy from {$wpdb->prefix}term_taxonomy group by taxonomy";
		$counts = $wpdb->get_results($query);

		foreach ($counts as $key => $taxonomy) {
			$taxonomy->taxonomy = str_replace('-', '_', $taxonomy->taxonomy);
			$output .= "\n 						taxonomy_{$taxonomy->taxonomy} : $taxonomy->count,";
		}
		return $output;
	}

	function get_comments(){
		global $wpdb;
		$query = "SELECT count(distinct comment_ID) as count from {$wpdb->prefix}comments";
		$counts = $wpdb->get_var($query);
		if (!$counts) $counts = 0;
		return "\n 						comment_count : $counts,";
	}

	function get_post_statuses(){
		global $wpdb;
		$query = "SELECT post_status, count(ID) as count from {$wpdb->prefix}posts group by post_status";
		$counts = $wpdb->get_results($query);

		foreach ($counts as $key => $type) {
			$type->post_status = str_replace('-', '_', $type->post_status);
			$output .= "\n 						post_status_{$type->post_status} : $type->count,";
		}
		return $output;
	}

	function get_user_data(){
		global $current_user;
		$data = get_userdata($current_user->ID);
		$output .= "\n 						user_id : $current_user->ID,";
		foreach ($data->allcaps as $cap => $val) {
			$cap = sanitize_title($cap);
			$cap = str_replace('-', '_', $cap);
			$output .= "\n 						cap_{$cap} : $val,";
		}
		return $output;
	}

	function get_current_url() {
		if (isset($_SERVER['REQUEST_URI'])) {
			return 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		} else if (isset($_SERVER['PATH_INFO'])) {
			return $_SERVER['PATH_INFO'];
		} else {
			$host = $_SERVER['HTTP_HOST'];
			$port = $_SERVER['SERVER_PORT'];
			$request = $_SERVER['PHP_SELF'];
			$query = isset($_SERVER['argv']) ? substr($_SERVER['argv'][0], strpos($_SERVER['argv'][0], ';') + 1) : '';
			$toret = $protocol . '://' . $host . ($port == $protocol_port ? '' : ':' . $port) . $request . (empty($query) ? '' : '?' . $query);
			return $toret;
		}
	}

	function get_disabled_wts(){
		$wts = str_replace('"', '', get_option('sk_disabled_wts'));
		if ($wts) {
			return $wts;
		}
		return 'false';
	}

	function get_plugins(){
		$active_plugins = wp_get_active_and_valid_plugins();
		$mu_plugins = get_mu_plugins();

		$printed = false;

		$output .= '[';

		if (is_array($active_plugins)) {
			foreach ($active_plugins as $plugins_key => $plugin) {
				$data = get_plugin_data( $plugin, false, false );

				$plugins[addslashes($data['Name'])] = $data['Version'];
				if ($plugins_key > 0) $output .= ',';
				$data['Name'] = addslashes($data['Name']);
				$output .= "{'{$data['Name']}' : '{$data['Version']}'}";
				$printed = true;
			}
		}

		if (is_array($mu_plugins)) {
			foreach ($mu_plugins as $plugins_key => $plugin) {
				$plugins[addslashes($data['Name'])] = $plugin['Version'];
				if ($printed) $output .= ',';
				$plugin['Name'] = addslashes($plugin['Name']);
				$output .= "{'{$plugin['Name']}' : '{$plugin['Version']}'}";
				$printed = true;
			}
		}
		$output .= ']';
		return $output;
	}

	function get_user_role(){
		global $current_user, $wp_roles;

		if (is_super_admin($current_user->ID)) {
			return 'administrator';
		}

		if(!isset($current_user->caps) || count($current_user->caps) < 1){
			// In MS in some specific pages current user is returning empty caps so this is a work around for that case.
			if (current_user_can('activate_plugins')){
				return 'administrator';
			}
		}
		foreach($wp_roles->role_names as $role => $Role) {
			if (array_key_exists($role, $current_user->caps)){
				$user_role = $role;
				break;
			}
		}
		return $user_role;
	}
}