/**
 * @file
 * CheckoutIntegration Api javascript functions.
 */

(function ($) {
  $(function () {
    var head = document.getElementsByTagName("head")[0],
    scriptJs = document.getElementById('checkoutApijs');

    if (!scriptJs) {
      scriptJs = document.createElement('script');

      scriptJs.src = 'https://www.checkout.com/cdn/js/checkout.js';
      scriptJs.id = 'checkoutApijs';
      scriptJs.type = 'text/javascript';
      var interVal = setInterval(function () {
        if (CheckoutIntegration) {
          $('head').append($('.widget-container link'));
            clearInterval(interVal);
        }

      }, 1000);
      head.appendChild(scriptJs);
    } 

    $('#commerce-checkout-form-review input.checkout-continue.form-submit').click(function (event) {
      if ($('.widget-container').length) {
        event.preventDefault();
        $('.messages.error').hide();
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
        renderMode: 2,
        publicKey: Drupal.settings.commerce_checkoutpayment.publicKey,
        customerEmail: Drupal.settings.commerce_checkoutpayment.email,
        namespace: 'CheckoutIntegration',
        customerName: Drupal.settings.commerce_checkoutpayment.name,
        value: Drupal.settings.commerce_checkoutpayment.amount,
        currency: Drupal.settings.commerce_checkoutpayment.currency,
        namespace: "CheckoutIntegration",
        paymentToken: Drupal.settings.commerce_checkoutpayment.paymentToken,
        paymentMode: 'card',
        widgetContainerSelector: '.widget-container',
        cardCharged: function (event) {
          document.getElementById('cko-cc-paymenToken').value = event.data.paymentToken;
          $('#commerce-checkout-form-review').trigger('submit');
          $('input.checkout-continue.form-submit').attr("disabled", 'disabled');
        },
        lightboxDeactivated: function (event) {
          $('span.checkout-processing').addClass('element-invisible');
          $('#commerce-checkout-form-review #edit-buttons input').first().show().nextAll('input#edit-continue').hide();
          if (reload) {
              window.location.reload();
          }
        },
        paymentTokenExpired: function (event) {
          reload = true;
        },
        invalidLightboxConfig: function (event) {
          reload = true;
         }
      }
	  var $_editElement = $('#edit-commerce-payment-payment-method-commerce-checkoutpaymentcommerce-payment-commerce-checkoutpayment');
      $_editElement.unbind('click.CheckoutApi').bind('click.CheckoutApi', function () {

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