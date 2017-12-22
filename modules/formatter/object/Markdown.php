<?php
/**
 * Markdown formatter
 * @package formatter
 * @version 0.0.1
 * @upgrade true
 */

namespace Formatter\Object;

class Markdown implements \JsonSerializable
{
    protected $_value;
    protected $_html;
    
    public function __construct($value){
        $this->_html = $this->_value = $value ?? '';
        if(module_exists('lib-markdown'))
            $this->_html = \Michelf\Markdown::defaultTransform(hs($this->_html));
        $this->_html = new \Formatter\Object\Text($this->_html);
    }
    
    public function __get($name){
        if($name === 'value')
            return $this->_value;
        
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