<?php

namespace Paytr\Transfer\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Paytr\Transfer\Helper\PaytrHelper;
use Paytr\Transfer\Helper\PaytrRequestHelper;

/**
 * Class Redirect
 */
class Redirect extends \Magento\Framework\View\Element\Template
{

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $config;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $_messageManager;

    /**
     * @var PaytrHelper
     */
    protected PaytrHelper $paytrHelper;

    /**
     * @var PaytrRequestHelper
     */
    protected PaytrRequestHelper $paytrRequestHelper;

    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        PaytrHelper $paytrHelper,
        PaytrRequestHelper $paytrRequestHelper
    ) {
        $this->config = $context->getScopeConfig();
        $this->_messageManager = $messageManager;
        $this->paytrHelper = $paytrHelper;
        $this->paytrRequestHelper = $paytrRequestHelper;
        parent::__construct($context);
    }

    protected function _prepareLayout()
    {
        try {
            if (!$this->paytrHelper->getOrder()->getRealOrderId()) {
                header('Location: '. $this->_storeManager->getStore()->getBaseUrl());
                return false;
            }
            $paytr_data = [
                'status' => 'success',
                'token' => $this->paytrRequestHelper->getPaytrToken(),
                'message' => ''
            ];
            $this->setAction(json_encode($paytr_data));
        } catch (\Exception $e) {
            $this->_messageManager->addErrorMessage('An error occurred. Please try again.');
        }
        return true;
    }
}
