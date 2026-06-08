<?php

namespace WPMailSMTP\Vendor\GuzzleHttp;

use WPMailSMTP\Vendor\Psr\Http\Message\MessageInterface;
interface BodySummarizerInterface
{
    /**
     * Returns a summarized message body.
     */
    public function summarize(MessageInterface $message) : ?string;
}
