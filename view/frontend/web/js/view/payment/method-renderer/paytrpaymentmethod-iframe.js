define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'mage/url',
    ],
    function (Component,placeOrderAction,url) {
        'use strict';
        return Component.extend(
            {
                defaults: {
                    template: 'Paytr_Transfer/payment/paytrpaymentmethod-iframe'
                },
                getPaymentAcceptanceMarkSrc: function () {
                    return window.checkoutConfig.payment.paytr_transfer.logo_url;
                },
                isLogoVisible: function () {
                    return window.checkoutConfig.payment.paytr_transfer.logo_visible;
                },
                afterPlaceOrder: function () {
                    window.location.replace(url.build('paytrtransfer/redirect/'));
                },
                getMailingAddress: function () {
                    return window.checkoutConfig.payment.checkmo.mailingAddress;
                },
            }
        );
    }
);
