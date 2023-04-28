<?php

namespace Paytr\Transfer\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

/**
 * Class ControllerActionPredispatch
 */
class ControllerActionPredispatch implements ObserverInterface
{

    /**
     * @var Session
     */
    protected Session $checkoutSession;

    /**
     * @var OrderFactory
     */
    protected OrderFactory $orderFactory;

    /**
     * @var Http
     */
    protected Http $_redirect;

    /**
     * @var mixed
     */
    protected $urlBuilder;

    /**
     * ControllerActionPredispatch constructor.
     *
     * @param Session      $checkoutSession
     * @param OrderFactory $orderFactory
     * @param Http         $redirect
     */
    public function __construct(
        Session $checkoutSession,
        OrderFactory $orderFactory,
        Http $redirect
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->_redirect = $redirect;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getData('request');
        if ($request->getModuleName() == "checkout" and $request->getActionName()== "success") {
            $orderId = $this->checkoutSession->getLastOrderId();
            if ($orderId) {
                $order = $this->orderFactory->create()->load($orderId);
                if (($order->getPayment()->getMethodInstance()->getCode()== "paytr_iframe_transfer"
                    ) && $order->getState()== Order::STATE_NEW) {
                    $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
                    $url = $this->urlBuilder->getUrl("paytrtransfer/redirect");
                    $this->_redirect->setRedirect($url);
                }
            }
        }
    }
}
