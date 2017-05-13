<?php
/**
 * Media formatter
 * @package formatter
 * @version 0.0.1
 * @upgrade true
 */

namespace Formatter\Object;

class Media implements \JsonSerializable
{
    protected $_value;
    protected $_file;
    protected $_ext;
    protected $_external;
    
    private function _parseMedia(){
        $exts = explode('.', $this->_value);
        $this->_ext  = end($exts);
        $this->_file = preg_replace('/\.'.$this->_ext.'$/', '', $this->_value);
    }
    
    public function __construct($value){
        $this->_value = $value;
    }
    
    public function __get($name){
        if($name === 'value')
            return $this->_value;
        
        if(!module_exists('media'))
            return $this->_value;
        
        if(is_null($this->_external))
            $this->_external = substr($this->_value,0,4) === 'http';
        
        if(is_null($this->_ext))
            $this->_parseMedia();
        
        $ext_lower = strtolower($this->_ext);
        if($this->_external || !in_array($ext_lower, ['jpg', 'jpeg', 'png', 'bmp', 'gif']))
            return $this->_value;
        
        return $this->_file . $name . '.' . $this->_ext;
    }
    
    public function __toString(){
        return $this->_value;
    }
    
    public function jsonSerialize(){
        return $this->_value;
    }
}