<?php

namespace AliyunMNS\Traits;

use AliyunMNS\Constants;

trait MessagePropertiesForPublish
{
    public $messageBody;
    public $messageAttributes;

    public function getMessageBody()
    {
        return $this->messageBody;
    }

    public function setMessageBody($messageBody)
    {
        $this->messageBody = $messageBody;
    }

    public function getMessageAttributes()
    {
        return $this->messageAttributes;
    }

    public function setMessageAttributes($messageAttributes)
    {
        $this->messageAttributes = $messageAttributes;
    }

    public function writeMessagePropertiesForPublishXML(\XMLWriter $xmlWriter)
    {
        if (null != $this->messageBody) {
            $xmlWriter->writeElement(Constants::MESSAGE_BODY, $this->messageBody);
        }
        if (null !== $this->messageAttributes) {
            $this->messageAttributes->writeXML($xmlWriter);
        }
    }
}
