<?php

namespace AliyunMNS\Model;

use AliyunMNS\Constants;

class SubscriptionAttributes
{
    private $endpoint;
    private $strategy;
    private $contentFormat;

    // may change in AliyunMNS\Topic
    private $topicName;

    // the following attributes cannot be changed
    private $subscriptionName;
    private $topicOwner;
    private $createTime;
    private $lastModifyTime;

    public function __construct(
        $subscriptionName = null,
        $endpoint = null,
        $strategy = null,
        $contentFormat = null,
        $topicName = null,
        $topicOwner = null,
        $createTime = null,
        $lastModifyTime = null)
    {
        $this->endpoint = $endpoint;
        $this->strategy = $strategy;
        $this->contentFormat = $contentFormat;
        $this->subscriptionName = $subscriptionName;

        //cloud change in AliyunMNS\Topic
        $this->topicName = $topicName;

        $this->topicOwner = $topicOwner;
        $this->createTime = $createTime;
        $this->lastModifyTime = $lastModifyTime;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function getStrategy()
    {
        return $this->strategy;
    }

    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
    }

    public function getContentFormat()
    {
        return $this->contentFormat;
    }

    public function setContentFormat($contentFormat)
    {
        $this->contentFormat = $contentFormat;
    }

    public function getTopicName()
    {
        return $this->topicName;
    }

    public function setTopicName($topicName)
    {
        $this->topicName = $topicName;
    }

    public function getTopicOwner()
    {
        return $this->topicOwner;
    }

    public function getSubscriptionName()
    {
        return $this->subscriptionName;
    }

    public function getCreateTime()
    {
        return $this->createTime;
    }

    public function getLastModifyTime()
    {
        return $this->lastModifyTime;
    }

    public function writeXML(\XMLWriter $xmlWriter)
    {
        if (null != $this->endpoint) {
            $xmlWriter->writeElement(Constants::ENDPOINT, $this->endpoint);
        }
        if (null != $this->strategy) {
            $xmlWriter->writeElement(Constants::STRATEGY, $this->strategy);
        }
        if (null != $this->contentFormat) {
            $xmlWriter->writeElement(Constants::CONTENT_FORMAT, $this->contentFormat);
        }
    }

    public static function fromXML(\XMLReader $xmlReader)
    {
        $endpoint = null;
        $strategy = null;
        $contentFormat = null;
        $topicOwner = null;
        $topicName = null;
        $createTime = null;
        $lastModifyTime = null;

        while ($xmlReader->read()) {
            if (\XMLReader::ELEMENT == $xmlReader->nodeType) {
                switch ($xmlReader->name) {
                case 'TopicOwner':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $topicOwner = $xmlReader->value;
                    }
                    break;
                case 'TopicName':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $topicName = $xmlReader->value;
                    }
                    break;
                case 'SubscriptionName':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $subscriptionName = $xmlReader->value;
                    }
                    // no break
                case 'Endpoint':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $endpoint = $xmlReader->value;
                    }
                    break;
                case 'NotifyStrategy':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $strategy = $xmlReader->value;
                    }
                    break;
                case 'NotifyContentFormat':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $contentFormat = $xmlReader->value;
                    }
                    break;
                case 'CreateTime':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $createTime = $xmlReader->value;
                    }
                    break;
                case 'LastModifyTime':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $lastModifyTime = $xmlReader->value;
                    }
                    break;
                }
            }
        }

        $attributes = new SubscriptionAttributes(
            $subscriptionName,
            $endpoint,
            $strategy,
            $contentFormat,
            $topicName,
            $topicOwner,
            $createTime,
            $lastModifyTime);

        return $attributes;
    }
}
