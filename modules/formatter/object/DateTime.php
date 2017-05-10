<?php
/**
 * DateTime Object
 * @package formatter
 * @version 0.0.1
 * @upgrade true
 */

namespace Formatter\Object;

class DateTime implements \JsonSerializable
{
    protected $_value;
    protected $_time;
    protected $_date;
    
    public function __construct($date){
        $this->_value = (string)$date;
        $this->_time  = strtotime($this->_value);
        $this->_date  = new \DateTime($this->_value);
    }
    
    public function __toString(){
        return $this->_value;
    }
    
    public function __get($name){
        if($name === 'value')
            return $this->_value;
        elseif($name === 'time')
            return $this->_time;
    }
    
    public function jsonSerialize(){
        return $this->_value;
    }
    
    public function format($format){
        return $this->_date->format($format);
    }
}