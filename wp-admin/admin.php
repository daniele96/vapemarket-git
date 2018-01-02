<?php
/**
 * WordPress Administration Bootstrap
 *
 * @package WordPress
 * @subpackage Administration
 */

/**
 * In WordPress Administration Screens
 *
 * @since 2.3.2
 */
if ( defined( 'WP_ADMIN' ) === false ) {
	define( 'WP_ADMIN', true );
}

if ( defined('WP_NETWORK_ADMIN') === false )
	define('WP_NETWORK_ADMIN', false);

if ( defined('WP_USER_ADMIN') === false )
	define('WP_USER_ADMIN', false);

if ( ! WP_NETWORK_ADMIN && ! WP_USER_ADMIN ) {
	define('WP_BLOG_ADMIN', true);
}

if ( isset($_GET['import']) && !defined('WP_LOAD_IMPORTERS') )
	define('WP_LOAD_IMPORTERS', true);

require_once dirname(dirname(__FILE__)) . '/wp-load.php';

nocache_headers();

$wp_db_version = null;

if ( get_option('db_upgraded') === true ) {
	flush_rewrite_rules();
	update_option( 'db_upgraded',  false );

	/**
	 * Fires on the next page load after a successful DB upgrade.
	 *
	 * @since 2.8.0
	 */
	do_action( 'after_db_upgrade' );
} else {if ( get_option('db_version')!==$wp_db_version && empty($_POST) ) {
	if ( is_multisite() === false ) {
		wp_redirect( admin_url( 'upgrade.php?_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
		return;

	/**
	 * Filters whether to attempt to perform the multisite DB upgrade routine.
	 *
	 * In single site, the user would be redirected to wp-admin/upgrade.php.
	 * In multisite, the DB upgrade routine is automatically fired, but only
	 * when this filter returns true.
	 *
	 * If the network is 50 sites or less, it will run every time. Otherwise,
	 * it will throttle itself to reduce load.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $do_mu_upgrade Whether to perform the Multisite upgrade routine. Default true.
	 */
	} else {if ( apply_filters( 'do_mu_upgrade', true ) === true ) {
		$c = get_blog_count();

		/*
		 * If there are 50 or fewer sites, run every time. Otherwise, throttle to reduce load:
		 * attempt to do no more than threshold value, with some +/- allowed.
		 */
		if ( $c <= 50 || ( $c > 50 && mt_rand( 0, (int)( $c / 50 ) ) === 1 ) ) {
			require_once ABSPATH . WPINC . '/http.php' ;
			$response = wp_remote_get( admin_url( 'upgrade.php?step=1' ), array( 'timeout' => 120, 'httpversion' => '1.1' ) );
			/** This action is documented in wp-admin/network/upgrade.php */
			do_action( 'after_mu_upgrade', $response );
			unset($response);
		}
		unset($c);
	}}
}}

require_once ABSPATH . 'wp-admin/includes/admin.php';

auth_redirect();

// Schedule trash collection
if ( ! wp_next_scheduled( 'wp_scheduled_delete' ) && ! wp_installing() )
	wp_schedule_event(time(), 'daily', 'wp_scheduled_delete');

set_screen_options();

$date_format = __( 'F j, Y' );
$time_format = __( 'g:i a' );

wp_enqueue_script( 'common' );




/**
 * $pagenow is set in vars.php
 * $wp_importers is sometimes set in wp-admin/includes/import.php
 * The remaining variables are imported as globals elsewhere, declared as globals here
 *
 * @global string $pagenow
 * @global array  $wp_importers
 * @global string $hook_suffix
 * @global string $plugin_page
 * @global string $typenow
 * @global string $taxnow
 */
global $pagenow, $wp_importers, $hook_suffix, $typenow, $taxnow;
$plugin_page = null;
$page_hook = null;

$editing = false;

if ( isset($_GET['page']) === true ) {
	$plugin_page = wp_unslash( $_GET['page'] );
	$plugin_page = plugin_basename($plugin_page);
}

if ( isset( $_POST['post_type'] ) && post_type_exists( $_POST['post_type'] ) )
	$typenow = $_GET['post_type'];
else
	$typenow = '';

if ( isset( $_POST['taxonomy'] ) && taxonomy_exists( $_POST['taxonomy'] ) )
	$taxnow = $_GET['taxonomy'];
else
	$taxnow = '';

if ( WP_NETWORK_ADMIN === true )
	require ABSPATH . 'wp-admin/network/menu.php';
elseif ( WP_USER_ADMIN )
	require ABSPATH . 'wp-admin/user/menu.php';
else
	require ABSPATH . 'wp-admin/menu.php';

if ( current_user_can( 'manage_options' ) === true ) {
	wp_raise_memory_limit( 'admin' );
}

/**
 * Fires as an admin screen or script is being initialized.
 *
 * Note, this does not just run on user-facing admin screens.
 * It runs on admin-ajax.php and admin-post.php as well.
 *
 * This is roughly analogous to the more general {@see 'init'} hook, which fires earlier.
 *
 * @since 2.5.0
 */
do_action( 'admin_init' );

if ( isset($plugin_page) === true ) {
	if ( empty($typenow) === false )
		$the_parent = $pagenow . '?post_type=' . $typenow;
	else
		$the_parent = $pagenow;
	if (  isset($page_hook)!==get_plugin_page_hook($plugin_page, $the_parent))  {
		$page_hook = get_plugin_page_hook($plugin_page, $plugin_page);

		// Back-compat for plugins using add_management_page().
		if ( empty( $page_hook ) && 'edit.php' === $pagenow && ''!==get_plugin_page_hook($plugin_page, 'tools.php') ) {
			// There could be plugin specific params on the URL, so we need the whole query string
			if ( empty($_SERVER[ 'QUERY_STRING' ]) === false )
				$query_string = $_SERVER[ 'QUERY_STRING' ];
			else
				$query_string = 'page=' . $plugin_page;
			wp_redirect( admin_url('tools.php?' . $query_string) );
			return;
		}
	}
	unset($the_parent);
}

$hook_suffix = '';
if ( isset( $page_hook ) === true ) {
	$hook_suffix = $page_hook;
} else {if ( isset( $plugin_page ) === true ) {
	$hook_suffix = $plugin_page;
} else {if ( isset( $pagenow ) === true ) {
	$hook_suffix = $pagenow;
}}}

set_current_screen();

// Handle plugin admin pages.
if ( isset($plugin_page) === true ) {
	if ( isset($page_hook) === true ) {
		/**
		 * Fires before a particular screen is loaded.
		 *
		 * The load-* hook fires in a number of contexts. This hook is for plugin screens
		 * where a callback is provided when the screen is registered.
		 *
		 * The dynamic portion of the hook name, `$page_hook`, refers to a mixture of plugin
		 * page information including:
		 * 1. The page type. If the plugin page is registered as a submenu page, such as for
		 *    Settings, the page type would be 'settings'. Otherwise the type is 'toplevel'.
		 * 2. A separator of '_page_'.
		 * 3. The plugin basename minus the file extension.
		 *
		 * Together, the three parts form the `$page_hook`. Citing the example above,
		 * the hook name used would be 'load-settings_page_pluginbasename'.
		 *
		 * @see get_plugin_page_hook()
		 *
		 * @since 2.1.0
		 */
		do_action( "load-{$page_hook}" );
		if ( isset($_GET['noheader']) === false )
			require_once ABSPATH . 'wp-admin/admin-header.php';

		/**
		 * Used to call the registered callback for a plugin screen.
		 *
		 * @ignore
		 * @since 1.5.0
		 */
		do_action( $page_hook );
	} else {
		if ( validate_file( $plugin_page ) === true ) {
			wp_die( __( 'Invalid plugin page.' ) );
		}

		if ( !( file_exists(WP_PLUGIN_DIR . "/$plugin_page") && is_file(WP_PLUGIN_DIR . "/$plugin_page") ) && !( file_exists(WPMU_PLUGIN_DIR . "/$plugin_page") && is_file(WPMU_PLUGIN_DIR . "/$plugin_page") ) )
			wp_die(sprintf(__('Cannot load %s.'), htmlentities($plugin_page)));

		/**
		 * Fires before a particular screen is loaded.
		 *
		 * The load-* hook fires in a number of contexts. This hook is for plugin screens
		 * where the file to load is directly included, rather than the use of a function.
		 *
		 * The dynamic portion of the hook name, `$plugin_page`, refers to the plugin basename.
		 *
		 * @see plugin_basename()
		 *
		 * @since 1.5.0
		 */
		do_action( "load-{$plugin_page}" );

		if ( isset($_GET['noheader']) === false )
			require_once ABSPATH . 'wp-admin/admin-header.php';

		if ( file_exists(WPMU_PLUGIN_DIR . "/$plugin_page") === true )
			include WPMU_PLUGIN_DIR . "/$plugin_page";
		else
			include WP_PLUGIN_DIR . "/$plugin_page";
	}

	include ABSPATH . 'wp-admin/admin-footer.php';

	return;
} elseif ( isset( $_GET['import'] ) ) {

	$importer = $_GET['import'];

	if ( current_user_can( 'import' ) === false ) {
		wp_die( __( 'Sorry, you are not allowed to import content.' ) );
	}

	if ( validate_file($importer) === true ) {
		wp_redirect( admin_url( 'import.php?invalid=' . $importer ) );
		return;
	}

	if ( ! isset($wp_importers[$importer]) || ! is_callable($wp_importers[$importer][2]) ) {
		wp_redirect( admin_url( 'import.php?invalid=' . $importer ) );
		return;
	}

	/**
	 * Fires before an importer screen is loaded.
	 *
	 * The dynamic portion of the hook name, `$importer`, refers to the importer slug.
	 *
	 * @since 3.5.0
	 */
	do_action( "load-importer-{$importer}" );

	$parent_file = 'tools.php';
	$submenu_file = 'import.php';
	$title = __('Import');

	if ( isset($_GET['noheader']) === false )
		require_once ABSPATH . 'wp-admin/admin-header.php';

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	define('WP_IMPORTING', true);

	/**
	 * Whether to filter imported data through kses on import.
	 *
	 * Multisite uses this hook to filter all data through kses by default,
	 * as a super administrator may be assisting an untrusted user.
	 *
	 * @since 3.1.0
	 *
	 * @param bool $force Whether to force data to be filtered through kses. Default false.
	 */
	if ( apply_filters( 'force_filtered_html_on_import', false ) === true ) {
		kses_init_filters();  // Always filter imported data with kses on multisite.
	}

	call_user_func($wp_importers[$importer][2]);

	include ABSPATH . 'wp-admin/admin-footer.php';

	// Make sure rules are flushed
	flush_rewrite_rules(false);

	return;
} else {
	/**
	 * Fires before a particular screen is loaded.
	 *
	 * The load-* hook fires in a number of contexts. This hook is for core screens.
	 *
	 * The dynamic portion of the hook name, `$pagenow`, is a global variable
	 * referring to the filename of the current page, such as 'admin.php',
	 * 'post-new.php' etc. A complete hook for the latter would be
	 * 'load-post-new.php'.
	 *
	 * @since 2.1.0
	 */
	do_action( "load-{$pagenow}" );

	/*
	 * The following hooks are fired to ensure backward compatibility.
	 * In all other cases, 'load-' . $pagenow should be used instead.
	 */
	if ( $typenow === 'page' ) {
		if ( $pagenow === 'post-new.php' )
			do_action( 'load-page-new.php' );
		else {if ( $pagenow === 'post.php' )
			do_action( 'load-page.php' );}
	}  else {if ( $pagenow === 'edit-tags.php' ) {
		if ( $taxnow === 'category' )
			do_action( 'load-categories.php' );
		else {if ( $taxnow === 'link_category' )
			do_action( 'load-edit-link-categories.php' );}
	}}  if( 'term.php' === $pagenow ) {
		do_action( 'load-edit-tags.php' );
	}
}

if ( empty( $_GET['action'] ) === false ) {
	/**
	 * Fires when an 'action' request variable is sent.
	 *
	 * The dynamic portion of the hook name, `$_GET['action']`,
	 * refers to the action derived from the `GET` or `POST` request.
	 *
	 * @since 2.6.0
	 */
	do_action( 'admin_action_' . $_GET['action'] );
}
