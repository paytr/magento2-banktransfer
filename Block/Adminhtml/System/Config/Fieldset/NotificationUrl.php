<?php

namespace Paytr\Transfer\Block\Adminhtml\System\Config\Fieldset;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class NotificationUrl
 */
class NotificationUrl extends Field
{

    /**
     * @param  AbstractElement $element
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_storeManager->getStore()->getBaseUrl().'rest/V1/paytr/callback/';
    }
}
