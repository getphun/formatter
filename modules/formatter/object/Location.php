<?php
/**
 * Location formatter
 * @package formatter
 * @version 0.0.1
 * @upgrade true
 */

namespace Formatter\Object;

class Location implements \JsonSerializable
{
    protected $_value;
    protected $_html;
    protected $_longitude;
    protected $_latitude;
    protected $_name;
    protected $_template = '<iframe width="560" height="314" allowFullscreen="1" frameborder="0" src="//www.google.com/maps/embed/v1/place?q=%s&zoom=13&key=%s"></iframe>';
    
    private function _parseLocation(){
        $this->_html = '';
        $this->_name = $this->_value;
        $vals = explode(',', $this->_value);
        if(count($vals) === 2){
            $this->_longitude = trim($vals[1]);
            $this->_latitude  = trim($vals[0]);
            $q = $this->_latitude . ',' . $this->_longitude;
        }else{
            $q = urlencode($this->_value);
        }
        
        $google_api_key = '';
        $this->_html = sprintf($this->_template, $q, $google_api_key);
    }
    
    public function __construct($value){
        $this->_value = $value;
    }
    
    public function __get($name){
        if(is_null($this->_html))
            $this->_parseLocation();
        if($name === 'longitude')
            return $this->_longitude;
        if($name === 'latitude')
            return $this->_latitude;
        if($name === 'name')
            return $this->_name;
        if($name === 'html')
            return $this->_html;
    }
    
    public function __toString(){
        return $this->_value;
    }
    
    public function jsonSerialize(){
        return $this->_value;
    }
}