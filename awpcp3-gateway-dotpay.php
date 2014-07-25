<?php
/*
Plugin Name: AWPCP3 Gateway Dotpay
Plugin URI: http://michak.pl/awpcp3-gateway-dotpay
Description: Add a credit card payment gateway for Dotpay (Poland) to AWPCP3+
Version: 1.0
Author: Michak
Author URI: http://michak.pl
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once dirname(__FILE__) . '/includes/required_plugins.php';

function init_awpcp3_gateway_dotpay() {
    require_once dirname(__FILE__) . '/includes/awpcp3-gateway-dotpay.class.php';

    $awpcp3_gateway_dotpay = new AWPCP3_Gateway_Dotpay();
}

if ( in_array( 'another-wordpress-classifieds-plugin/awpcp.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    load_plugin_textdomain( 'dotpay-payment-gateway', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
    
    add_action( 'init', 'init_awpcp3_gateway_dotpay' );
}

?>