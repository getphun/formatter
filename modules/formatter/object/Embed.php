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
    protected $vendor;
    protected $html;
    protected $width;
    protected $url;
        
    private function _parseEmbed(){
        $sizes = [
            'dailymail'     => [ 'width' => 698, 'height' => 573 ],
            'dailymotion'   => [ 'width' => 480, 'height' => 270 ],
            'imdb'          => [ 'width' => 854, 'height' => 400 ],
            'facebook'      => [ 'width' => 854, 'height' => 400 ],
            'liveleak'      => [ 'width' => 640, 'height' => 360 ],
            'vidio'         => [ 'width' => 480, 'height' => 270 ],
            'vidme'         => [ 'width' => 854, 'height' => 480 ],
            'vimeo'         => [ 'width' => 425, 'height' => 350 ],
            'youtube'       => [ 'width' => 560, 'height' => 314 ],
        ];
        
        $vendors = [
            'dailymail.co.uk'       => 'dailymail',
            'dailymotion.com'       => 'dailymotion',
            'facebook.com'          => 'facebook',
            'imdb.com'              => 'imdb',
            'liveleak.com'          => 'liveleak',
            'vidio.com'             => 'vidio',
            'vimeo.com'             => 'vimeo',
            'player.vimeo.com'      => 'vimeo',
            'youtu.be'              => 'youtube',
            'youtube.com'           => 'youtube',
            'youtube-nocookie.com'  => 'youtube',
            'vid.me'                => 'vidme'
        ];
        
        $re_ids = [
            '/youtube.com\/embed\/([\w_\-]+)/i'                 => 1,
            '/youtube\.com(.+)v=([\w_\-]+)/'                    => 2,
            '/facebook\.com\/([^\/]+)\/videos\/([^\/]+)/'       => 2,
            '/vidio.com\/embed\/([\w\-]+)/'                     => 1,
            
            '/dailymail.co.uk\/video\/([\w]+)\/video-([0-9]+)/' => 2,
            '/dailymail.co.uk\/([\w]+)\/video\/([0-9]+)/'       => 2,
            '/dailymotion.com\/video\/([^_]+)/'                 => 1,
            '/dailymotion.com\/embed\/video\/([^_]+)/'          => 1,
            '/imdb\.com\/videoembed\/([\w]+)/'                  => 1,
            '/imdb\.com\/video\/imdb\/([\w]+)/'                 => 1,
            '/imdb\.com\/list\/[^\/]+\/videoplayer\/([\w]+)/'   => 1,
            '/liveleak.com\/ll_embed\?f=([\w\-]+)/'             => 1,
            '/vid.me\/([\w\-]+)/'                               => 1,
            '/vid.me\/e\/([\w\-]+)/'                            => 1,
            '/vidio.com\/watch\/([\w\-]+)/'                     => 1,
            '/vimeo\.com\/([0-9]+)/'                            => 1,
            '/vimeo\.com\/(.*)\/([0-9]+)/'                      => 2,
            '/youtu\.be\/([\w_\-]+)/'                           => 1,
            '/youtube-nocookie.com\/embed\/([\w_\-]+)/i'        => 1
        ];
        
        $templates = [
            'dailymail'     => '<iframe width="${width}" height="${height}" allowFullscreen="1" frameborder="0" scrolling="no" src="http://www.dailymail.co.uk/embed/video/${id}.html"></iframe>',
            'dailymotion'   => '<iframe width="${width}" height="${height}" allowFullscreen="1" frameborder="0" src="https://www.dailymotion.com/embed/video/${id}"></iframe>',
            'facebook'      => '<div class="fb-video" data-href="${url}" data-width="auto" data-show-text="false" data-allowfullscreen="true"></div>',
            'imdb'          => '<iframe width="${width}" height="${height}" allowFullscreen="1" frameborder="0" src="http://www.imdb.com/videoembed/${id}"></iframe>',
            'liveleak'      => '<iframe width="${width}" height="${height}" allowFullscreen="1" frameborder="0" src="http://www.liveleak.com/ll_embed?f=${id}"></iframe>',
            'vidio'         => '<iframe width="${width}" height="${height}" allowFullscreen="1" frameborder="0" src="https://www.vidio.com/embed/${id}?player_only=true&autoplay=false"></iframe>',
            'vidme'         => '<iframe width="${width}" height="${height}" allowFullscreen="1" frameborder="0" src="https://vid.me/e/${id}?tools=1"></iframe>',
            'vimeo'         => '<iframe width="${width}" height="${height}" allowFullscreen="1" frameborder="0" src="https://player.vimeo.com/video/${id}?title=0&amp;byline=0&amp;portrait=0&color=e3a01b"></iframe>',
            'youtube'       => '<iframe width="${width}" height="${height}" allowFullscreen="1" frameborder="0" src="https://www.youtube.com/embed/${id}?rel=0"></iframe>'
        ];
        
        $this->url  = trim($this->_value);
        $this->html = $this->url;
        
        $val = $this->url;
        if(preg_match('!<(iframe|video)[^>]+src="([^"]+)"!', $val, $match)){
            $this->url = $match[2];
            
            if(preg_match('!width="([0-9]+)"!', $val, $match))
                $this->width = $match[1];
            if(preg_match('!height="([0-9]+)"!', $val, $match))
                $this->height = $match[1];
        }
        
        $url = parse_url($this->url);
        
        // let find the vendor
        $url['host'] = preg_replace('!^www\.!', '', $url['host']);
        $this->vendor = $vendors[$url['host']] ?? null;
        
        // facebook.com/plugins/video.php?href=.
        if($this->vendor == 'facebook' && $url['path'] == '/plugins/video.php' && $url['query']){
            parse_str($url['query'], $url_query);
            if(isset($url_query['href']))
                $this->url = $url_query['href'];
            if(isset($url_query['width']))
                $this->width = $url_query['width'];
            if(isset($url_query['height']))
                $this->height = $url_query['height'];
        }
        
        if($this->vendor){
            if(!$this->width)
                $this->width = $sizes[$this->vendor]['width'];
            if(!$this->height)
                $this->height = $sizes[$this->vendor]['height'];
        }
        
        foreach($re_ids as $re => $index){
            if(preg_match($re, $this->url, $match) && isset($match[$index])){
                $this->id = $match[$index];
                break;
            }
        }
        
        if(isset($templates[$this->vendor])){
            $template = $templates[$this->vendor];
            $looper   = ['width', 'height', 'id', 'url', 'vendor'];
            foreach($looper as $loop)
                $template = str_replace('${'.$loop.'}', $this->$loop, $template);
            $this->html = $template;
        }
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