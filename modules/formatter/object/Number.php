<?php
/**
 * Number formatter
 * @package formatter
 * @version 0.0.1
 * @upgrade true
 */

namespace Formatter\Object;

class Number implements \JsonSerializable
{
    protected $_value;
    
    public function __construct($value){
        $this->_value = (int)$value;
    }
    
    public function __get($name){
        if($name === 'value')
            return $this->_value;
    }
    
    public function format($decimals=0, $dec_point=',', $thousands_sep='.'){
        return number_format($this->_value, $decimals, $dec_point, $thousands_sep);
    }
    
    public function __toString(){
        return (string)$this->_value;
    }
    
    public function jsonSerialize(){
        return (int)$this->_value;
    }
}