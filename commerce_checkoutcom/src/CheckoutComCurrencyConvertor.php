<?php

namespace Drupal\commerce_checkoutcom;

/**
 * Class CheckoutComCurrencyConvertor.
 *
 * @ref https://github.com/checkout/checkout-magento2-plugin/blob/master/etc/config.xml
 *
 * @package Drupal\commerce_checkoutcom
 */
class CheckoutComCurrencyConvertor {

  /**
   * Currencies where charge amount is full.
   *
   * @var array
   */
  const FULL_VALUE_CURRENCIES = [
    'BIF', 'DJF', 'GNF', 'ISK', 'KMF',
    'XAF', 'CLF', 'XPF', 'JPY', 'PYG',
    'RWF', 'KRW', 'VUV', 'VND', 'XOF',
  ];

  /**
   * Currencies where charge amount is divided by 1000.
   *
   * @var array
   */
  const DIV_1000_VALUE_CURRENCIES = ['BHD', 'LYD', 'JOD', 'KWD', 'OMR', 'TND'];

  /**
   * Get Checkout.com amount.
   *
   * @param mixed $amount
   *   Actual amount
   * @param string $currency
   *
   * @return int
   *   Checkout.com amount.
   */
  public static function getCheckoutComAmount($amount, string $currency) {
    $multiplier = self::getMultiplier($currency);
    return round($amount, strlen($multiplier)) * $multiplier;
  }

  /**
   * @param mixed $amount
   *   Checkout.com amount.
   * @param string $currency
   *   Currency code.
   *
   * @return float
   *   Actual amount.
   */
  public static function getActualAmount($amount, string $currency) {
    $multiplier = self::getMultiplier($currency);
    return round($amount / $multiplier, strlen($multiplier));
  }

  /**
   * Get multiplier for currency.
   *
   * @param string $currency
   *   Currency code.
   *
   * @return int
   *   Multiplier.
   */
  protected static function getMultiplier(string $currency) {
    $multiplier = 100;

    if (in_array($currency, self::DIV_1000_VALUE_CURRENCIES)) {
      $multiplier = 1000;
    }
    elseif (in_array($currency, self::FULL_VALUE_CURRENCIES)) {
      $multiplier = 1;
    }

    return $multiplier;
  }

}
