<?php
/*
Plugin Name: AWPCP3 Gateway Dotpay
Plugin URI: http://michak.pl/awpcp3-gateway-dotpay
Description: Add a credit card payment gateway for Dotpay (Poland) to AWPCP3+
Version: 1.0
Author: Michak
Author URI: http://michak.pl
Text Domain: awpcp3-gateway-dotpay
*/

if ( ! defined( 'WPINC' ) ) die; // Exit if accessed directly

define('PLUGINROOT', plugin_dir_path( __FILE__ ));
define('PLUGINURL', plugin_dir_url( __FILE__ ));

require_once dirname(__FILE__) . '/includes/required_plugins.php';

function init_awpcp3_gateway_dotpay() {
    require_once dirname(__FILE__) . '/includes/awpcp3-gateway-dotpay.class.php';

    AWPCP3_Gateway_Dotpay::get_instance();
}

if ( in_array( 'another-wordpress-classifieds-plugin/awpcp.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    load_plugin_textdomain( 'awpcp3-gateway-dotpay', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
    
    add_action( 'init', 'init_awpcp3_gateway_dotpay' );
}

?>