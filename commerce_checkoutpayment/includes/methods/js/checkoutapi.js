/**
 * @file
 * CheckoutIntegration Api javascript functions.
 */

(function ($) {
  $(function () {
    var head = document.getElementsByTagName("head")[0];
    var s = document.createElement('script');
    s.type = 'text/javascript';
    s.async = true;
    if (Drupal.settings.commerce_checkoutpayment.mode === 'live') {
      s.src = "https://www.checkout.com/cdn/js/checkout.js";
    }
    else {
      s.src = "//sandbox.checkout.com/js/v1/checkout.js";
    }
    head.appendChild(s);

    $('#commerce-checkout-form-review input.checkout-continue.form-submit').click(function (event) {
      if ($('.widget-container').length) {
        event.preventDefault();
        CheckoutIntegration.open();
        $(this).hide().next().show().nextAll('input#edit-continue').hide();
        $('span.checkout-processing').removeClass('element-invisible');
      }
    });
  });

  Drupal.behaviors.commerce_checkoutpayment = {
    attach: function (context, settings) {

      var reload = false;
      window.CKOConfig = {
        debugMode: false,
        renderMode: 2, //displaying widget:- 0 All, 1 Pay Button Only, 2 Icons Only
        publicKey: Drupal.settings.commerce_checkoutpayment.publicKey,
        customerEmail: Drupal.settings.commerce_checkoutpayment.email,
        namespace: 'CheckoutIntegration',
        customerName: Drupal.settings.commerce_checkoutpayment.name,
        value: Drupal.settings.commerce_checkoutpayment.amount,
        currency: Drupal.settings.commerce_checkoutpayment.currency,
        paymentToken: Drupal.settings.commerce_checkoutpayment.paymentToken,
        widgetContainerSelector: '.widget-container', //The .class of the element hosting the Checkout.js widget card icons
        cardCharged: function (event) {
          document.getElementById('cko-cc-paymenToken').value = event.data.paymentToken;
          $('#commerce-checkout-form-review').trigger('submit');
          $('input.checkout-continue.form-submit').attr("disabled", 'disabled');
        },
        lightboxDeactivated: function () {
          $('span.checkout-processing').addClass('element-invisible');
          $('#commerce-checkout-form-review #edit-buttons input').first().show().nextAll('input#edit-continue').hide();
          if (reload) {
              window.location.reload();
          }
        },
        paymentTokenExpired: function () {
          reload = true;
        },
        invalidLightboxConfig: function () {
          reload = true;
         }
      };

      $('#edit-commerce-payment-payment-method-commerce-checkoutpaymentcommerce-payment-commerce-checkoutpayment').once('checkoutapi').change(function(){
        var interVal2 = setInterval(function () {
          if ($('.widget-container').length) {
            CheckoutIntegration.render(window.CKOConfig);
            clearInterval(interVal2);
          }
        }, 500);
      });
    }
  }
})(jQuery);