<?php

namespace AliyunMNS\Common;

class XMLParser
{
    /**
     * Most of the error responses are in same format.
     */
    public static function parseNormalError(\XMLReader $xmlReader)
    {
        $result = ['Code' => null, 'Message' => null, 'RequestId' => null, 'HostId' => null];
        while ($xmlReader->Read()) {
            if (\XMLReader::ELEMENT == $xmlReader->nodeType) {
                switch ($xmlReader->name) {
                case 'Code':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $result['Code'] = $xmlReader->value;
                    }
                    break;
                case 'Message':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $result['Message'] = $xmlReader->value;
                    }
                    break;
                case 'RequestId':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $result['RequestId'] = $xmlReader->value;
                    }
                    break;
                case 'HostId':
                    $xmlReader->read();
                    if (\XMLReader::TEXT == $xmlReader->nodeType) {
                        $result['HostId'] = $xmlReader->value;
                    }
                    break;
                }
            }
        }

        return $result;
    }
}
