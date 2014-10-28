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
 * amf byte arrays will be converted to and from this class
 *
 * @package Amfphp_Core_Amf_Types
 * @author Ariel Sommeria-klein
 */
namespace amf\core\amf\types;
class ByteArray {

    /**
     * data
     * @var string 
     */
    public $data;

    /**
     * constructor
     * @param string $data
     */
    public function __construct($data) {
        $this->data = $data;
    }

}