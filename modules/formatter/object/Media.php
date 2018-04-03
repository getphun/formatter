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
    
    private function _getAbs(){
        $dis = \Phun::$dispatcher;
        return $dis->router->to('mediaGenerator', ['path'=>str_replace('/media/','',$this->_value)]);
    }
    
    private function _getSize(){
        $abs = BASEPATH . $this->_value;
        if(is_file($abs))
            list($this->_width, $this->_height) = getimagesize($abs);
        else
            $this->_width = $this->_height = 1;
    }
    
    private function _calculateFinalSize($width=null, $height=null){
        $img_width = $this->size('width');
        $img_height= $this->size('height');
        
        if(is_null($width) || is_null($height)){
            if(is_null($width) && is_null($height)){
                $width = $img_width;
                $height= $img_height;
                
            }elseif(is_null($width)){
                $width = floor($img_width * $height / $img_height);
                
            }elseif(is_null($height)){
                $height= floor($img_height * $width / $img_width);
            }
        }
        
        if($height == 'square')
            $height = $width;
        elseif($height == 'wide')
            $height = floor(($width/16)*9);
        elseif($height == '4x3')
            $height = floor(($width/4)*3);
            
        return [$width, $height];
    }
    
    public function __construct($value){
        $this->_value = $value;
    }
    
    public function __get($name){
        if($name === 'value')
            return $this->_value;
        if($name === 'abs')
            return $this->_getAbs();
        
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
        if(is_array($width)){
            $attrs = $width;
            $width = null;
            $height = null;
        }elseif(is_array($height)){
            $attrs = $height;
            $height = null;
        }
        
        $img_width = $this->size('width');
        $img_height= $this->size('height');
        
        list($width, $height) = $this->_calculateFinalSize($width, $height);
        
        $attrs['width']  = $width;
        $attrs['height'] = $height;
        
        if($width != $img_width || $height != $img_height){
            $attrs['src'] = $this->__get("_{$width}x{$height}");
        }else{
            $attrs['src'] = $this->_value;
        }
        
        $tx = '<img ';
        $tx.= array_to_attr($attrs);
        $tx.= '>';
        
        return $tx;
    }
    
    public function picture($width, $height, $attrs=[], $responsive=true, $sizes=[]){
        $dis = \Phun::$dispatcher;
        
        list($width, $height) = $this->_calculateFinalSize($width, $height);
        
        $tx = '';
        
        $picture_attrs = [
            'style' => 'width:' . $width . 'px;height:' . $height . 'px'
        ];
        
        if($responsive){
            $padding_bottom = round($height/$width*100, 2);
            $tx.= '<div style="position:relative;height:0;padding-bottom:'.$padding_bottom.'%">';
            $picture_attrs = [
                'style' => 'position:absolute;left:0;top:0;width:100%;height:100%'
            ];
        }
        
        $tx.=   '<picture '.array_to_attr($picture_attrs).'>';
        if($dis->config->media['webp'])
            $tx.=   $this->sourceWebP($width, $height);
        $tx.=       $this->img($width, $height, $attrs);
        $tx.=   '</picture>';
        
        if($responsive){
            $tx.= '</div>';
        }
        
        return $tx;
    }
    
    public function size($dir){
        if(is_null($this->_width))
            $this->_getSize();
        
        if(!in_array($dir, ['width', 'height']))
            return 0;
        
        return $this->{'_'.$dir};
    }
    
    public function sourceWebP($width=null, $height=null, $attrs=[]){
        if(is_array($width)){
            $attrs = $width;
            $width = null;
            $height = null;
        }elseif(is_array($height)){
            $attrs = $height;
            $height = null;
        }
        
        $img_width = $this->size('width');
        $img_height= $this->size('height');
        
        list($width, $height) = $this->_calculateFinalSize($width, $height);
        
        if($width != $img_width || $height != $img_height){
            $attrs['srcset'] = $this->__get("_{$width}x{$height}");
        }else{
            $attrs['srcset'] = $this->_value;
        }
        
        $attrs['srcset'].= '.webp';
        
        $attrs['type'] = 'image/webp';
        
        $tx = '<source ';
        $tx.= array_to_attr($attrs);
        $tx.= '>';
        
        return $tx;
    }
    
    
    public function jsonSerialize(){
        return $this->_value;
    }
}