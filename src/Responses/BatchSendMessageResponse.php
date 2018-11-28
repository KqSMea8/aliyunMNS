<?php

namespace AliyunMNS\Responses;

use AliyunMNS\Common\XMLParser;
use AliyunMNS\Constants;
use AliyunMNS\Exception\BatchSendFailException;
use AliyunMNS\Exception\InvalidArgumentException;
use AliyunMNS\Exception\MalformedXMLException;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Exception\QueueNotExistException;
use AliyunMNS\Model\SendMessageResponseItem;

class BatchSendMessageResponse extends BaseResponse
{
    protected $sendMessageResponseItems;

    public function __construct()
    {
        $this->sendMessageResponseItems = [];
    }

    public function getSendMessageResponseItems()
    {
        return $this->sendMessageResponseItems;
    }

    public function parseResponse($statusCode, $content)
    {
        $this->statusCode = $statusCode;
        if (201 == $statusCode) {
            $this->succeed = true;
        } else {
            $this->parseErrorResponse($statusCode, $content);
        }

        $xmlReader = $this->loadXmlContent($content);

        try {
            while ($xmlReader->read()) {
                if (\XMLReader::ELEMENT == $xmlReader->nodeType && 'Message' == $xmlReader->name) {
                    $this->sendMessageResponseItems[] = SendMessageResponseItem::fromXML($xmlReader);
                }
            }
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
            while ($xmlReader->read()) {
                if (\XMLReader::ELEMENT == $xmlReader->nodeType) {
                    switch ($xmlReader->name) {
                    case Constants::ERROR:
                        $this->parseNormalErrorResponse($xmlReader);
                        break;
                    default: // case Constants::Messages
                        $this->parseBatchSendErrorResponse($xmlReader);
                        break;
                    }
                }
            }
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

    private function parseBatchSendErrorResponse($xmlReader)
    {
        $ex = new BatchSendFailException($this->statusCode, 'BatchSendMessage Failed For Some Messages');
        while ($xmlReader->read()) {
            if (\XMLReader::ELEMENT == $xmlReader->nodeType && 'Message' == $xmlReader->name) {
                $ex->addSendMessageResponseItem(SendMessageResponseItem::fromXML($xmlReader));
            }
        }
        throw $ex;
    }

    private function parseNormalErrorResponse($xmlReader)
    {
        $result = XMLParser::parseNormalError($xmlReader);
        if (Constants::QUEUE_NOT_EXIST == $result['Code']) {
            throw new QueueNotExistException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
        }
        if (Constants::INVALID_ARGUMENT == $result['Code']) {
            throw new InvalidArgumentException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
        }
        if (Constants::MALFORMED_XML == $result['Code']) {
            throw new MalformedXMLException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
        }
        throw new MnsException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
    }
}
