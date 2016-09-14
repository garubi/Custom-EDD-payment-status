<?php
/*
Plugin Name: Custom-EDD-payment-status
Plugin URI: https://github.com/garubi/Custom-EDD-payment-status
Description: 
Version: 0.1.0
Author: Stefano Garuti
Author URI: https://github.com/garubi
Author Email: stefano@wpmania.it
License:
  Copyright 2011 TODO (email@domain.com)
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>

/** 
 * Adds new payment statuses to array of available statuses
 */
function sww_add_edd_payment_statuses( $payment_statuses ) {
	
	$payment_statuses['invoice_created']	= 'Fatturato';
	//$payment_statuses['invoice_sent']	= 'Fattura inviata';

	return $payment_statuses;	
}
add_filter( 'edd_payment_statuses', 'sww_add_edd_payment_statuses' );


/**
 * Adds our new statuses as post statuses so we can use them in Payment History navigation
 */
function sww_register_post_type_statuses() {

	// Payment Statuses
	register_post_status( 'invoice_created', array(
		'label'                     => _x( 'Fatturato', 'Invoice created payment status', 'sww-edd' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Fatturato <span class="count">(%s)</span>', 'Fatturato <span class="count">(%s)</span>', 'sww-edd' )
	) );
	/*
	register_post_status( 'in_progress', array(
		'label'                     => _x( 'Project in progress', 'In progress payment status', 'sww-edd' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'In progress <span class="count">(%s)</span>', 'In progress <span class="count">(%s)</span>', 'sww-edd' )
	)  );
	*/
}
add_action( 'init', 'sww_register_post_type_statuses' );


/**
 * Adds our new payment statuses to the Payment History navigation
 */
function sww_edd_payments_new_views( $views ) {
	
	$views['invoice_paid']	= sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'status' => 'invoice_created', 'paged' => FALSE ) ), 'Fatturato' );
//	$views['in_progress']	= sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'status' => 'in_progress', 'paged' => FALSE ) ), 'In progress' );
	
	return $views;

}
add_filter( 'edd_payments_table_views', 'sww_edd_payments_new_views' );


/**
 * Adds bulk actions to the bulk action dropdown on "Payment History" screen
 */
function sww_edd_bulk_status_dropdown( $actions ) {

	$new_bulk_status_actions = array();
	
	// Loop through existing bulk actions
	foreach ( $actions as $key => $action ) {
	
		$new_bulk_status_actions[ $key ] = $action;
 		
 		// Add our actions after the "Set To Cancelled" action
        if ( 'set-status-cancelled' === $key ) {
            $new_bulk_status_actions['set-status-invoice-created']			= 'Segna come Fatturato';
		//	$new_bulk_status_actions['set-status-in-progress']	= 'Set To In Progress';
            // Add a $new_bulk_status_actions[key] = value; for each status you've added (in the order you want)
        }
    }
	
	return $new_bulk_status_actions;
}
add_filter( 'edd_payments_table_bulk_actions', 'sww_edd_bulk_status_dropdown' );


/**
 * Adds bulk actions to update orders when performed
 */
function sww_edd_bulk_status_action( $id, $action ) {

	if ( 'set-status-invoice-created' === $action ) {
		edd_update_payment_status( $id, 'invoice_created' );
	}
	/*
	if ( 'set-status-in-progress' === $action ) {
		edd_update_payment_status( $id, 'in_progress' );
	}
	*/
}
add_action( 'edd_payments_table_do_bulk_action', 'sww_edd_bulk_status_action', 10, 2 );


/**
 * Adds our custom statuses to earnings and sales reports
 */
function sww_edd_earnings_reporting_args( $args ) {

	//$args['post_status'] = array_merge( $args['post_status'], array( 'invoice_paid', 'in_progress' ) );
	$args['post_status'] = array_merge( $args['post_status'], array( 'invoice_created' ) );

	return $args;
}
add_filter( 'edd_get_earnings_by_date_args', 'sww_edd_earnings_reporting_args' );


function sww_edd_sales_reporting_args( $args ) {

	//$args['post_status'] = array_merge( $args['post_status'], array( 'invoice_paid', 'in_progress' ) );
	$args['post_status'] = array_merge( $args['post_status'], array( 'invoice_created' ) );

	return $args;
}
add_filter( 'edd_get_sales_by_date_args', 'sww_edd_sales_reporting_args' );
