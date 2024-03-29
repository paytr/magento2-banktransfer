<?php

namespace Paytr\Transfer\Controller\Redirect;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 *
 */
class Index extends \Magento\Framework\App\Action\Action
{

    protected $pageFactory;

    public function __construct(Context $context, PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->pageFactory = $this->pageFactory->create();
        $this->pageFactory->getConfig()->getTitle()->set((__('Do Not Leave This Page Until The Payment Is Completed')));
        return $this->pageFactory;
    }
}
