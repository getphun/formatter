<?php
/**
 * Embed formatter
 * @package formatter
 * @version 0.0.1
 * @upgrade true
 */

namespace Formatter\Object;

class Embed implements \JsonSerializable
{
    protected $_value;
    protected $_url;
    protected $_html;
    
    private function _parseEmbed(){
        $this->_url = '';
        $this->_html = $this->_value;
        
        $regexs = [
            '/youtu\.be\/([\w\-.]+)/'                           => ['<iframe width="560" height="314" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'https://www.youtube.com/embed/$1?rel=0'],
            '/youtube\.com(.+)v=([^&]+)/'                       => ['<iframe width="560" height="314" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'https://www.youtube.com/embed/$2?rel=0'],
            '/youtube.com\/embed\/([a-z0-9\-_]+)/i'             => ['<iframe width="560" height="314" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'https://www.youtube.com/embed/$1?rel=0'],
            '/youtube-nocookie.com\/embed\/([a-z0-9\-_]+)/i'    => ['<iframe width="560" height="314" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'https://www.youtube.com/embed/$1?rel=0'],
            '/vimeo\.com\/([0-9]+)/'                            => ['<iframe width="425" height="350" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'https://player.vimeo.com/video/$1?title=0&amp;byline=0&amp;portrait=0&color=e3a01b'],
            '/vimeo\.com\/(.*)\/([0-9]+)/'                      => ['<iframe width="425" height="350" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'https://player.vimeo.com/video/$2?title=0&amp;byline=0&amp;portrait=0&color=e3a01b'],
            '/dailymotion.com\/embed\/video\/([^_]+)/'          => ['<iframe width="480" height="270" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'https://www.dailymotion.com/embed/video/$1'],
            '/dailymotion.com\/video\/([^_]+)/'                 => ['<iframe width="480" height="270" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'https://www.dailymotion.com/embed/video/$1'],
            '/vidio.com\/watch\/([\w\-]+)/'                     => ['<iframe width="480" height="270" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'https://www.vidio.com/embed/$1?player_only=true'],
            '/vidio.com\/embed\/([\w\-]+)/'                     => ['<iframe width="480" height="270" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'https://www.vidio.com/embed/$1?player_only=true'],
            '/liveleak.com\/ll_embed\?f=([\w\-]+)/'             => ['<iframe width="640" height="360" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'http://www.liveleak.com/ll_embed?f=$1'],
            '/dailymail.co.uk\/video\/([\w]+)\/video-([0-9]+)/' => ['<iframe width="698" height="573" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'http://www.dailymail.co.uk/embed/video/$2.html'],
            '/dailymail.co.uk\/([\w]+)\/video\/([0-9]+)/'       => ['<iframe width="698" height="573" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'http://www.dailymail.co.uk/embed/video/$2.html'],
            '/vid.me\/([\w\-]+)/'                               => ['<iframe width="854" height="480" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'https://vid.me/e/$1?tools=1'],
            '/vid.me\/e\/([\w\-]+)/'                            => ['<iframe width="854" height="480" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'https://vid.me/e/$1?tools=1'],
            '/imdb.com\/video\/imdb\/([\w]+)/'                  => ['<iframe width="854" height="400" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'http://www.imdb.com/videoembed/$1'],
            '/facebook\.com\/([^\/]+)\/videos\/([^\/]+)/'       => ['<iframe width="854" height="400" allowFullscreen="1" frameborder="0" src="%s"></iframe>', 'https://www.facebook.com/video/embed?video_id=$2'],
            '/.+\.mp4$/'                                        => ['<video width="560" height="314" controls><source src="%s" type="video/mp4"></video>', '$0']
        ];
        
        foreach($regexs as $re => $args){
            if(!preg_match($re, $this->_value, $match))
                continue;
            
            $html = $args[0];
            $url  = $args[1];
            
            foreach($match as $index => $val)
                $url = str_replace('$' . $index, $val, $url);
        
            $this->_url  = $url;
            $this->_html = sprintf($html, $url);
        }
    }
    
    public function __construct($value){
        $this->_value = (string)$value;
    }
    
    public function __get($name){
        if(is_null($this->_url))
            $this->_parseEmbed();
        if($name === 'url')
            return $this->_url;
        elseif($name === 'html')
            return $this->_html;
    }
    
    public function __toString(){
        return $this->_value;
    }
    
    public function jsonSerialize(){
        return $this->_value;
    }
}