<?php

namespace Paytr\Transfer\Model\Adminhtml\System\Config;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\Phrase;

/**
 *
 */
class PaytrNotificationUrlComment implements CommentInterface
{

    /**
     * @param $elementValue
     * @return Phrase
     */
    public function getCommentText($elementValue): Phrase
    {
        return __('Add the NOTIFICATION URL ADDRESS above to the <a href="https://www.paytr.com/magaza/ayarlar" target="_blank"> Notification URL </a> setting.');
    }
}
