<?php

namespace AliyunMNS\Http;

use AliyunMNS\AsyncCallback;
use AliyunMNS\Config;
use AliyunMNS\Constants;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Requests\BaseRequest;
use AliyunMNS\Responses\BaseResponse;
use AliyunMNS\Responses\MnsPromise;
use AliyunMNS\Signature\Signature;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;

class HttpClient
{
    private $client;
    private $region;
    private $accountId;
    private $accessId;
    private $accessKey;
    private $securityToken;
    private $requestTimeout;
    private $connectTimeout;

    public function __construct($endPoint, $accessId,
        $accessKey, $securityToken = null, Config $config = null)
    {
        if (null == $config) {
            $config = new Config();
        }
        $this->accessId = $accessId;
        $this->accessKey = $accessKey;
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $endPoint,
            'defaults' => [
                'headers' => [
                    'Host' => $endPoint,
                ],
                'proxy' => $config->getProxy(),
                'expect' => $config->getExpectContinue(),
            ],
        ]);
        $this->requestTimeout = $config->getRequestTimeout();
        $this->connectTimeout = $config->getConnectTimeout();
        $this->securityToken = $securityToken;
        $this->endpoint = $endPoint;
        $this->parseEndpoint();
    }

    public function getRegion()
    {
        return $this->region;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    // This function is for SDK internal use
    private function parseEndpoint()
    {
        $pieces = explode('//', $this->endpoint);
        $host = end($pieces);

        $host_pieces = explode('.', $host);
        $this->accountId = $host_pieces[0];
        $region_pieces = explode('-internal', $host_pieces[2]);
        $this->region = $region_pieces[0];
    }

    private function addRequiredHeaders(BaseRequest &$request)
    {
        $body = $request->generateBody();
        $queryString = $request->generateQueryString();

        $request->setBody($body);
        $request->setQueryString($queryString);

        if (null != $body) {
            $request->setHeader(Constants::CONTENT_LENGTH, strlen($body));
        }
        $request->setHeader('Date', gmdate(Constants::GMT_DATE_FORMAT));
        if (!$request->isHeaderSet(Constants::CONTENT_TYPE)) {
            $request->setHeader(Constants::CONTENT_TYPE, 'text/xml');
        }
        $request->setHeader(Constants::MNS_VERSION_HEADER, Constants::MNS_VERSION);

        if (null != $this->securityToken) {
            $request->setHeader(Constants::SECURITY_TOKEN, $this->securityToken);
        }

        $sign = Signature::SignRequest($this->accessKey, $request);
        $request->setHeader(Constants::AUTHORIZATION,
            Constants::MNS.' '.$this->accessId.':'.$sign);
    }

    public function sendRequestAsync(BaseRequest $request,
        BaseResponse &$response, AsyncCallback $callback = null)
    {
        $promise = $this->sendRequestAsyncInternal($request, $response, $callback);

        return new MnsPromise($promise, $response);
    }

    public function sendRequest(BaseRequest $request, BaseResponse &$response)
    {
        $promise = $this->sendRequestAsync($request, $response);

        return $promise->wait();
    }

    private function sendRequestAsyncInternal(BaseRequest &$request, BaseResponse &$response, AsyncCallback $callback = null)
    {
        $this->addRequiredHeaders($request);

        $parameters = ['exceptions' => false, 'http_errors' => false];
        $queryString = $request->getQueryString();
        $body = $request->getBody();
        if (null != $queryString) {
            $parameters['query'] = $queryString;
        }
        if (null != $body) {
            $parameters['body'] = $body;
        }

        $parameters['timeout'] = $this->requestTimeout;
        $parameters['connect_timeout'] = $this->connectTimeout;

        $request = new Request(strtoupper($request->getMethod()),
            $request->getResourcePath(), $request->getHeaders());
        try {
            if (null != $callback) {
                return $this->client->sendAsync($request, $parameters)->then(
                    function ($res) use (&$response, $callback) {
                        try {
                            $response->parseResponse($res->getStatusCode(), $res->getBody());
                            $callback->onSucceed($response);
                        } catch (MnsException $e) {
                            $callback->onFailed($e);
                        }
                    }
                );
            } else {
                return $this->client->sendAsync($request, $parameters);
            }
        } catch (TransferException $e) {
            $message = $e->getMessage();
            if ($e->hasResponse()) {
                $message = $e->getResponse()->getBody();
            }
            throw new MnsException($e->getCode(), $message, $e);
        }
    }
}
