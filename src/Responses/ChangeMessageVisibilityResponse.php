<?php

namespace AliyunMNS\Responses;

use AliyunMNS\Common\XMLParser;
use AliyunMNS\Constants;
use AliyunMNS\Exception\InvalidArgumentException;
use AliyunMNS\Exception\MessageNotExistException;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Exception\QueueNotExistException;
use AliyunMNS\Exception\ReceiptHandleErrorException;
use AliyunMNS\Model\Message;

class ChangeMessageVisibilityResponse extends BaseResponse
{
    protected $receiptHandle;
    protected $nextVisibleTime;

    public function __construct()
    {
    }

    public function getReceiptHandle()
    {
        return $this->receiptHandle;
    }

    public function getNextVisibleTime()
    {
        return $this->nextVisibleTime;
    }

    public function parseResponse($statusCode, $content)
    {
        $this->statusCode = $statusCode;
        if (200 == $statusCode) {
            $this->succeed = true;
        } else {
            $this->parseErrorResponse($statusCode, $content);
        }

        $xmlReader = $this->loadXmlContent($content);

        try {
            $message = Message::fromXML($xmlReader, true);
            $this->receiptHandle = $message->getReceiptHandle();
            $this->nextVisibleTime = $message->getNextVisibleTime();
        } catch (\Exception $e) {
            throw new MnsException($statusCode, $e->getMessage(), $e);
        } catch (\Throwable $t) {
            throw new MnsException($statusCode, $t->getMessage());
        }
    }

    public function parseErrorResponse($statusCode, $content, MnsException $exception = null)
    {
        $this->succeed = false;
        $xmlReader = $this->loadXmlContent($content);

        try {
            $result = XMLParser::parseNormalError($xmlReader);

            if (Constants::INVALID_ARGUMENT == $result['Code']) {
                throw new InvalidArgumentException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
            }
            if (Constants::QUEUE_NOT_EXIST == $result['Code']) {
                throw new QueueNotExistException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
            }
            if (Constants::MESSAGE_NOT_EXIST == $result['Code']) {
                throw new MessageNotExistException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
            }
            if (Constants::RECEIPT_HANDLE_ERROR == $result['Code']) {
                throw new ReceiptHandleErrorException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
            }

            throw new MnsException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
        } catch (\Exception $e) {
            if (null != $exception) {
                throw $exception;
            } elseif ($e instanceof MnsException) {
                throw $e;
            } else {
                throw new MnsException($statusCode, $e->getMessage());
            }
        } catch (\Throwable $t) {
            throw new MnsException($statusCode, $t->getMessage());
        }
    }
}
