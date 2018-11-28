<?php

namespace AliyunMNS\Model;

use AliyunMNS\Constants;
use AliyunMNS\Traits\MessageIdAndMD5;

/**
 * if isSucceed() == TRUE, the messageId and messageBodyMD5 are returned.
 * otherwise, the errorCode and errorMessage are returned.
 */
class SendMessageResponseItem
{
    use MessageIdAndMD5;

    protected $isSucceed;
    protected $errorCode;
    protected $errorMessage;

    public function __construct($isSucceed, $param1, $param2)
    {
        $this->isSucceed = $isSucceed;
        if (true == $isSucceed) {
            $this->messageId = $param1;
            $this->messageBodyMD5 = $param2;
        } else {
            $this->errorCode = $param1;
            $this->errorMessage = $param2;
        }
    }

    public function isSucceed()
    {
        return $this->isSucceed;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public static function fromXML($xmlReader)
    {
        $messageId = null;
        $messageBodyMD5 = null;
        $errorCode = null;
        $errorMessage = null;

        while ($xmlReader->read()) {
            switch ($xmlReader->nodeType) {
            case \XMLReader::ELEMENT:
                switch ($xmlReader->name) {
                case Constants::MESSAGE_ID:
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $messageId = $xmlReader->value;
                    }
                    break;
                case Constants::MESSAGE_BODY_MD5:
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $messageBodyMD5 = $xmlReader->value;
                    }
                    break;
                case Constants::ERROR_CODE:
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $errorCode = $xmlReader->value;
                    }
                    break;
                case Constants::ERROR_MESSAGE:
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $errorMessage = $xmlReader->value;
                    }
                    break;
                }
                break;
            case \XMLReader::END_ELEMENT:
                if ('Message' == $xmlReader->name) {
                    if (null != $messageId) {
                        return new SendMessageResponseItem(true, $messageId, $messageBodyMD5);
                    } else {
                        return new SendMessageResponseItem(false, $errorCode, $errorMessage);
                    }
                }
                break;
            }
        }

        if (null != $messageId) {
            return new SendMessageResponseItem(true, $messageId, $messageBodyMD5);
        } else {
            return new SendMessageResponseItem(false, $errorCode, $errorMessage);
        }
    }
}
