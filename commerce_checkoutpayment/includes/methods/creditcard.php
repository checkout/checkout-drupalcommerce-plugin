<?php

class methods_creditcard extends methods_Abstract
{

  public function submitFormCharge($payment_method, $pane_form, $pane_values, $order, $charge) {

    $config = parent::submitFormCharge($payment_method, $pane_form, $pane_values, $order, $charge);
    $config['postedParam']['paymentToken'] = $pane_values['cko-cc-paymenToken'];

    if(!empty($pane_values['cko-cc-redirectUrl'])){
      drupal_goto($pane_values['cko-cc-redirectUrl'] . '&trackId=' . $order->order_id);
    }
    else {
      return $this->_placeorder($config, $charge, $order, $payment_method);
    }
  }

  public function submit_form($payment_method, $pane_values, $checkout_pane, $order) {

    $data = $this->getExtraInit($order,$payment_method);
    $form['pay_method_container'] = array(
        '#type' => 'container',
        '#attributes' => array(
            'class' => array('widget-container')
        ),
    );

    $form['credit_card']['cko-cc-paymenToken'] = array(
        '#type' => 'hidden',
        '#value' => !empty($data['paymentToken']['token'])?$data['paymentToken']['token']:'',
        '#attributes' => array(
            'id' => array('cko-cc-paymenToken')
        ),
    );

    $form['#attached']['js'] = array(
        drupal_get_path('module', 'commerce_checkoutpayment') . '/includes/methods/js/checkoutapi.js' => array(
            'type' => 'file',
        ),
    );

    $form['#attached']['js'][] = array(
        'data' => array('commerce_checkoutpayment' => $data['script']),
        'type' => 'setting',
    );

    return $form;
  }

  public function getExtraInit($order,$payment_method) {

    $array = array();
    module_load_include('inc', 'commerce_payment', 'includes/commerce_payment.credit_card');

    $paymentToken = $this->generatePaymentToken($order,$payment_method);

    if ($order) {
      $order_wrapper = entity_metadata_wrapper('commerce_order', $order);
      $billing_address = $order_wrapper->commerce_customer_billing->commerce_customer_address->value();
      $order_array = $order_wrapper->commerce_order_total->value();
      $config = array();

      $config['publicKey'] = $payment_method['settings']['public_key'];
      $config['mode'] = $payment_method['settings']['mode'];
      $config['email'] = $order->mail;
      $config['name'] = "{$billing_address['first_name']} {$billing_address['last_name']}";
      $config['amount'] = $order_array['amount'];
      $config['currency'] = $order_array['currency_code'];
      $config['localpayment'] = ($payment_method['settings']['localpayment'] == 'false')? 'card': 'mixed';
      $config['paymentToken'] = $paymentToken['token'];

      $jsConfig = $config;
      $array['script'] = $jsConfig;
      $array['paymentToken'] = $paymentToken;
    }

    return $array;
  }

  public function generatePaymentToken($order,$payment_method) {

    global $user;
    $config = array();
    $shippingAddressConfig = null;

    $order_wrapper = entity_metadata_wrapper('commerce_order', $order);

    $billing_address = $order_wrapper->commerce_customer_billing->commerce_customer_address->value();
    $order_array = $order_wrapper->commerce_order_total->value();
    $product_line_items = $order->commerce_line_items[LANGUAGE_NONE];

    if (isset($order)) {

      $orderId = $order->order_id;
      $amountCents = $order->commerce_order_total['und'][0]['amount'];

      $scretKey = $payment_method['settings']['private_key'];
      $mode = $payment_method['settings']['mode'];
      $timeout = $payment_method['settings']['timeout'];

      $config['authorization'] = $scretKey;
      $config['mode'] = $mode;
      $config['timeout'] = $timeout;

      if ($payment_method['settings']['payment_action'] == 'authorize') {

        $config = array_merge($config, $this->_authorizeConfig());
      }
      else {

        $config = array_merge($config, $this->_captureConfig($payment_method));
      }

      $products = array();
      if (!empty($product_line_items)) {
        foreach ($product_line_items as $key => $item) {

          $line_item[$key] = commerce_line_item_load($item['line_item_id']);

          $products[$key] = array(
              'name' => commerce_line_item_title($line_item[$key]),
              'sku' => $line_item[$key]->line_item_label,
              'price' => $line_item[$key]->commerce_unit_price[LANGUAGE_NONE][0]['amount'],
              'quantity' => (int) $line_item[$key]->quantity,
          );
        }
      }

      $billingAddressConfig = array(
          'addressLine1' => $billing_address['thoroughfare'],
          'addressLine2' => $billing_address['premise'],
          'postcode' => $billing_address['postal_code'],
          'country' => $billing_address['country'],
          'city' => $billing_address['locality'],
      );

      if (module_exists('commerce_shipping') && !empty($order_wrapper->commerce_customer_shipping->commerce_customer_address)) {
        $shipping_address = $order_wrapper->commerce_customer_shipping->commerce_customer_address->value();

        // Add the shipping address parameters to the request.
        $shippingAddressConfig = array(
            'addressLine1' => $shipping_address['thoroughfare'],
            'addressLine2' => $shipping_address['premise'],
            'postcode' => $shipping_address['postal_code'],
            'country' => $shipping_address['country'],
            'city' => $shipping_address['locality'],
        );
      }

      $config['postedParam'] = array_merge($config['postedParam'], array(
          'email' => $order->mail,
          'value' => $amountCents,
          'trackId' => $orderId,
          'currency' => $order->commerce_order_total[LANGUAGE_NONE][0]['currency_code'],
          'description' => 'Order number::' . $orderId,
          'shippingDetails' => $shippingAddressConfig,
          'products' => $products,
          'card' => array (
              'billingDetails' => $billingAddressConfig,
          ),
      ));

      $Api = CheckoutApi_Api::getApi(array('mode' => $mode));

      $paymentTokenCharge = $Api->getPaymentToken($config);

      $paymentTokenArray = array(
          'message' => '',
          'success' => '',
          'eventId' => '',
          'token' => '',
      );

      if ($paymentTokenCharge->isValid()) {
        $paymentTokenArray['token'] = $paymentTokenCharge->getId();
        $paymentTokenArray['success'] = true;
      }
      else {

        $paymentTokenArray['message'] = $paymentTokenCharge->getExceptionState()->getErrorMessage();
        $paymentTokenArray['success'] = false;
        $paymentTokenArray['eventId'] = $paymentTokenCharge->getEventId();
      }
    }
    return $paymentTokenArray;
  }

  protected function _createCharge($config) {

    $config = array();

    $payment_method = commerce_payment_method_instance_load('commerce_checkoutpayment|commerce_payment_commerce_checkoutpayment');
    $scretKey = $payment_method['settings']['private_key'];
    $mode = $payment_method['settings']['mode'];
    $timeout = $payment_method['settings']['timeout'];

    $config['authorization'] = $scretKey;
    $config['timeout'] = $timeout;
    $config['paymentToken'] = $_POST['cko-cc-paymenToken'];

    $Api = CheckoutApi_Api::getApi(array('mode' => $mode));
    return $Api->verifyChargePaymentToken($config);
  }

  protected function _captureConfig($action) {
    $to_return['postedParam'] = array(
        'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE,
        'autoCapTime' => $action['settings']['autocaptime']
    );

    return $to_return;
  }

  protected function _authorizeConfig() {
    $to_return['postedParam'] = array(
        'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH,
        'autoCapTime' => 0
    );
    return $to_return;
  }
}