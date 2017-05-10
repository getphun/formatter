<?php
/**
 * Text formatter
 * @package formatter
 * @version 0.0.1
 * @upgrade true
 */

namespace Formatter\Object;

class Enum implements \JsonSerializable
{
    protected $_value;
    protected $_options;
    
    public function __construct($options, $value){
        $this->_value   = $value;
        $this->_options = $options;
    }
    
    public function __get($name){
        if($name === 'value')
            return $this->_value;
        if($name === 'label')
            return $this->_options[$this->_value];
        if($name === 'options')
            return $this->_options;
    }
    
    public function __toString(){
        return (string)$this->_value;
    }
    
    public function jsonSerialize(){
        return $this->_value;
    }
}