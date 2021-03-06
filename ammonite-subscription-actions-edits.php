<?php
/**
 * Plugin Name:       Ammonite Subscriptions Actions Edits
 * Description:       This plugin makes edits to the actions within the 'Subscriptions' tab of the 'My Account' section. It removes the 'Upgrade or Downgrade' button from the subscription totals section, moving it to the table above. It also reorders the subscription actions, removes the 'Change Address' action, and changes the cancel action text to 'Cancel Renewals'.
 * Version:           1.0.0
 * Author:            Daniel Ellis
 * Author URI:        https://danielellisdevelopment.com/
 */

/**
 * DOCUMENTATION:
 * -This plugin makes edits to the actions within the 'Subscriptions' tab of the 'My Account' section.
 * -It removes the 'Upgrade or Downgrade' button from the subscription totals section, moving it to the table above.
 * -It reorders the subscription actions and removes the 'Change Address' action.
 * -It changes the cancel action text to 'Cancel Renewals'.
 */

/*
  Basic Security
*/
if ( ! defined( 'ABSPATH' ) ) {
  die;
}

/*
  Main Plugin Functionality
*/
// Remove switch button in subscription totals table
add_action( 'wp', function() {
  remove_action( 'woocommerce_order_item_meta_end', 'WC_Subscriptions_Switcher::print_switch_link', 10 );
} );

// Add upgrade or downgrade to actions and change their order
add_filter( 'wcs_view_subscription_actions', function( $actions, $subscription ) {
  // Make changes only if $actions and $subscription exist
  if ( $actions && $subscription ) {
    $first_subscription_item_id = array_keys( $subscription->get_items() )[0];
    $first_subscription_item_value = array_values( $subscription->get_items() )[0];
    $switch_url = esc_url( WC_Subscriptions_Switcher::get_switch_url( $first_subscription_item_id, $first_subscription_item_value, $subscription ) );
    $switch_text = get_option( WC_Subscriptions_Admin::$option_prefix . '_switch_button_text', __( 'Upgrade or Downgrade', 'woocommerce-subscriptions' ) );

    // Remove 'Change Address' action from $actions
    unset( $actions[ 'change_address' ] );

    // Change text for 'Cancel' action if it exists
    if ( array_key_exists( 'cancel', $actions ) ) {
      $updated_cancel_action = $actions[ 'cancel' ];
      $updated_cancel_action[ 'name' ] = 'Cancel Renewals';
      $actions[ 'cancel' ] = $updated_cancel_action;
    }

    // Create reordered actions to be prepended to $actions array
    $reordered_actions = array();

    // If 'upgrade_or_downgrade' should be printed, push it to reordered_actions array (this becomes the first item) (This logic comes from the native WC Subscriptions function WC_Subscriptions_Switcher::print_switch_link)
    if ( ! wcs_is_order( $subscription ) && 'shop_subscription' === $subscription->get_type() && WC_Subscriptions_Switcher::can_item_be_switched_by_user( $first_subscription_item_value, $subscription )) {
       $reordered_actions += [ 'upgrade_or_downgrade' => array( 'url'  => $switch_url, 'name' => $switch_text ) ];
    }

    // If 'subscription_renewal_early' exists in actions array, push it to reordered_actions array (this becomes the second item)
    if ( array_key_exists( 'subscription_renewal_early', $actions ) ) {
      $reordered_actions += [ 'subscription_renewal_early' => $actions[ 'subscription_renewal_early' ] ];
    }

    // If 'cancel' exists in actions array, push it to reordered_actions array (this becomes the third item)
    if ( array_key_exists( 'cancel', $actions ) ) {
      $reordered_actions += [ 'cancel' => $actions[ 'cancel' ] ];
    }

    // Prepend new reordered actions to $actions array
    $actions = array_merge( $reordered_actions, $actions );
  }

	return $actions;
}, 100, 2 );
