# Commerce Checkout.com

## CONTENTS OF THIS FILE

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * How it works
 * Maintainers

## INTRODUCTION

**Commerce Checkout.com** is [Drupal Commerce](https://drupal.org/project/commerce)
module that integrates the [Checkout.com](https://www.checkout.com/) payement
gateway into your Drupal Commerce shop.


## REQUIREMENTS

This module requires the following:
* Submodules of Drupal Commerce package (https://drupal.org/project/commerce)
  - Commerce core
  - Commerce Payment (and its dependencies)
* Checkout.com PHP SDK (https://github.com/checkout/checkout-sdk-php)
* Checkout.com Hub account (test or live) (https://www.checkout.com/)


## INSTALLATION

* This module needs to be installed via Composer, which will download
the required libraries.
composer require "drupal/commerce_checkoutcom"
https://www.drupal.org/docs/8/extending-drupal-8/installing-modules-composer-dependencies

## CONFIGURATION

* Create a new Checkout.com payment gateway.
  Administration > Commerce > Configuration > Payment gateways > Add payment gateway
  Checkout.com-specific settings available:
  - Secret key
  - Public key
  Use the API credentials provided by your Checkout.com hub account.
  You can find the keys in Settings > Channels > API keys.
  It is recommended to enter test credentials and then override these with live
  credentials in settings.php. This way, live credentials will not be stored in
  the db.


## HOW IT WORKS

* General considerations:
  - The store owner must have a Checkout.com hub account.
    Sign up here:
    https://www.checkout.com
  - Customers should have a valid credit card.
    - Checkout.com provides several dummy credit card numbers for testing:
      https://docs.checkout.com/docs/testing

* Checkout workflow:
  It follows the Drupal Commerce Credit Card workflow.
  The customer should enter his/her credit card data
  or select one of the credit cards saved with Checkout.com
  from a previous order.

* 3DS support:
  This module supports 3DS secure payments, you may be redirected to your
  payment provider page to authenticate the payment and then returned back to
  the website to complete the order.

* Payment Terminal
  The store owner can Void, Capture and Refund the Checkout.com payments.

* Webhooks:
  This module will create a payment capture webhook on your Checkout.com hub
  account. This is needed because Checkout.com always return authorization
  status in the payment response even if you choose to capture the payment
  directly. Because the capture happens just after the authorization
  asynchronously.
  **DON'T DELETE THIS WEBHOOK FROM YOUR CHECKOUT.COM HUB MANUALLY.** If you
  delete it by mistake just navigate to the payment gateway settings page
  and hit save, a new capture webhook will be created automatically.


## MAINTAINERS

Current maintainers:
* Anas Mawlawi (Anas_maw) - https://www.drupal.org/u/Anas_maw
* Yasser Samman (yasser-samman) - https://www.drupal.org/u/yasser-samman

This project has been developed by:
* Coders Enterprise Web & Mobile Solutions: https://www.codersme.com/
