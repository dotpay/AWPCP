<form method="post" action=<?php echo esc_attr($dotpay_url) ?>>
    <h3><?php _ex('Transaction Details', 'payment completed page', 'AWPCP') ?></h3>

    <p><?php __('You chose payment by Dotpay. Click Continue do proceed', 'awpcp3-gateway-dotpay') ?></p>
    
    <p class="form-submit">
        <input type="hidden" value="<?php echo esc_attr( $customer_id ); ?>" name="id">
        <input type="hidden" value="<?php echo esc_attr( $amount ); ?>" name="amount">
        <input type="hidden" value="<?php echo esc_attr( $amount ); ?>" name="amount">
        <input type="hidden" value="<?php echo esc_attr( $payment_currency ); ?>" name="currency">
        <input type="hidden" value="<?php echo esc_attr( $return_url ); ?>" name="URL">
        <input type="hidden" value="<?php echo esc_attr( $notify_url ); ?>" name="URLC">
        <input type="hidden" value="<?php echo esc_attr( $payment_type ); ?>" name="type">
        <input type="hidden" value="legacy" name="api_version">
        <input type="hidden" value="<?php echo sprintf(__('Payment for advert no. %1$s (%2$s)', 'awpcp3-gateway-dotpay'), esc_attr( $custom ), get_bloginfo('name')) ?>" name="description">
        <input class="button" type="submit" value="<?php _e('Continue', 'AWPCP') ?>" id="submit" name="submit">
    </p>
</form>
