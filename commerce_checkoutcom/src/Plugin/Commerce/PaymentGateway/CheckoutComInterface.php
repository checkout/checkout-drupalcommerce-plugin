<?php

namespace Drupal\commerce_checkoutcom\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsRefundsInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsAuthorizationsInterface;

/**
 * Provides the interface for the Checkou.com payment gateway.
 */
interface CheckoutComInterface extends OnsitePaymentGatewayInterface, SupportsRefundsInterface, SupportsAuthorizationsInterface {

}
