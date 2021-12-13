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

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $config;

    /**
     * @var Session
     */
    protected Session $checkoutSession;

    /**
     * @var OrderFactory
     */
    protected OrderFactory $orderFactory;

    /**
     * @var StoreManagerInterface
     */
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

    /**
     * @return string
     */
    public function getScopeInterface(): string
    {
        return ScopeInterface::SCOPE_STORE;
    }

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->config->getValue('payment/paytr_iframe_transfer/merchant_id', $this->getScopeInterface()) ?? '151591';
    }

    /**
     * @return mixed
     */
    public function getMerchantSalt()
    {
        return $this->config->getValue('payment/paytr_iframe_transfer/merchant_salt', $this->getScopeInterface()) ?? 'EbZ8rC8CEF7HU8hM';
    }

    public function getOrderStatus()
    {
        return $this->config->getValue('payment/paytr_iframe_transfer/order_status', $this->getScopeInterface());
    }

    /**
     * @return mixed
     */
    public function getMerchantKey()
    {
        return $this->config->getValue('payment/paytr_iframe_transfer/merchant_key', $this->getScopeInterface()) ?? 'NnYLzPw6CTtoNk5K';
    }

    /**
     * @return mixed
     */
    public function getDebugOn()
    {
        return $this->config->getValue('payment/paytr_iframe_transfer/debug_on', $this->getScopeInterface());
    }

    /**
     * @return mixed
     */
    public function getTestMode()
    {
        return $this->config->getValue('payment/paytr_iframe_transfer/test_mode', $this->getScopeInterface()) ?? 1;
    }

    /**
     * @return mixed
     */
    public function getRealOrderId()
    {
        return $this->checkoutSession->getLastRealOrder()->getId();
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->orderFactory->create()->load($this->getRealOrderId());
    }

    /**
     * @return mixed
     */
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

    /**
     * @return string
     */
    public function getMerchantOid(): string
    {
        return 'SP'.$this->getRealOrderId().'MG'.strtotime($this->getOrder()->getUpdatedAt());
    }

    /**
     * @return OrderAddressInterface|null
     */
    public function getBilling(): ?OrderAddressInterface
    {
        return $this->getOrder()->getBillingAddress();
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getBilling()->getEmail();
    }

    /**
     * @return false|string
     */
    public function getPaymentAmount()
    {
        return substr(str_replace('.', '', $this->getOrder()->getBaseGrandTotal()), 0, -2);
    }

    /**
     * @return int
     */
    public function getTimeoutLimit(): int
    {
        return 30;
    }

    /**
     * @return string
     */
    public function makeHashStr(): string
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

    /**
     * @return string
     */
    public function getToken(): string
    {
        return base64_encode(hash_hmac('sha256', $this->makeHashStr().$this->getMerchantSalt(), $this->getMerchantKey(), true));
    }

    /**
     * @return array
     */
    public function makePostVariables(): array
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
