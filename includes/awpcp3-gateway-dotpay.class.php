<?php
/**
* payment gateway integration for Another WordPress Classifieds Plugin since v3.0
* @ref http://www.awpcp.com/
*/
class AWPCP3_Gateway_Dotpay extends AWPCP_PaymentGateway {

    // special test customer ID for sandbox
    const DOTPAY_PAYMENTS_TEST_CUSTOMER = '75293';

    // Dotpay IP address
    const DOTPAY_IP = '195.150.9.37';

    // Dotpay URL
    const DOTPAY_URL = 'https://ssl.dotpay.pl';

    // Gateway name
    const PAYMENT_METHOD = 'dotpay';

    protected $integration;
    protected $paymentsAPI = false;

    /**
    * initialise gateway with custom settings
    */
    public function __construct() {
        // admin actions / filters
        add_action('awpcp_register_settings', array($this, 'awpcpRegisterSettings'));

        // front end actions / filters
        add_filter('awpcp-register-payment-methods', array($this, 'awpcpRegisterPaymentMethods'));          // AWPCP v3.0+

        //Init Payment in AWPCP_PaymentGateway class
        parent::__construct(self::PAYMENT_METHOD, 'Dotpay Payment Gateway', 'Credit card payment via Dotpay', plugins_url('resources/images/dotpay.gif', __FILE__));
    }

    /**
    * declare type of integration as showing a payment button
    * @return string
    */
    public function get_integration_type() {
        return self::INTEGRATION_BUTTON;
    }

    /**
    * process payment of a transaction -- show the payment button
    * @param AWPCP_Payment_Transaction $transaction
    * @return string
    */
    
    public function process_payment($transaction) {
        return $this->render_payment_button($transaction);
    }

