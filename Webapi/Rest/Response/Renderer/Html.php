<?php

namespace Paytr\Transfer\Webapi\Rest\Response\Renderer;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Response\RendererInterface;

/**
 * Class Html
 */
class Html implements RendererInterface
{

    public function getMimeType(): string
    {
        return 'text/html';
    }

    public function render($data)
    {
        if (is_string($data)) {
            return $data;
        } else {
            throw new Exception(
                __('Data is not html.')
            );
        }
    }
}
