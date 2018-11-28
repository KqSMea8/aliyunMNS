<?php

namespace AliyunMNS\Responses;

use AliyunMNS\Common\XMLParser;
use AliyunMNS\Exception\MnsException;

class ListTopicResponse extends BaseResponse
{
    private $topicNames;
    private $nextMarker;

    public function __construct()
    {
        $this->topicNames = [];
        $this->nextMarker = null;
    }

    public function isFinished()
    {
        return null == $this->nextMarker;
    }

    public function getTopicNames()
    {
        return $this->topicNames;
    }

    public function getNextMarker()
    {
        return $this->nextMarker;
    }

    public function parseResponse($statusCode, $content)
    {
        $this->statusCode = $statusCode;
        if (200 != $statusCode) {
            $this->parseErrorResponse($statusCode, $content);

            return;
        }

        $this->succeed = true;
        $xmlReader = $this->loadXmlContent($content);

        try {
            while ($xmlReader->read()) {
                if (\XMLReader::ELEMENT == $xmlReader->nodeType) {
                    switch ($xmlReader->name) {
                    case 'TopicURL':
                        $xmlReader->read();
                        if (\XMLReader::TEXT == $xmlReader->nodeType) {
                            $topicName = $this->getTopicNameFromTopicURL($xmlReader->value);
                            $this->topicNames[] = $topicName;
                        }
                        break;
                    case 'NextMarker':
                        $xmlReader->read();
                        if (\XMLReader::TEXT == $xmlReader->nodeType) {
                            $this->nextMarker = $xmlReader->value;
                        }
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            throw new MnsException($statusCode, $e->getMessage(), $e);
        } catch (\Throwable $t) {
            throw new MnsException($statusCode, $t->getMessage());
        }
    }

    private function getTopicNameFromTopicURL($topicURL)
    {
        $pieces = explode('/', $topicURL);
        if (5 == count($pieces)) {
            return $pieces[4];
        }

        return '';
    }

    public function parseErrorResponse($statusCode, $content, MnsException $exception = null)
    {
        $this->succeed = false;
        $xmlReader = $this->loadXmlContent($content);

        try {
            $result = XMLParser::parseNormalError($xmlReader);

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
