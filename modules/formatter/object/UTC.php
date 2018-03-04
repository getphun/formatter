<?php
/**
 * UTC Object
 * @package formatter
 * @version 0.0.1
 * @upgrade true
 */

namespace Formatter\Object;

class UTC implements \JsonSerializable
{
    protected $_value;
    protected $_time;
    protected $_date;
    
    public function __construct($date){
        $this->_value = (string)$date;
        $this->_time  = strtotime($this->_value . ' UTC');
        $this->_date  = new \DateTime($this->_value, new \DateTimeZone('UTC'));
        $user_timezone = date_default_timezone_get();
        $this->_date->setTimezone(new \DateTimeZone($user_timezone));
    }
    
    public function __toString(){
        return $this->_date->format('c');
    }
    
    public function __get($name){
        if($name === 'value')
            return $this->_value;
        elseif($name === 'time')
            return $this->_time;
    }
    
    public function jsonSerialize(){
        return $this->__toString();
    }
    
    public function format($format){
        return $this->_date->format($format);
    }
}