<?php
// Remove switch button in subscription totals table
remove_action( 'woocommerce_order_item_meta_end', 'WC_Subscriptions_Switcher::print_switch_link', 10 );

add_filter( 'wcs_view_subscription_actions', function( $actions, $subscription ) {
	$first_subscription_item_id = array_keys( $subscription->get_items() )[0];
	$first_subscription_item_value = array_values( $subscription->get_items() )[0];
	$switch_url = esc_url( WC_Subscriptions_Switcher::get_switch_url( $first_subscription_item_id, $first_subscription_item_value, $subscription ) );

	// Remove 'Change Address' action from $actions\
	unset( $actions[ 'change_address' ] );

	// Create reordered actions to be prepended to $actions array
	$reordered_actions = array(
		'upgrade_or_downgrade' => array( 'url'  => $switch_url, 'name' => 'Upgrade or Downgrade' ),
		'subscription_renewal_early' => $actions[ 'subscription_renewal_early' ],
		'cancel' => $actions[ 'cancel' ],
	);

	// Prepend new reordered actions to $actions array
	$actions = array_merge( $reordered_actions, $actions );

	return $actions;
}, 10, 2 );
