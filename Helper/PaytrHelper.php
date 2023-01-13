<?php

namespace Paytr\Transfer\Helper;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class PaytrHelper
{

    protected ScopeConfigInterface $config;
    protected Session $checkoutSession;
    protected OrderFactory $orderFactory;
    protected StoreManagerInterface $_storeManager;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderFactory $orderFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->config               = $context->getScopeConfig();
        $this->checkoutSession      = $checkoutSession;
        $this->orderFactory         = $orderFactory;
        $this->_storeManager        = $storeManager;
    }

    public function getScopeInterface()
    {
        return ScopeInterface::SCOPE_STORE;
    }

    public function getMerchantId()
    {
        return $this->config->getValue('payment/paytr_iframe_transfer/merchant_id', $this->getScopeInterface());
    }

    public function getMerchantSalt()
    {
        return $this->config->getValue('payment/paytr_iframe_transfer/merchant_salt', $this->getScopeInterface());
    }

    public function getOrderStatus()
    {
        return $this->config->getValue('payment/paytr_iframe_transfer/order_status', $this->getScopeInterface());
    }

    public function getMerchantKey()
    {
        return $this->config->getValue('payment/paytr_iframe_transfer/merchant_key', $this->getScopeInterface());
    }

    public function getDebugOn()
    {
        return $this->config->getValue('payment/paytr_iframe_transfer/debug_on', $this->getScopeInterface());
    }

    public function getTestMode()
    {
        return $this->config->getValue('payment/paytr_iframe_transfer/test_mode', $this->getScopeInterface());
    }

    public function getRealOrderId()
    {
        return $this->checkoutSession->getLastRealOrder()->getId();
    }

    public function getOrder()
    {
        return $this->orderFactory->create()->load($this->getRealOrderId());
    }

    public function getUserIp()
    {
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        return $ip;
    }

    public function getMerchantOid()
    {
        return 'SP'.$this->getRealOrderId().'MG'.strtotime($this->getOrder()->getUpdatedAt());
    }

    public function getBilling()
    {
        return $this->getOrder()->getBillingAddress();
    }

    public function getEmail()
    {
        return $this->getBilling()->getEmail();
    }

    public function getPaymentAmount()
    {
        return substr(str_replace('.', '', $this->getOrder()->getBaseGrandTotal()), 0, -2);
    }

    public function getTimeoutLimit()
    {
        return $this->config->getValue('payment/paytr_iframe_transfer/timeout_limit', $this->getScopeInterface());
    }

    public function makeHashStr()
    {
        return
            $this->getMerchantId()
            . $this->getUserIp()
            . $this->getMerchantOid()
            . $this->getEmail()
            . $this->getPaymentAmount()
            . 'eft'
            . $this->getTestMode();
    }

    public function getToken()
    {
        return base64_encode(hash_hmac('sha256', $this->makeHashStr().$this->getMerchantSalt(), $this->getMerchantKey(), true));
    }

    public function makePostVariables()
    {
        return array(
            'merchant_id'       =>  $this->getMerchantId(),
            'user_ip'           =>  $this->getUserIp(),
            'merchant_oid'      =>  $this->getMerchantOid(),
            'email'             =>  $this->getEmail(),
            'payment_amount'    =>  $this->getPaymentAmount(),
            'payment_type'      =>  'eft',
            'paytr_token'       =>  $this->getToken(),
            'debug_on'          =>  $this->getDebugOn(),
            'timeout_limit'     =>  $this->getTimeoutLimit(),
            'test_mode'         =>  $this->getTestMode(),
        );
    }
}
