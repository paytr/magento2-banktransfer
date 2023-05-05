<?php

namespace Paytr\Transfer\Webapi\Rest\Request\Deserializer;

use Magento\Framework\App\State;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception;
use Paytr\Transfer\Model\PostbackNotification\Decoder;

/**
 * Class XWwwFormUrlencoded
 */
class XWwwFormUrlencoded implements \Magento\Framework\Webapi\Rest\Request\DeserializerInterface
{

    protected Decoder $decoder;

    protected State $appState;

    /**
     * @param Decoder $decoder
     * @param State $appState
     */
    public function __construct(Decoder $decoder, State $appState)
    {
        $this->decoder = $decoder;
        $this->appState = $appState;
    }

    /**
     * @param $encodedBody
     * @return array|mixed|null
     * @throws Exception
     */
    public function deserialize($encodedBody)
    {
        if (!is_string($encodedBody)) {
            throw new \InvalidArgumentException(
                sprintf('"%s" data type is invalid. String is expected.', gettype($encodedBody))
            );
        }
        try {
            $decodedBody = $this->decoder->decode($encodedBody);
        } catch (\InvalidArgumentException $e) {
            if ($this->appState->getMode() !== State::MODE_DEVELOPER) {
                throw new Exception(new Phrase('Decoding error.'));
            } else {
                throw new Exception(
                    new Phrase(
                        'Decoding error: %1%2%3%4',
                        [PHP_EOL, $e->getMessage(), PHP_EOL, $e->getTraceAsString()]
                    )
                );
            }
        }
        return $decodedBody;
    }
}
