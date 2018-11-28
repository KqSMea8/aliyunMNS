<?php

namespace AliyunMNS\Responses;

use AliyunMNS\Common\XMLParser;
use AliyunMNS\Constants;
use AliyunMNS\Exception\InvalidArgumentException;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Exception\SubscriptionAlreadyExistException;

class SubscribeResponse extends BaseResponse
{
    public function parseResponse($statusCode, $content)
    {
        $this->statusCode = $statusCode;
        if (201 == $statusCode || 204 == $statusCode) {
            $this->succeed = true;
        } else {
            $this->parseErrorResponse($statusCode, $content);
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
            if (Constants::SUBSCRIPTION_ALREADY_EXIST == $result['Code']) {
                throw new SubscriptionAlreadyExistException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
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
