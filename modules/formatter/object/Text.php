<?php
/**
 * Text formatter
 * @package formatter
 * @version 0.0.1
 * @upgrade true
 */

namespace Formatter\Object;

class Text implements \JsonSerializable
{
    protected $_value;
    protected $_safe;
    
    private function _safeValue(){
        $this->_safe = htmlspecialchars($this->_value, ENT_QUOTES);
    }
    
    private function _cleanValue(){
        $ctx = preg_replace('!<[^>]+>!', ' ', $this->_value);
        $ctx = preg_replace('! +!', ' ', $ctx);
        $ctx = trim(htmlspecialchars($ctx, ENT_QUOTES));
        
        $this->_clean = $ctx;
    }
    
    public function __construct($value){
        $this->_value = $value ?? '';
    }
    
    public function __get($name){
        if($name === 'value')
            return $this->_value;
        
        if($name === 'safe'){
            if(is_null($this->_safe))
                $this->_safeValue();
            return $this->_safe;
        }
        
        if($name === 'clean'){
            if(is_null($this->_clean))
                $this->_cleanValue();
            return $this->_clean;
        }
    }
    
    public function __toString(){
        return $this->_value;
    }
    
    public function jsonSerialize(){
        return $this->_value;
    }
    
    public function chars($len=20){
        if(is_null($this->_clean))
            $this->_cleanValue();
        return trim(substr($this->_clean, 0, $len), ' &');
    }
    
    public function words($len){
        if(is_null($this->_clean))
            $this->_cleanValue();
        
        $ctxs= explode(' ', $this->_clean);
        $ctx = array_slice($ctxs, 0, $len);
        return implode(' ', $ctx);
    }
}