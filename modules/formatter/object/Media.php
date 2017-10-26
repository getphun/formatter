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
    
    protected $_width;
    protected $_height;
    
    private function _parseMedia(){
        $exts = explode('.', $this->_value);
        $this->_ext  = end($exts);
        $this->_file = preg_replace('/\.'.$this->_ext.'$/', '', $this->_value);
    }
    
    private function _getSize(){
        $abs = BASEPATH . $this->_value;
        if(is_file($abs))
            list($this->_width, $this->_height) = getimagesize($abs);
        else
            $this->_width = $this->_height = 1;
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
    
    public function img($width=null, $height=null, $attrs=[]){
        // TODO
        // get image size if $width or $height is null
        
        $attrs['width']  = $width;
        $attrs['height'] = $height;
        $attrs['src']    = $this->__get("_{$width}x{$height}");
        
        $tx = '<img ';
        $tx.= array_to_attr($attrs);
        $tx.= '>';
        
        return $tx;
    }
    
    public function size($dir){
        if(is_null($this->_width))
            $this->_getSize();
        
        if(!in_array($dir, ['width', 'height']))
            return 0;
        
        return $this->{'_'.$dir};
    }
    
    public function jsonSerialize(){
        return $this->_value;
    }
}