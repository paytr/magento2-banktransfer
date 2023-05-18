<?php

namespace Paytr\Transfer\Controller\Success;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Session\SuccessValidator;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Class Index
 */
class Index extends \Magento\Framework\App\Action\Action
{

    private CheckoutSession $checkoutSession;

    private SuccessValidator $successValidator;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        CheckoutSession $checkoutSession,
        SuccessValidator $successValidator
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->successValidator = $successValidator;
    }

    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if($order->getState() == Order::STATE_PENDING_PAYMENT ||
            $order->getState() == Order::STATE_NEW ||
            $order->getState() == null) {
            $order->setState(Order::STATE_PENDING_PAYMENT);
            if ($order->getPayment()->getMethodInstance()->getCode() == "paytr_iframe_transfer") {
                $order->setStatus('paytr_pending');
            }
            $order->save();
        }
        $this->checkoutSession->setLastOrderId($order->getId())
            ->setLastSuccessQuoteId($order->getQuoteId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());
        if (!$this->successValidator->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
    }
}
