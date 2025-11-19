<?php

namespace Paytr\Transfer\Model\Api;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\Builder as TransactionBuilder;
use Magento\Sales\Model\OrderFactory;
use Paytr\Transfer\Helper\PaytrHelper;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Response\Http as HttpResponse;

class Webhook
{
    protected OrderFactory $orderFactory;
    protected ScopeConfigInterface $config;
    protected TransactionBuilder $transactionBuilder;
    protected TransactionRepositoryInterface $transactionRepository;
    protected Request $request;
    protected PaytrHelper $paytrHelper;
    private OrderSender $orderSender;
    private LoggerInterface $logger;
    private HttpResponse $httpResponse;
    private string $responseText = '';

    public function __construct(
        OrderFactory $orderFactory,
        Context $context,
        TransactionBuilder $tb,
        TransactionRepositoryInterface $transactionRepository,
        Request $request,
        PaytrHelper $paytrHelper,
        OrderSender $orderSender,
        LoggerInterface $logger,
        HttpResponse $httpResponse
    ) {
        $this->orderFactory             = $orderFactory;
        $this->config                   = $context->getScopeConfig();
        $this->transactionBuilder       = $tb;
        $this->transactionRepository    = $transactionRepository;
        $this->request                  = $request;
        $this->paytrHelper              = $paytrHelper;
        $this->orderSender              = $orderSender;
        $this->logger                   = $logger;
        $this->httpResponse             = $httpResponse;
    }

    /**
     * Returns webhook response
     *
     * @return void
     */
    public function getResponse(): void
    {
        $response = $this->responseNormalize($this->request->getBodyParams());

        $resultText = (array_key_exists('status', $response) && $response['status'] === 'success')
            ? $this->getSuccessResponse($response)
            : $this->getFailedResponse($response);

        $this->httpResponse->setHeader('Content-Type', 'text/plain', true);
        $this->httpResponse->setBody($resultText);

        $this->httpResponse->sendResponse();
        @exit;
    }

    /**
     * Returns webhook response
     *
     * @return string
     */
    public function getSuccessResponse($response): string
    {
        if ($this->validateHash($response, $response['hash'])) {
            $order_id   = $this->normalizeMerchantOid($response['merchant_oid']);
            $order      = $this->orderFactory->create()->load($order_id);
            return $this->addTransactionToOrder($order, $response);
        } else {
            return 'PAYTR notification failed: bad hash';
        }
    }

    /**
     * Returns webhook response
     *
     * @return string
     */
    public function getFailedResponse($response): string
    {
        if ($this->validateHash($response, $response['hash'])) {
            $order_id   = $this->normalizeMerchantOid($response['merchant_oid']);
            $order      = $this->orderFactory->create()->load($order_id);
            if($order->getState() == Order::STATE_PENDING_PAYMENT ||
                $order->getState() == Order::STATE_NEW) {
                $order->addStatusHistoryComment($response['failed_reason_msg']);
                $order->cancel();
                $order->setState(Order::STATE_CANCELED);
                $order->setStatus("canceled");
                $order->save();
                return 'OK';
            }
            return 'OK';
        } else {
            return 'PAYTR notification failed: bad hash';
        }
    }

    /**
     * Returns webhook response
     *
     * @return bool
     */
    public function validateHash($response, $hash): bool
    {
        return base64_encode(hash_hmac('sha256', $response['merchant_oid'] . $this->paytrHelper->getMerchantSalt() . $response['status'] . $response['total_amount'], $this->paytrHelper->getMerchantKey(), true)) === $hash;
    }

    /**
     * Returns webhook response
     *
     * @return array
     */
    public function responseNormalize($params): array
    {
        $items = [];
        foreach ($params as $key => $param) {
            $items[$key] = $param;
        }
        return $items;
    }

    /**
     * Returns webhook response
     *
     * @return mixed
     */
    public function normalizeMerchantOid($merchant_oid)
    {
        $merchant_oid = explode('SP', $merchant_oid);
        $merchant_oid = explode('MG', $merchant_oid[1]);
        return $merchant_oid[0];
    }

    /**
     * Returns webhook response
     *
     * @return mixed
     */
    public function addTransactionToOrder($order, $response)
    {
        if ($order->getState()) {
            if($order->getState() == Order::STATE_PENDING_PAYMENT ||
                $order->getState() == Order::STATE_NEW ||
                $order->getState() == Order::STATE_CANCELED) {
                $order->setState(Order::STATE_PROCESSING, true);
                $order->setStatus(Order::STATE_PROCESSING);
                $order->save();
                $payment = $order->getPayment();
                $payment->setLastTransId($response['merchant_oid']);
                $payment->setTransactionId($response['merchant_oid']);
                $transaction = $this->transactionBuilder->setPayment($payment)
                    ->setOrder($order)
                    ->setTransactionId($response['merchant_oid'])
                    ->setAdditionalInformation(
                        [Transaction::RAW_DETAILS => (array) $response]
                    )
                    ->setFailSafe(true)
                    ->build(Transaction::TYPE_ORDER);
                $payment->addTransactionCommentsToOrder(
                    $transaction,
                    $this->customNote($response, $order)
                );
                $payment->setParentTransactionId(null);
                $payment->save();
                $order->save();
                try {
                    $this->orderSender->send($order);
                } catch (\Throwable $e) {
                    $this->logger->critical($e);
                }
                return 'OK';
            }
            return 'OK';
        }
        return 'HATA: Sipariş durumu tamamlanmadı. Tekrar deneniyor.';
    }

    /**
     * Returns webhook response
     *
     * @return mixed
     */
    public function customNote($response, $order)
    {
        $currency               = $this->orderFactory->create()->load($order->getRealOrderId());
        $currency               = $currency->getOrderCurrency()->getId();
        $maturity_difference    = 'Vade Farkı: ' . (round(($response['total_amount'] - $response['payment_amount']) / 100)) . ' ' . $currency . '<br>';
        $total_amount           = number_format(($response['total_amount'] / 100), 2, '.', '.');
        $amount                 = number_format(($response['payment_amount'] / 100), 2, '.', '.');
        $note = '<b>' . __('PAYTR NOTICE - Payment Accepted') . '</b><br>';
        $note .= __('Total Paid') . ': ' . $total_amount . ' ' . $currency . '<br>';
        $note .= __('Paid') . ': ' . $amount . ' ' . $currency . '<br>';
        $note .= ($response['installment_count'] === '1' ? '' : $maturity_difference);
        $note .= __('Installment Count') . ': ' . ($response['installment_count'] === '1' ? 'Tek Çekim' : $response['installment_count']) . '<br>';
        $note .= __('PayTR Order Number') . ': <a href="https://www.paytr.com/magaza/islemler?merchant_oid=' . $response['merchant_oid'] . '" target="_blank">' . $response['merchant_oid'] . '</a><br>';
        return $note;
    }
}
