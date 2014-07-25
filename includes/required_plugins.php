<?php
	require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';

	function awpcp3_gateway_dotpay_recommended_plugins() {
		$plugins = array(
			array(
				'name'		=> 'Another WordPress Classifieds Plugin',
				'slug'		=> 'another-wordpress-classifieds-plugin',
				'required'	=> true,
				'version'	=> '3.0.0',
			),
		);

		tgmpa( $plugins );
	}

	add_action( 'tgmpa_register', 'awpcp3_gateway_dotpay_recommended_plugins' );