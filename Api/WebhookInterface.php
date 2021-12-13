<?php

namespace Paytr\Transfer\Api;

/**
 * Interface WebhookInterface
 */
interface WebhookInterface
{
    /**
     * GET for Post api
     *
     * @return string
     */
    public function getResponse(): string;
}
