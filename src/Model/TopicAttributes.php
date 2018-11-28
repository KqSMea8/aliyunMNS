<?php

namespace AliyunMNS\Model;

use AliyunMNS\Constants;

/**
 * Please refer to
 * https://docs.aliyun.com/?spm=#/pub/mns/api_reference/intro&intro
 * for more details
 */
class TopicAttributes
{
    private $maximumMessageSize;
    private $messageRetentionPeriod;
    private $LoggingEnabled;

    // the following attributes cannot be changed
    private $topicName;
    private $createTime;
    private $lastModifyTime;

    public function __construct(
        $maximumMessageSize = null,
        $messageRetentionPeriod = null,
        $topicName = null,
        $createTime = null,
        $lastModifyTime = null,
        $LoggingEnabled = null)
    {
        $this->maximumMessageSize = $maximumMessageSize;
        $this->messageRetentionPeriod = $messageRetentionPeriod;
        $this->loggingEnabled = $LoggingEnabled;

        $this->topicName = $topicName;
        $this->createTime = $createTime;
        $this->lastModifyTime = $lastModifyTime;
    }

    public function setMaximumMessageSize($maximumMessageSize)
    {
        $this->maximumMessageSize = $maximumMessageSize;
    }

    public function getMaximumMessageSize()
    {
        return $this->maximumMessageSize;
    }

    public function setLoggingEnabled($loggingEnabled)
    {
        $this->loggingEnabled = $loggingEnabled;
    }

    public function getLoggingEnabled()
    {
        return $this->loggingEnabled;
    }

    public function setMessageRetentionPeriod($messageRetentionPeriod)
    {
        $this->messageRetentionPeriod = $messageRetentionPeriod;
    }

    public function getMessageRetentionPeriod()
    {
        return $this->messageRetentionPeriod;
    }

    public function getTopicName()
    {
        return $this->topicName;
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
        if (null != $this->maximumMessageSize) {
            $xmlWriter->writeElement(Constants::MAXIMUM_MESSAGE_SIZE, $this->maximumMessageSize);
        }
        if (null != $this->messageRetentionPeriod) {
            $xmlWriter->writeElement(Constants::MESSAGE_RETENTION_PERIOD, $this->messageRetentionPeriod);
        }
        if (null !== $this->loggingEnabled) {
            $xmlWriter->writeElement(Constants::LOGGING_ENABLED, $this->loggingEnabled ? 'True' : 'False');
        }
    }

    public static function fromXML(\XMLReader $xmlReader)
    {
        $maximumMessageSize = null;
        $messageRetentionPeriod = null;
        $topicName = null;
        $createTime = null;
        $lastModifyTime = null;
        $loggingEnabled = null;

        while ($xmlReader->read()) {
            if (\XMLReader::ELEMENT == $xmlReader->nodeType) {
                switch ($xmlReader->name) {
                case 'MaximumMessageSize':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $maximumMessageSize = $xmlReader->value;
                    }
                    break;
                case 'MessageRetentionPeriod':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $messageRetentionPeriod = $xmlReader->value;
                    }
                    break;
                case 'TopicName':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $topicName = $xmlReader->value;
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
                case 'LoggingEnabled':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $loggingEnabled = $xmlReader->value;
                        if ('True' == $loggingEnabled) {
                            $loggingEnabled = true;
                        } else {
                            $loggingEnabled = false;
                        }
                    }
                    break;
                }
            }
        }

        $attributes = new TopicAttributes(
            $maximumMessageSize,
            $messageRetentionPeriod,
            $topicName,
            $createTime,
            $lastModifyTime,
            $loggingEnabled);

        return $attributes;
    }
}