    private function render_payment_button($transaction) {
        // no current support for multiple items
        $item = $transaction->get_item(0);
        
        $custom = $transaction->id;
        
        $customer_id = get_awpcp_option('dotpay_customerid');

        $totals = $transaction->get_totals();
        $amount = $totals['money'];

        $payments = awpcp_payments_api();
        $return_url = $payments->get_return_url($transaction);
        $notify_url = $payments->get_notify_url($transaction);
        $cancel_url = $payments->get_cancel_url($transaction);
        $payment_currency = get_awpcp_option('dotpaycurrencycode');
        $payment_type = 0;

        $dotpay_url = self::DOTPAY_URL;

        ob_start();
            include(dirname(__FILE__) . '/frontend/templates/awpcp3-dotpay-payment-button.tpl.php');
            $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    private function verify_transaction($transaction) {
        $errors = array();

        if ($_POST['t_status']) {
            if($_SERVER['REMOTE_ADDR'] == self::DOTPAY_IP) {
                if($_POST['t_status'] == 2) {
                    $verified = true;
                }
            }
        } else {
            $verified = $transaction->get('verified', false);
        }

        if (!$verified) {
            switch ($_POST['t_status']) {
                case 1:
                    $response = 'PENDING';
                    break;
                case 2:
                    $response = 'COMPLETED';
                    break;
                case 3:
                    $response = 'FAILED';
                    break;
                case 4:
                    $response = 'CANCELED';
                    break;
                case 5:
                    $response = 'COMPLAINT';
                    break;
                default:
                    $response = 'UNDEFINED';
                    break;
            }
            $variables = count($_POST);
            $url = awpcp_current_url();



            if ($variables <= 1) {
                $message = __("We haven't received your payment information from Dotpay yet and we are unable to verify your transaction. Please reload this page or visit <a href=\"%s\">%s</a> in 30 seconds to continue placing your Ad.", 'AWPCP');
                $errors[] = sprintf($message, $url, $url);
            } else {
                $message = __("Dotpay returned the following status from your payment: %s. %d payment variables were posted.",'AWPCP');
                $errors[] = sprintf($message, $response, count($_POST));
                $errors[] = __("If this status is not PENDING or COMPLETED, then you may need to wait a bit before your payment is approved, or contact PayPal directly as to the reason the payment is having a problem.",'AWPCP');
            }

            $errors[] = __("If you have any further questions, please contact this site administrator.",'AWPCP');

            if ($variables <= 0)
                $transaction->errors['verification-get'] = $errors;
            else
                $transaction->errors['verification-post'] = $errors;
        } else {
            // clean up previous errors
            unset($transaction->errors['verification-get']);
            unset($transaction->errors['verification-post']);
        }

        $transaction->set('verified', $verified);

        return $verified;
    }

    private function validate_transaction($transaction) {
        $errors = $transaction->errors;

        if (!$_POST['t_status']) {
            return $transaction->get('validated', false);
        }

        $payment_gross = number_format((double) awpcp_post_param('amount'), 2);
        $payer_email = awpcp_post_param('email');

        $totals = $transaction->get_totals();
        $amount = number_format($totals['money'], 2);

        if($amount != $payment_gross) {
            $message = __("The amount you have paid does not match the required amount for this transaction. Please contact us to clarify the problem.", "AWPCP");
            $transaction->errors['validation'] = $message;
            $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_INVALID;
            awpcp_payment_failed_email($transaction, $message);
            return false;
        }

        switch (awpcp_post_param('t_status')) {
            case 1:
                $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_PENDING;
                break;
            case 2:
                $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED;
                break;
            case 3:
                $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED;
                break;
            case 4:
                $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_CANCELED;
                break;
            case 5:
                $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED;
                break;
            default:
                $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_UNKNOWN;
                return false;
                break;
        }

        unset($transaction->errors['validation']);

        $transaction->set( 'validated', true );

        $transaction->payment_gateway = $this->slug;
        $transaction->payer_email = $payer_email;
        
        return true;
    }

    /**
    * process payment notification
    * @param AWPCP_Payment_Transaction $transaction
    */
    public function process_payment_notification($transaction) {
        if($this->process_payment_completed($transaction)) {
            echo 'OK';
        }
    }

    /**
    * process completed transaction
    * @param AWPCP_Payment_Transaction $transaction
    */
    public function process_payment_completed($transaction) {
        if (!$this->verify_transaction($transaction)) {
            $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_NOT_VERIFIED;
        } else {
            $this->validate_transaction( $transaction );
        }
    }

    /**
    * process payment cancellation
    * @param AWPCP_Payment_Transaction $transaction
    */
    public function process_payment_canceled($transaction) {
        // There's no something like cancel url in dotpay.pl
        $transaction->errors[] = __("The payment transaction was canceled by the user.", "AWPCP");
        $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_CANCELED;
    }

    /**
    * AWPCP v3.0+: register new payment gateway with front end (NB: admin side never calls this!)
    * @param AWPCP_PaymentsAPI $payments AWPCP payments API
    */
    public function awpcpRegisterPaymentMethods($payments) {
        $this->paymentsAPI = $payments;

        if (get_awpcp_option('activatedotpay')) {
            $this->paymentsAPI->register_payment_method($this);
        }
    }

    /**
    * register settings for this payment method
    */
    public function awpcpRegisterSettings() {
        global $awpcp;

        // create a new section
        $section = $awpcp->settings->add_section('payment-settings', 'Dotpay Payment Gateway', 'dotpay', 100, array($awpcp->settings, 'section'));

        $awpcp->settings->add_setting($section, 'activatedotpay', 'Activate Dotpay?',
            'checkbox', 1, 'Activate Dotpay?');

        $awpcp->settings->add_setting($section, 'dotpay_customerid', 'Dotpay customer ID', 'textfield', self::DOTPAY_PAYMENTS_TEST_CUSTOMER,
            '<br>your Dotpay customer ID');

        $awpcp->settings->add_setting($section, 'dotpaycurrencycode', 'Dotpay currency code', 'textfield', 'PLN', '<br>The currency in which you would like to receive your Dotpay payments');
    }

}