<?php
/**
 * WP-Members Export Functions
 *
 * Mananges exporting users to a CSV file.
 *
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2017
 */

/**
 * New export function to export all or selected users
 *
 * @since 2.9.7
 * @since 3.2.0 Updated to use fputcsv
 *
 * @param array $args
 * @param array $users
 */
function wpmem_export_users( $args, $users = null ) {

	global $wpmem;

	$today = date( "m-d-y" );

	// Setup defaults.
	$defaults = array(
		'export'         => 'all',
		'filename'       => 'wp-members-user-export-' . $today . '.csv',
		'export_fields'  => wpmem_fields(), //array(),
		'exclude_fields' => array( 'password', 'confirm_password', 'confirm_email' ),
	);

	// Merge $args with defaults.
	/**
	 * Filter the default export arguments.
	 *
	 * @since 2.9.7
	 *
	 * @param array $args An array of arguments to merge with defaults. Default null.
	 */
	$args = wp_parse_args( apply_filters( 'wpmem_export_args', $args ), $defaults );

	// Output needs to be buffered, start the buffer.
	ob_start();

	// If exporting all, get all of the users.
	$users = ( 'all' == $args['export'] ) ? get_users( array( 'fields' => 'ID' ) ) : $users;

	// Generate headers and a filename based on date of export.
	header( "Content-Description: File Transfer" );
	header( "Content-type: application/octet-stream" );
	header( "Content-Disposition: attachment; filename=" . $args['filename'] );
	header( "Content-Type: text/csv; charset=" . get_option( 'blog_charset' ), true );

	$handle = fopen( 'php://output', 'w' );
	fputs( $handle, "\xEF\xBB\xBF" ); // UTF-8 BOM

	$header = [ 'User ID', 'Username' ];
	// Remove excluded fields from $export_fields while setting up $header array.
	foreach ( $args['export_fields'] as $meta => $field ) {
		if ( in_array( $meta, $args['exclude_fields'] ) ) {
			unset( $args['export_fields'][ $meta ] );
		} else {
			$header[] = $field['label'];
		}
	}

	if ( $wpmem->mod_reg == 1 ) {
		$header[] = __( 'Activated?', 'wp-members');
	}

	if ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) {
		$header[] = __( 'Subscription', 'wp-members' );
		$header[] = __( 'Expires', 'wp-members' );
	}

	$header[] = __( 'Registered', 'wp-members' );
	$header[] = __( 'IP', 'wp-members' );

	fputcsv( $handle, $header );

	// Loop through the array of users, assemble csv.
	// $args['export_fields'] only includes fields to be exported at this point.
	foreach ( $users as $user ) {

		$user_info = get_userdata( $user );

		$wp_user_fields = [ 'user_email', 'user_nicename', 'user_url', 'display_name' ];
		$row = array();
		foreach ( $args['export_fields'] as $meta => $field ) {
			$row[] = in_array( $meta, $wp_user_fields ) ? $user_info->{$meta} : get_user_meta( $user, $meta, true );
		}

		$row = array_merge(
			[
				$user_info->ID,
				$user_info->user_login,
			],
			$row
		);

		if ( $wpmem->mod_reg == 1 ) {
			$row[] = get_user_meta( $user, 'active', 1 ) ? __( 'Yes' ) : __( 'No' );
		}

		if ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) {
			$row[] = get_user_meta( $user, 'exp_type', true );
			$row[] = get_user_meta( $user, 'expires', true );
		}

		$row[] = $user_info->user_registered;
		$row[] = get_user_meta( $user, 'wpmem_reg_ip', true );

		fputcsv( $handle, $row );

		// Update the user record as being exported.
		if ( 'all' != $args['export'] ){
			update_user_meta( $user, 'exported', 1 );
		}
	}

	fclose( $handle );
	print( ob_get_clean() );

	exit();
}

// End of file.
