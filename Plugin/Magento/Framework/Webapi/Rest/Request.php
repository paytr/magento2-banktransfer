<?php

namespace Paytr\Transfer\Plugin\Magento\Framework\Webapi\Rest;

/**
 * Class Request
 */
class Request
{

    /**
     * @param  \Magento\Framework\Webapi\Rest\Request $subject
     * @param  array                                  $result
     * @return array|string[]
     */
    public function afterGetAcceptTypes(\Magento\Framework\Webapi\Rest\Request $subject, array $result): array
    {
        if ($subject->getRequestUri() === '/rest/V1/paytr/callback/' || $subject->getRequestUri() === '/index.php/rest/V1/paytr/callback/') {
            $result = ['text/html'];
        }
        return $result;
    }
}
