<?php

namespace Paytr\Transfer\Model\PostbackNotification;

use Magento\Framework;

class Decoder implements DecoderInterface
{
    /**
     * @param  string $data
     * @return mixed
     */
    public function decode($data)
    {
        parse_str($data, $result);
        return $result;
    }
}
