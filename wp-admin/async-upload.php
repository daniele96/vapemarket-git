<?php
/**
 * Server-side file upload handler from wp-plupload, swfupload or other asynchronous upload methods.
 *
 * @package WordPress
 * @subpackage Administration
 */

if ( isset( $_GET['action'] ) && 'upload-attachment' === $_GET['action'] ) {
	define( 'DOING_AJAX', true );
}

if ( ! defined( 'WP_ADMIN' ) ) {
	define( 'WP_ADMIN', true );
}

if ( defined('ABSPATH') )
	require_once ABSPATH . 'wp-load.php';
else
	require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php' ;

if ( ! ( isset( $_GET['action'] ) && 'upload-attachment' == $_GET['action'] ) ) {
	// Flash often fails to send cookies with the POST or upload, so we need to pass it in GET or POST instead
	if ( is_ssl() && empty($_COOKIE[SECURE_AUTH_COOKIE]) && !empty($_POST['auth_cookie']) )
		$_COOKIE[SECURE_AUTH_COOKIE] = $_POST['auth_cookie'];
	elseif ( empty($_COOKIE[AUTH_COOKIE]) && !empty($_POST['auth_cookie']) )
		$_COOKIE[AUTH_COOKIE] = $_POST['auth_cookie'];
	if ( empty($_COOKIE[LOGGED_IN_COOKIE]) && !empty($_POST['logged_in_cookie']) )
		$_COOKIE[LOGGED_IN_COOKIE] = $_POST['logged_in_cookie'];
	unset($current_user);
}

require_once ABSPATH . 'wp-admin/admin.php' ;

header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );

if ( isset( $_GET['action'] ) && 'upload-attachment' === $_GET['action'] ) {
	include ABSPATH . 'wp-admin/includes/ajax-actions.php' ;

	send_nosniff_header();
	nocache_headers();

	wp_ajax_upload_attachment();
	return 0;
}

if ( ! current_user_can( 'upload_files' ) ) {
	wp_die( __( 'Sorry, you are not allowed to upload files.' ) );
}

// just fetch the detail form for that attachment
if ( isset($_GET['attachment_id']) && ($id = intval($_GET['attachment_id'])) && $_POST['fetch'] ) {
	$post = get_post( $id );
	if ( 'attachment' != $post->post_type )
		wp_die( __( 'Invalid post type.' ) );
	if ( ! current_user_can( 'edit_post', $id ) )
		wp_die( __( 'Sorry, you are not allowed to edit this item.' ) );

	switch ( $_POST['fetch'] ) {
		case 3 :
			if ( $thumb_url = wp_get_attachment_image_src( $id, 'thumbnail', true ) )


				$var=esc_url( $thumb_url[0] );
			    $var2= esc_url( get_edit_post_link( $id ) );
			    $var3= _x( 'Edit', 'media item' );

				$str= <<<HTML
	 <img class="pinkynail" src=" $var" alt="" />
	 <a class="edit-attachment" href="$var2" target="_blank"> $var3 </a>
HTML;

		echo $str;
				
			// Title shouldn't ever be empty, but use filename just in case.
			$file = get_attached_file( $post->ID );
			$title = $post->post_title ? $post->post_title : wp_basename( $file );


			$var= esc_html( wp_html_excerpt( $title, 60, '&hellip;' ) );

			$str= <<<HTML
	 <div class="filename new"><span class="title">$var </span></div>
HTML;

		echo $str;
			
			break;
		case 2 :
			add_filter('attachment_fields_to_edit', 'media_single_attachment_fields_to_edit', 10, 2);
			echo get_media_item($id, array( 'send' => false, 'delete' => true ));
			break;
		default:
			add_filter('attachment_fields_to_edit', 'media_post_single_attachment_fields_to_edit', 10, 2);
			echo get_media_item($id);
			break;
	}
	return;
}

check_admin_referer('media-form');

$post_id = 0;
if ( isset( $_GET['post_id'] ) ) {
	$post_id = absint( $_GET['post_id'] );
	if ( ! get_post( $post_id ) || ! current_user_can( 'edit_post', $post_id ) )
		$post_id = 0;
}

$id = media_handle_upload( 'async-upload', $post_id );
if ( is_wp_error($id) ) {

	$var=sprintf(__('&#8220;%s&#8221; has failed to upload.'), $_FILES['async-upload']['name']);
	$var2= $id->get_error_message();

	$str= <<<HTML
	<div class="error-div error">
	<a class="dismiss" href="#" onclick="jQuery(this).parents(\'div.media-item\').slideUp(200, function(){jQuery(this).remove();});">'Dismiss'</a>
	<strong>$var</strong><br />
	$var2</div>
HTML;

		echo htmlspecialchars($str);
	
	return;
}

if ( $_GET['short'] ) {
	// Short form response - attachment ID only.
	echo $id;
} else {
	// Long form response - big chunk o html.
	$type = $_GET['type'];

	/**
	 * Filters the returned ID of an uploaded attachment.
	 *
	 * The dynamic portion of the hook name, `$type`, refers to the attachment type,
	 * such as 'image', 'audio', 'video', 'file', etc.
	 *
	 * @since 2.5.0
	 *
	 * @param int $id Uploaded attachment ID.
	 */
	echo htmlspecialchars(apply_filters( "async_upload_{$type}", $id ));
}
