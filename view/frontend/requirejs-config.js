let config = {
    config: {
        mixins: {
            'Magento_Catalog/js/price-box': {
                'Paytr_Transfer/js/priceboxBankTransfer': true
            }
        }
    },
    map: {
        '*': {
            iframeResizerBankTransfer: "Paytr_Transfer/js/iframeResizerBankTransfer.min"
        }
    }
};
