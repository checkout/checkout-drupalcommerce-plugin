<?php

namespace Drupal\commerce_checkoutcom;

use Drupal\commerce_payment\Exception\DeclineException;
use Drupal\commerce_payment\Exception\InvalidRequestException;
use Drupal\commerce_payment\Exception\InvalidResponseException;
use Checkout\Library\Exceptions\CheckoutHttpException;
use Drupal\commerce\Response\NeedsRedirectException;
use Drupal\commerce_payment\Entity\PaymentMethodInterface;

/**
 * Translates Checkout.com exceptions and errors into Commerce exceptions.
 */
class ErrorHelper {

  /**
   * Translates Checkout.com exceptions into Commerce exceptions.
   *
   * @param \Exception $exception
   *   The Checkout.com exception.
   *
   * @throws \Drupal\commerce_payment\Exception\PaymentGatewayException
   *   The Commerce exception.
   */
  public static function handleException(\Exception $exception) {
    if ($exception instanceof CheckoutHttpException) {
      \Drupal::logger('commerce_checkoutcom')->warning($exception->getMessage());
      throw new InvalidRequestException('There was an error with Checkout.com request.');
    }
    else {
      \Drupal::logger('commerce_checkoutcom')->warning($exception->getMessage());
      throw new InvalidResponseException('There was an error with Checkout.com request.');
    }
  }

  /**
   * Translates Checkout.com token expiration exception into Commerce exceptions.
   *
   * @param \Exception $exception
   *   The Checkout.com exception.
   * @param PaymentMethodInterface $payment_method
   *   The expired payment method.
   *
   * @throws \Drupal\commerce_payment\Exception\PaymentGatewayException
   *   The Commerce exception.
   */
  public static function handleTokenExpiration(\Exception $exception, PaymentMethodInterface $payment_method) {
    $payment_method->delete();
    \Drupal::logger('commerce_checkoutcom')->warning($exception->getMessage());
    throw new InvalidRequestException('We encountered an error processing your card details. Please re enter your details again');
  }

  /**
   * Translates Checkout.com errors into Commerce exceptions.
   *
   * @param object $result
   *   The Checkout.com result object.
   * @param object $request_type
   *   The Checkout.com request type.
   *
   * @throws \Drupal\commerce_payment\Exception\PaymentGatewayException
   *   The Commerce exception.
   */
  public static function handleErrors($result, $request_type) {
    switch ($request_type) {
      case 'token':
        if ($result->http_code == 201) {
          return;
        }
        else {
          throw new InvalidRequestException('We encountered an error processing your card details. Please verify your details and try again');
        }
        break;

      case 'payment':
        if ($result->http_code == 200 || $result->http_code == 201 || $result->http_code == 202) {
          if ($result->status == 'Declined') {
            throw new DeclineException('Checkout.com decline the payment.');
          }
          // If 3ds is required, redirect to 3ds page and throw exception to
          // prevent order completion.
          elseif ($result->status == 'Pending') {
            throw new NeedsRedirectException($result->getRedirection());
          }
          else {
            return;
          }
        }
        else {
          throw new InvalidRequestException('We encountered an error processing your card details. Please verify your details and try again');
        }
        break;

      case 'capture':
      case 'void':
        if ($result->http_code == 202) {
          return;
        }
        else {
          throw new InvalidRequestException('We encountered an error processing your card details. Please verify your details and try again');
        }
        break;

      case 'refund':
        if ($result->http_code == 202) {
          return;
        }
        else {
          throw new InvalidRequestException('We encountered an error processing your card details. Please verify your details and try again');
        }
        break;

    }
  }

}
