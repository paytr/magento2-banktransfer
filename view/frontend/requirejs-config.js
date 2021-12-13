let config = {
    config: {
        mixins: {
            'Magento_Catalog/js/price-box': {
                'Paytr_Transfer/js/pricebox': true
            }
        }
    },
    map: {
        '*': {
            iframeResizer: "Paytr_Transfer/js/iframeResizer.min"
        }
    }
};
