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
 * interface for a class that can handle a deserialized request
 * @package Amfphp_Core_Common
 * @author Ariel Sommeria-klein
 */
namespace amf\core\common;
interface IDeserializedRequestHandler {

    /**
     * handle the deserialized request, usually by making a series of calls to a service. This should not handle exceptions, as this is done separately
     * @param mixed $deserializedRequest For amf, this is an AmfPacket
     * @param Amfphp_Core_Common_ServiceRouter $serviceRouter the service router created and configured by the gateway
     * @return mixed the response object.  For amf, this is an AmfPacket
     */
    public function handleDeserializedRequest($deserializedRequest, \amf\core\common\ServiceRouter $serviceRouter);
}