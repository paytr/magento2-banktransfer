define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'paytr_iframe_transfer',
                component: 'Paytr_Transfer/js/view/payment/method-renderer/paytrpaymentmethod-iframe'
            }
        );
        return Component.extend({});
    }
);
