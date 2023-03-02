<?php

namespace Paytr\Transfer\Plugin\Magento\Framework\Webapi\Rest;

/**
 * Class Request
 */
class Request
{

    public function afterGetAcceptTypes(\Magento\Framework\Webapi\Rest\Request $subject, array $result)
    {
        if (strpos($subject->getRequestUri(), 'rest/V1/paytr/callback') !== false) {
            $result = ['text/html'];
        }
        return $result;
    }
}
