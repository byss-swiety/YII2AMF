<?php

/**
 *  This file is part of amfPHP
 *
 * LICENSE
 *
 * This source file is subject to the license that is bundled
 * with this package in the file license.txt.
 */

/**
 * This is the default handler for the gateway. It's job is to handle everything that is specific to amf for the gateway.
 * 
 * @todo determine if indirection for serializer/deserializer necessary. Makes gateway code lighter, but is cumbersome 
 * @package Amfphp_Core_Amf
 * @author Ariel Sommeria-Klein
 */
namespace amf\core\amf;

use amf\core\Config;
use amf\core\Gateway;
use amf\core\FilterManager;
use amf\core\amf\Message;
use amf\core\common\IDeserializer;
use amf\core\common\IDeserializedRequestHandler;
use amf\core\common\IExceptionHandler;
use amf\core\common\ISerializer;


class Handler implements IDeserializer, IDeserializedRequestHandler, IExceptionHandler, ISerializer {
    /**
     * filter called for each amf request header, to give a plugin the chance to handle it.
     * Unless a plugin handles them, amf headers are ignored
     * Headers embedded in the serialized requests are regarded to be a amf specific, so they get their filter in amf Handler
     * @param Object $handler. null at call. Return if the plugin can handle
     * @param \amf\core\amf\Header $header the request header
     * @todo consider an interface for $handler. Maybe overkill here
     */

    const FILTER_AMF_REQUEST_HEADER_HANDLER = 'FILTER_AMF_REQUEST_HEADER_HANDLER';

    /**
     * filter called for each amf request message, to give a plugin the chance to handle it.
     * This is for the Flex Messaging plugin to be able to intercept the message and say it wants to handle it
     * @param Object $handler. null at call. Return if the plugin can handle
     * @param \amf\core\amf\Message $requestMessage the request message
     * @todo consider an interface for $handler. Maybe overkill here
     */
    const FILTER_AMF_REQUEST_MESSAGE_HANDLER = 'FILTER_AMF_REQUEST_MESSAGE_HANDLER';

    /**
     * filter called for exception handling an amf packet/message, to give a plugin the chance to handle it.
     * This is for the Flex Messaging plugin to be able to intercept the exception and say it wants to handle it
     * @param Object $handler. null at call. Return if the plugin can handle
     * @todo consider an interface for $handler. Maybe overkill here
     */
    const FILTER_AMF_EXCEPTION_HANDLER = 'FILTER_AMF_EXCEPTION_HANDLER';

    /**
     * amf specifies that an error message must be aimed at an end point. This stores the last message's response Uri to be able to give this end point
     * in case of an exception during the handling of the message. The default is '/1', because a response Uri is not always available
     * @var String
     */
    protected $lastRequestMessageResponseUri;

    /**
     * return error details
     * @see Amfphp_Core_Config::CONFIG_RETURN_ERROR_DETAILS
     * @var boolean 
     */
    protected $returnErrorDetails = true;

    /**
     * Vo Converter. 
     * @var \amf\core\common\IVoConverter 
     */
    protected $voConverter;
    /**
     * use this to manipulate the packet directly from your services. This is an advanced option, and should be used with caution!
     * @var \amf\core\amf\Packet
     */
    public static $requestPacket;

    /**
     * use this to manipulate the packet directly from your services. This is an advanced option, and should be used with caution!
     * @var \amf\core\amf\Packet
     */
    public static $responsePacket;
    
    /**
     * constructor
     * @param array $sharedConfig
     */
    public function __construct($sharedConfig) {
        $this->lastRequestMessageResponseUri = '/1';
        if (isset($sharedConfig[Config::CONFIG_RETURN_ERROR_DETAILS])) {
            $this->returnErrorDetails = $sharedConfig[Config::CONFIG_RETURN_ERROR_DETAILS];
        }
    }

    /**
     * deserialize
     * @see \amf\core\common\IDeserializer
     * @param array $getData
     * @param array $postData
     * @param string $rawPostData
     * @return string
     */
    public function deserialize(array $getData, array $postData, $rawPostData) {
        $deserializer = new \amf\core\amf\Deserializer();
        //note: this has to be done here and not in the constructor to avoid 
        //disabling scanning when it's another handler that ends up handling the request
        $this->voConverter = FilterManager::getInstance()->callFilters(Gateway::FILTER_VO_CONVERTER, null);
        if($this->voConverter){
            $this->voConverter->setScanEnabled(false);
            $deserializer->voConverter = $this->voConverter;
        }
        $requestPacket = $deserializer->deserialize($getData, $postData, $rawPostData);
        return $requestPacket;
    }

    /**
     * creates a ServiceCallParameters object from an \amf\core\amf\Message
     * supported separators in the targetUri are '/' and '.'
     * @param \amf\core\amf\Message $\amf\core\amf\Message
     * @return \amf\core\common\ServiceCallParameters
     */
    protected function getServiceCallParameters(\amf\core\amf\Message $Message) {
        $targetUri = str_replace('.', '/', $Message->targetUri);
        $split = explode('/', $targetUri);
        $ret = new \amf\core\common\ServiceCallParameters();
        $ret->methodName = array_pop($split);
        $ret->serviceName = join($split, '/');
        $ret->methodParameters = $Message->data;
        return $ret;
    }

