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
    
    protected $height;
    protected $html;
    protected $size;
    protected $url;
    protected $vendor;
    protected $width;
    
    protected $mime;
    protected $user;
    
    private function _parseEmbed(){
        $tmpls = [
            'dailymail' => 
                  '<iframe '
                .   'allowFullscreen="1" '
                .   'frameborder="0" '
                .   'height="${height}" '
                .   'scrolling="auto" '
                .   'src="${url}" '
                .   'width="${width}">'
                . '</iframe>',
            
            'dailymotion' => 
                  '<iframe '
                .   'allowFullscreen="1" '
                .   'frameborder="0" '
                .   'height="${height}" '
                .   'scrolling="no" '
                .   'src="${url}" '
                .   'width="${width}">'
                . '</iframe>',
            
            'facebook' =>
                  '<div '
                .   'class="fb-video" '
                .   'data-allowfullscreen="true" '
                .   'data-href="${url}" '
                .   'data-show-text="false" '
                .   'data-size="${size}" '
                .   'data-width="auto">'
                . '</div>',
            
            'googleplus' => 
                  '<div '
                .   'class="g-post" '
                .   'data-href="${url}">'
                . '</div>',
            
            'imdb' => 
                  '<iframe '
                .   'allowFullscreen="1" '
                .   'frameborder="0" '
                .   'height="${height}" '
                .   'scrolling="no" '
                .   'src="${url}" '
                .   'width="${width}">'
                . '</iframe>',
            
            'instagram' => 
                  '<blockquote '
                .   'class="instagram-media" '
                .   'data-instgrm-captioned '
                .   'data-instgrm-version="7">'
                .     '<a '
                .       'href="${url}" '
                .       'target="_blank">'
                .     '</a>'
                . '</blockquote>',
            
            'liveleak' => 
                  '<iframe '
                .   'allowFullscreen="1" '
                .   'frameborder="0" '
                .   'height="${height}" '
                .   'scrolling="no" '
                .   'src="${url}" '
                .   'width="${width}">'
                . '</iframe>',
            
            'twitter' =>
                  '<blockquote class="twitter-tweet">'
                .   '<a href="${url}"></a>'
                . '</blockquote>',
            
            'vidio' => 
                  '<iframe '
                .   'allowFullscreen="1" '
                .   'frameborder="0" '
                .   'height="${height}" '
                .   'scrolling="no" '
                .   'src="${url}" '
                .   'width="${width}">'
                . '</iframe>',
            
            'vidme' => 
                  '<iframe '
                .   'allowFullscreen="1" '
                .   'frameborder="0" '
                .   'height="${height}" '
                .   'scrolling="no" '
                .   'src="${url}" '
                .   'width="${width}">'
                . '</iframe>',
            
            'vimeo' => 
                  '<iframe '
                .   'allowFullscreen="1" '
                .   'frameborder="0" '
                .   'height="${height}" '
                .   'scrolling="no" '
                .   'src="${url}" '
                .   'width="${width}">'
                . '</iframe>',
            
            'youtube' => 
                  '<iframe '
                .   'allowFullscreen="1" '
                .   'frameborder="0" '
                .   'height="${height}" '
                .   'scrolling="no" '
                .   'src="${url}" '
                .   'width="${width}">'
                . '</iframe>',
            
            'videoplayer' => 
                  '<video '
                .   'width="${width}" '
                .   'height="${height}" '
                .   'controls>'
                .     '<source src="${url}" type="${mime}">'
                . '</video>'
        ];
        
        $urls = [
            'dailymail'     => 'http://www.dailymail.co.uk/embed/video/${id}.html',
            'dailymotion'   => 'https://www.dailymotion.com/embed/video/${id}',
            'facebook'      => 'https://www.facebook.com/${user}/videos/${id}',
            'googleplus'    => 'https://plus.google.com/${user}/posts/${id}',
            'imdb'          => 'http://www.imdb.com/videoembed/${id}',
            'instagram'     => 'https://www.instagram.com/p/${id}',
            'liveleak'      => 'http://www.liveleak.com/ll_embed?f=${id}',
            'twitter'       => 'https://twitter.com/${user}/status/${id}',
            'videoplayer'   => '${url}',
            'vidio'         => 'https://www.vidio.com/embed/${id}?player_only=true&autoplay=false',
            'vidme'         => 'https://vid.me/e/${id}?tools=1',
            'vimeo'         => 'https://player.vimeo.com/video/${id}?title=0&amp;byline=0&amp;portrait=0&color=e3a01b',
            'youtube'       => 'https://www.youtube.com/embed/${id}?rel=0'
        ];
        
        $regexs = [
            '/youtube.com\/embed\/([\w_\-]+)/i'                 => [ 'youtube',     ['id'=>1]               ],
            '/youtube\.com(.+)v=([\w_\-]+)/'                    => [ 'youtube',     ['id'=>2]               ],
            '/facebook\.com\/([^\/]+)\/videos\/([^\/]+)/'       => [ 'facebook',    ['user'=>1,'id'=>2]     ],
            '/facebook\.com\/.+facebook\.com%2F([^%]+)%2Fvideos%2F([0-9]+)/'
                                                                => [ 'facebook',    ['user'=>1,'id'=>2]     ],
            '/youtu\.be\/([\w_\-]+)/'                           => [ 'youtube',     ['id'=>1]               ],
            '/twitter.com\/([^\/]+)\/status\/([0-9]+)/'         => [ 'twitter',     ['user'=>1, 'id'=>2]    ],
            '/plus\.google\.com\/([0-9]+)\/posts\/([\w]+)/'     => [ 'googleplus',  ['user'=>1, 'id'=>2]    ],
            '/^.+\.(mp4|mpeg|ogg|webm)$/i'                      => [ 'videoplayer', ['url'=>0, 'mime'=>1]   ],
        
            '/vidio.com\/embed\/([\w\-]+)/'                     => [ 'vidio',       ['id'=>1]               ],
            '/vidio.com\/watch\/([\w\-]+)/'                     => [ 'vidio',       ['id'=>1]               ],
            '/instagram\.com\/p\/(\w+)/'                        => [ 'instagram',   ['id'=>1]               ],
            '/dailymail.co.uk\/video\/([\w]+)\/video-([0-9]+)/' => [ 'dailymail',   ['id'=>2]               ],
            '/dailymail.co.uk\/([\w]+)\/video\/([0-9]+)/'       => [ 'dailymail',   ['id'=>2]               ],
            '/dailymotion.com\/embed\/video\/([a-z0-9]+)/'      => [ 'dailymotion', ['id'=>1]               ],
            '/dailymotion.com\/video\/([a-z0-9]+)/'             => [ 'dailymotion', ['id'=>1]               ],
            '/dailymotion.com\/.+#video=([a-z0-9]+)/'           => [ 'dailymotion', ['id'=>1]               ],
            '/dai\.ly\/([a-z0-9]+)/'                            => [ 'dailymotion', ['id'=>1]               ],
            '/imdb\.com\/videoembed\/([\w]+)/'                  => [ 'imdb',        ['id'=>1]               ],
            '/imdb\.com\/videoplayer\/([\w]+)/'                 => [ 'imdb',        ['id'=>1]               ],
            '/imdb\.com\/.*\/videoplayer\/([\w]+)/'             => [ 'imdb',        ['id'=>1]               ],
            '/imdb\.com\/video\/imdb\/([\w]+)/'                 => [ 'imdb',        ['id'=>1]               ],
            '/liveleak.com\/ll_embed\?f=([\w\-]+)/'             => [ 'liveleak',    ['id'=>1]               ],
            '/vid.me\/e\/([\w\-]+)/'                            => [ 'vidme',       ['id'=>1]               ],
            '/vid.me\/([\w\-]+)/'                               => [ 'vidme',       ['id'=>1]               ],
            '/vimeo\.com\/([0-9]+)/'                            => [ 'vimeo',       ['id'=>1]               ],
            '/vimeo\.com\/(.*)\/([0-9]+)/'                      => [ 'vimeo',       ['id'=>2]               ],
            '/youtube-nocookie.com\/embed\/([\w_\-]+)/i'        => [ 'youtube',     ['id'=>1]               ],
            
        ];
        
        $sizes = [
            'dailymail'     => [ 'width' => 698, 'height' => 573 ],
            'dailymotion'   => [ 'width' => 480, 'height' => 270 ],
            'imdb'          => [ 'width' => 854, 'height' => 650 ],
            'instagram'     => [ 'width' => 320, 'height' => 320 ],
            'facebook'      => [ 'width' => 854, 'height' => 400 ],
            'googleplus'    => [ 'width' => 560, 'height' => 314 ],
            'liveleak'      => [ 'width' => 640, 'height' => 360 ],
            'twitter'       => [ 'width' => 560, 'height' => 314 ],
            'videoplayer'   => [ 'width' => 560, 'height' => 314 ],
            'vidio'         => [ 'width' => 480, 'height' => 270 ],
            'vidme'         => [ 'width' => 854, 'height' => 480 ],
            'vimeo'         => [ 'width' => 560, 'height' => 314 ],
            'youtube'       => [ 'width' => 560, 'height' => 314 ],
        ];
        
        $props = ['width', 'height', 'user', 'id', 'url', 'vendor', 'size', 'mime'];
        
        $this->url = trim($this->_value);
        $this->html= $this->url;
        
        if(!$this->url)
            return;
        
        $match = null;
        foreach($regexs as $re => $rule){
            if(preg_match($re, $this->url, $match))
                break;
        }
        
        if(!$match)
            return;
        
        $this->vendor = $rule[0];
        foreach($rule[1] as $prop => $index)
            $this->$prop = $match[$index];
        
        $url = $urls[$this->vendor];
        foreach($props as $attr)
            $url = str_replace('${'.$attr.'}', $this->$attr, $url);
        $this->url = $url;
        
        if(preg_match('!width ?[:|=][ "]?([0-9]+)!', $this->html, $match))
            $this->width = $match[1] ? $match[1] : null;
        if(preg_match('!height ?[:|=][ "]?([0-9]+)!', $this->html, $match))
            $this->height = $match[1] ? $match[1] : null;
        
        if(!$this->width && !$this->height){
            $this->width  = $sizes[$this->vendor]['width'];
            $this->height = $sizes[$this->vendor]['height'];
        
        }elseif(!$this->width && $this->height){
            $this->width  = floor($this->height * $sizes[$this->vendor]['width'] / $sizes[$this->vendor]['height'] );
            
        }elseif($this->width && !$this->height){
            $this->height = floor($this->width * $sizes[$this->vendor]['height'] / $sizes[$this->vendor]['width'] );
            
        }
        
        $this->size = $this->width . 'x' . $this->height;
        if($this->mime)
            $this->mime = 'video/'.$this->mime;
        
        $html = $tmpls[$this->vendor];
        foreach($props as $attr)
            $html = str_replace('${'.$attr.'}', $this->$attr, $html);
        $this->html = $html;
    }
    
    public function __construct($value){
        $this->_value = (string)$value;
    }
    
    public function __get($name){
        if(is_null($this->url))
            $this->_parseEmbed();
        if(property_exists($this, $name))
            return $this->$name;
        return null;
    }
    
    public function __toString(){
        return $this->_value;
    }
    
    public function jsonSerialize(){
        return $this->_value;
    }
}