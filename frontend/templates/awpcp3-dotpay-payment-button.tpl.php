<form method="post" action=<?php echo esc_attr($dotpay_url) ?>>
    <h3><?php _ex('Transaction Details', 'payment completed page', 'AWPCP') ?></h3>

    <p>You chose payment by Dotpay. Click Continue do proceed</p>
	
    <p class="form-submit">
        <input type="hidden" value="<?php echo esc_attr( $customer_id ); ?>" name="id">
        <input type="hidden" value="<?php echo esc_attr( $amount ); ?>" name="amount">
        <input type="hidden" value="<?php echo esc_attr( $amount ); ?>" name="amount">
        <input type="hidden" value="<?php echo esc_attr( $payment_currency ); ?>" name="currency">
        <input type="hidden" value="<?php echo esc_attr( $return_url ); ?>" name="URL">
        <input type="hidden" value="<?php echo esc_attr( $notify_url ); ?>" name="URLC">
        <input type="hidden" value="<?php echo esc_attr( $payment_type ); ?>" name="type">
        <input type="hidden" value="Płatność za ogłoszenie nr:<?php echo esc_attr( $transaction->id ); ?> na stronie <?php bloginfo('title') ?>" name="description">
        <input class="button" type="submit" value="<?php _e('Continue', 'AWPCP') ?>" id="submit" name="submit">
    </p>
</form>