    /**
     * process a request and generate a response.
     * throws an Exception if anything fails, so caller must encapsulate in try/catch
     *
     * @param \amf\core\amf\Message $requestMessage
     * @param \amf\core\common\ServiceRouter $serviceRouter
     * @return \amf\core\amf\Message the response Message for the request
     */
    protected function handleRequestMessage(\amf\core\amf\Message $requestMessage, \amf\core\common\ServiceRouter $serviceRouter) {
        $filterManager = FilterManager::getInstance();
        $fromFilters = $filterManager->callFilters(self::FILTER_AMF_REQUEST_MESSAGE_HANDLER, null, $requestMessage);
        if ($fromFilters) {
            $handler = $fromFilters;
            return $handler->handleRequestMessage($requestMessage, $serviceRouter);
        }

        //plugins didn't do any special handling. Assumes this is a simple \amf\core\amf\ RPC call
        $serviceCallParameters = $this->getServiceCallParameters($requestMessage);
        $ret = $serviceRouter->executeServiceCall($serviceCallParameters->serviceName, $serviceCallParameters->methodName, $serviceCallParameters->methodParameters);
        $responseMessage = new \amf\core\amf\Message();
        $responseMessage->data = $ret;
        $responseMessage->targetUri = $requestMessage->responseUri . Constants::CLIENT_SUCCESS_METHOD;
        //not specified
        $responseMessage->responseUri = 'null';
        return $responseMessage;
    }

    /**
     * handle deserialized request
     * @see \amf\core\common\IDeserializedRequestHandler
     * @param mixed $deserializedRequest
     * @param \amf\core\common\ServiceRouter $serviceRouter
     * @return mixed
     */
    public function handleDeserializedRequest($deserializedRequest, \amf\core\common\ServiceRouter $serviceRouter) {
        self::$requestPacket = $deserializedRequest;
        self::$responsePacket = new \amf\core\amf\Packet();
        $numHeaders = count(self::$requestPacket->headers);
        for ($i = 0; $i < $numHeaders; $i++) {
            $requestHeader = self::$requestPacket->headers[$i];
            //handle a header. This is a job for plugins, unless comes a header that is so fundamental that it needs to be handled by the core
            $fromFilters = FilterManager::getInstance()->callFilters(self::FILTER_AMF_REQUEST_HEADER_HANDLER, null, $requestHeader);
            if ($fromFilters) {
                $handler = $fromFilters;
                $handler->handleRequestHeader($requestHeader);
            }
        }

        $numMessages = count(self::$requestPacket->messages);

        //set amf version to the one detected in request
        self::$responsePacket->amfVersion = self::$requestPacket->amfVersion;

        //handle each message
        for ($i = 0; $i < $numMessages; $i++) {
            $requestMessage = self::$requestPacket->messages[$i];
            $this->lastRequestMessageResponseUri = $requestMessage->responseUri;
            $responseMessage = $this->handleRequestMessage($requestMessage, $serviceRouter);
            self::$responsePacket->messages[] = $responseMessage;
        }

        return self::$responsePacket;
    }

    /**
     * handle exception
     * @see \amf\core\common\IExceptionHandler
     * @param Exception $exception
     * @return \amf\core\amf\Packet
     */
    public function handleException(\amf\core\Exception $exception) {
        $errorPacket = new \amf\core\amf\Packet();
        $filterManager = FilterManager::getInstance();
        $fromFilters = $filterManager->callFilters(self::FILTER_AMF_EXCEPTION_HANDLER, null);
        if ($fromFilters) {
            $handler = $fromFilters;
            return $handler->generateErrorResponse($exception);
        }

        //no special handling by plugins. generate a simple error response with information about the exception
        $errorResponseMessage = null;
        $errorResponseMessage = new \amf\core\amf\Message();
        $errorResponseMessage->targetUri = $this->lastRequestMessageResponseUri . Constants::CLIENT_FAILURE_METHOD;
        //not specified
        $errorResponseMessage->responseUri = 'null';
        $data = new \stdClass();
        $data->faultCode = $exception->getCode();
        $data->faultString = $exception->getMessage();
        if ($this->returnErrorDetails) {
            $data->faultDetail = $exception->getTraceAsString();
            $data->rootCause = $exception;
        } else {
            $data->faultDetail = '';
        }
        $errorResponseMessage->data = $data;

        $errorPacket->messages[] = $errorResponseMessage;
        return $errorPacket;
    }

    /**
     * serialize
     * @see \amf\core\common\ISerializer
     * @param mixed $data
     * @return mixed
     */
    public function serialize($data) {

        $serializer = new \amf\core\amf\Serializer();
        $serializer->voConverter = $this->voConverter;
        return $serializer->serialize($data);
    }

}

?>
