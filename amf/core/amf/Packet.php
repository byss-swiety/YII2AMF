<?php
/**
 *  This file is part of amfPHP
 *
 * LICENSE
 *
 * This source file is subject to the license that is bundled
 * with this package in the file license.txt.
 */

namespace amf\core\amf;
/**
 * content holder for an amf Packet.
 *
 * @package Amfphp_Core_Amf
 * @author Ariel Sommeria-klein
 */
class Packet {
    /**
     * The place to keep the headers data
     *
     * @var <array>
     */
    public $headers;

    /**
     * The place to keep the Message elements
     *
     * @var <array>
     */
    public $messages;

    /**
     * either 0 or 3. This is stored here when deserializing, because the serializer needs the info
     * @var <int>
     */
    public $amfVersion;


    /**
     * The constructor function for a new amf object.
     *
     * All the constructor does is initialize the headers and Messages containers
     */
    public function __construct() {
        $this->headers = array();
        $this->messages = array();
        $this->amfVersion = \amf\core\amf\Constants::AMF0_ENCODING;
    }

    

}
?>
