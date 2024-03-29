<?php

namespace Paytr\Transfer\Helper;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PaytrRequestHelper
 */
class PaytrRequestHelper
{

    protected PaytrHelper $paytrHelper;
    public function __construct(PaytrHelper $paytrHelper)
    {
        $this->paytrHelper = $paytrHelper;
    }

    public function getPaytrToken(): string
    {
        return $this->callCurl($this->paytrHelper->makePostVariables());
    }

    private function callCurl($variables)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $variables);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return "PAYTR IFRAME connection error. err:".curl_error($ch);
        }
        curl_close($ch);
        $result = json_decode($result, 1);
        if ($result['status']=='success') {
            $token = $result['token'];
        } else {
            return "PAYTR IFRAME failed. reason:".$result['reason'];
        }
        return $token;
    }
}
