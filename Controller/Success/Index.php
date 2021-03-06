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

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     *
     * @var SuccessValidator
     */
    private SuccessValidator $successValidator;

    /**
     *
     * @param Context                  $context
     * @param OrderRepositoryInterface $orderRepository
     * @param CheckoutSession          $checkoutSession
     * @param SuccessValidator         $successValidator
     */
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

    /**
     * @return Redirect
     * @throws Exception
     */
    public function execute(): Redirect
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $order->setState(Order::STATE_PROCESSING)->setStatus(Order::STATE_PROCESSING)->save();
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
