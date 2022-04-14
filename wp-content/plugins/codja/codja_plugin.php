<?php
/*
Plugin Name: CODJA: WordPress Full Stack Testing Specification
Author: László Mészöly
Description: Use with [codja_users] shortcode
Version: 1.0.0
*/

add_shortcode('codja_users', 'codja_users_shortcode');
function codja_users_shortcode( $atts = [], $content = null ) {
	if ( !current_user_can('manage_options') ) {
		// user is not admin
		return null;
	}
	$table = users_data(array(), 'id', 'ASC', 1);
	$template = '<input type="hidden" id="sort" value="DESC">';
	$template .= '<div id="codja_roles"><ul><li class="active">';
	$roles = count_users(); // using count to avoid empty role groups
	$template .= implode('</li><li class="active">', array_keys(array_filter($roles['avail_roles'])));
	$template .= '</ul></div>';
	$template .= '<div id="codja">' . $table . '</div>';
	return $template;
}

function users_data( $filter, $orderby, $order, $page ) {
	$order_icon = ( $order == 'ASC' ) ? '<i class="fa-solid fa-arrow-down-a-z"></i>' : '<i class="fa-solid fa-arrow-up-a-z"></i>';
	$table = '<table id="users"><thead><tr>';
	//$table .= '<th>id</th>';
	$table .= wp_sprintf('<th><span id="display_name" %1$s Display name</span></th><th><span id="user_email" %2$s User email</span></th>',
						 ( $orderby == 'display_name' ) ? 'class="active">' . $order_icon : '><i class="fa-solid fa-sort"></i>',
						 ( $orderby == 'user_email' ) ? 'class="active">' . $order_icon : '><i class="fa-solid fa-sort"></i>'
	);
	$table .= '<th>WP role</th></tr></thead><tbody>';

	$users = new WP_User_Query(array(
								   'orderby' => $orderby,
								   'order' => $order,
								   'role__in' => $filter,
								   'number' => 10,
								   'offset' => ( $page - 1 ) * 10
							   ));
	$total_users = $users->total_users;
	foreach ( $users->get_results() as $user ) {
		//$table .= wp_sprintf('<tr><td>%1$s</td><td>%2$s</td><td>%3$s</td><td>%4$s</td></tr>',
		$table .= wp_sprintf('<tr><td>%1$s</td><td>%2$s</td><td>%3$s</td></tr>',
			//$user->id,
							 $user->display_name,
							 $user->user_email,
							 implode(',', $user->roles)
		);
	}
	$result = $table . '</tbody></table>';

	$total_pages = ceil($total_users / 10);
	$result .= '<div><ul class="page-numbers">';
	for ( $i = 1; $i <= $total_pages; $i++ ) {
		if ( intval($page) == $i ) {
			$result .= '<li p="' . $i . '" class="active">' . $i . '</li>';
		} else {
			$result .= '<li p="' . $i . '">' . $i . '</li>';
		}
	};
	$result .= '</ul></div>';

	return $result;
}

add_action('wp_enqueue_scripts', 'codja_enqueue');
function codja_enqueue() {
	if ( is_user_logged_in() ) {
		wp_register_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.css');
		wp_register_style('coda-core-css', plugin_dir_url(__FILE__) . 'assets/css/codja_core.css');
		wp_enqueue_style('fontawesome');
		wp_enqueue_style('coda-core-css');
		wp_enqueue_script('codja-core-js',
						  plugin_dir_url(__FILE__) . 'assets/js/codja_core.js',
						  array('jquery'),
						  '1.0.0',
						  true);
		wp_localize_script('codja-core-js', 'ajax', ['ajaxurl' => admin_url('admin-ajax.php')]);
	};
}

add_action('wp_ajax_codja_action', 'codja_action');
function codja_action() {
	$filter = $_POST['filter'];
	if ( empty( $filter ) ) {
		// returns the table headers, even if there is no match for sure
		$filter = array(
			'placeholder'
		);
	}
	echo users_data($filter, $_POST['columnName'], $_POST['sort'], $_POST['page']);
	wp_die();
}
