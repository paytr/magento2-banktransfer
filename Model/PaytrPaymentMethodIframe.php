<?php

namespace Paytr\Transfer\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class PaytrPaymentMethodIframe
 */
class PaytrPaymentMethodIframe extends AbstractMethod
{

    protected $_code                = 'paytr_iframe_transfer';
    protected $_isInitializeNeeded  = true;
    protected $_isOffline            = true;

    /**
     * @return string[][][]
     */
    public function getConfig(): array
    {
        $objectManager   = ObjectManager::getInstance();
        $logo            = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('payment/paytr_iframe_transfer/paytr_logo');
        return [
            'payment' => [
                'paytr_transfer' => [
                    'logo_url' => 'https://www.paytr.com/img/general/paytr.svg',
                    'logo_visible' => $logo ? 'display: inline' : 'display: none'
                ]
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getOrderPlaceRedirectUrl()
    {
        return ObjectManager::getInstance()->get('Magento\Framework\UrlInterface')->getUrl("paytrtransfer/redirect");
    }
}
