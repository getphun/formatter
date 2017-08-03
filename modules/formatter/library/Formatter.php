<?php
/**
 * General formatter
 * @package formatter
 * @version 0.0.1
 * @upgrade true
 */

use Formatter\Object\DateTime;
use Formatter\Object\Embed;
use Formatter\Object\Enum;
use Formatter\Object\Location;
use Formatter\Object\Media;
use Formatter\Object\Number;
use Formatter\Object\Text;

class Formatter {
    
    static private function apply($value, $type){
        switch($type){
        
        case 'boolean':
            $value = is_string($value) ? filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool)$value;
            $value = is_null($value) ? false : $value;
            break;
            
        case 'date':
            $value = new DateTime($value);
            break;
            
        case 'delete';
            $value = null;
            break;
            
        case 'embed':
            $value = new Embed($value);
            break;
            
        case 'location':
            $value = new Location($value);
            break;
            
        case 'media':
            if($value)
                $value = new Media($value);
            break;
            
        case 'media-list':
            $values = explode(',', $value);
            foreach($values as $index => $val){
                $val = trim($val);
                $values[$index] = new Media($val);
            }
            $value = $values;
            break;
            
        case 'number':
            $value = new Number($value);
            break;
            
        case 'text':
            $value = new Text($value);
            break;
            
        }
        
        return $value;
    }
    
    static function format($name, $object, $fetch=false){
        $objects = Formatter::formatMany($name, [$object], false, $fetch);
        return $objects[0];
    }
    
    /**
     * Format all objects at once
     * @param string $name The format name
     * @param array $objects List of object to format
     * @param string $arraykey The property of object to set as array key
     * @param boolean $fetch Also fetch the data from other table, and format
     *     them if `format` property exists on formatter rule.
     * @return array List of object after formatted.
     */
    static function formatMany($name, $objects, $arraykey=false, $fetch=false){
        $formatter_config = Phun::$config['formatter'] ?? [];
        if(!isset($formatter_config[$name]))
            throw new \Exception('Formatter named `' . $name . '` not registered.');
            
        $rules = $formatter_config[$name];
        
        $field_objects = [];
        $new_fetch = [];
        $object_ids = null;
        $object_field_objects = [];
        
        if($fetch){
            foreach($rules as $field => $args){
                if(is_string($args))
                    continue;
                
                if(!in_array($args['type'], ['partial', 'object', 'multiple-object', 'chain']))
                    continue;
                
                $process = false;
                if(is_bool($fetch)){
                    $process = true;
                    $new_fetch[$field] = false;
                }elseif(is_array($fetch)){
                    if(isset($fetch[$field])){
                        $process = true;
                        $new_fetch[$field] = $fetch[$field];
                    }elseif(in_array($field, $fetch)){
                        $process = true;
                        $new_fetch[$field] = false;
                    }
                }
                
                if(!$process)
                    continue;
                
                $field_objects[$field] = $args;
                $field_objects[$field]['ids'] = [];
            }
            
            foreach($field_objects as $field => $args){
                foreach($objects as $object){
                    if($object->$field){
                        if($args['type'] === 'object')
                            $args['ids'][] = $object->$field;
                        elseif($args['type'] == 'multiple-object')
                            $args['ids'] = array_merge($args['ids'], explode($args['separator'], $object->$field));
                    }
                }
                if($args['ids'])
                    $args['ids'] = array_values(array_unique($args['ids']));
                $field_objects[$field] = $args;
            }
            
            foreach($field_objects as $field => $args){
                $objs = [];
                
                if($args['type'] === 'chain'){
                    if(is_null($object_ids))
                        $object_ids = array_column($objects, 'id');
                    
                    $model = $args['model'];
                    if(!autoload_class_exists($model))
                        continue;
                    $chains= $args['chain'];
                    $chain_model  = $chains['model'];
                    $chain_object = $chains['object'];
                    $chain_parent = $chains['parent'];
                    
                    $obj_chains = $chain_model::get([
                        $chain_object . ' IN :ids',
                        'bind' => [
                            'ids' => $object_ids
                        ]
                    ]);
                    
                    if(!$obj_chains)
                        continue;
                    
                    $objs_ids = array_column($obj_chains, $chain_parent);
                    $objs_ids = array_values(array_unique($objs_ids));
                    
                    $objs = $model::get([
                        'id IN :ids',
                        'bind' => [
                            'ids' => $objs_ids
                        ]
                    ]);
                    
                    if(!$objs)
                        continue;
                    
                    if(isset($args['format']))
                        $objs = self::formatMany($args['format'], $objs, 'id', $new_fetch[$field]);
                    else
                        $objs = prop_as_key($objs, 'id');
                    
                    if(isset($args['field'])){
                        $args_type = isset($args['field']['type']) ? $args['field']['type'] : false;
                        foreach($objs as $index => $obj){
                            $obj = $obj->{$args['field']['name']};
                            $objs[$index] = $obj;
                            if($args_type)
                                $objs[$index] = self::apply($obj, $args_type);
                        }
                    }
                    
                    $used_chains = [];
                    foreach($obj_chains as $chain){
                        if(!isset($objs[$chain->$chain_parent]))
                            continue;
                        
                        $chain->$chain_parent = $objs[$chain->$chain_parent];
                        if(!isset($used_chains[$chain->$chain_object]))
                            $used_chains[$chain->$chain_object] = [];
                        $used_chains[$chain->$chain_object][] = $chain->$chain_parent;
                    }
                    
                    $object_field_objects[$field] = $used_chains;
                    
                }elseif($args['type'] === 'object' || $args['type'] === 'multiple-object'){
                    if(!$args['ids'])
                        continue;
                    
                    $model = $args['model'];
                    if(!autoload_class_exists($model))
                        continue;
                    $objs  = $model::get([
                        'id IN :ids',
                        'bind' => [
                            'ids' => $args['ids']
                        ]
                    ]);
                    
                    if(!$objs)
                        continue;
                    
                    if(isset($args['format']))
                        $objs = self::formatMany($args['format'], $objs, 'id', $new_fetch[$field]);
                    else
                        $objs = prop_as_key($objs, 'id');
                    
                    if(isset($args['field'])){
                        $args_type = isset($args['field']['type']) ? $args['field']['type'] : false;
                        foreach($objs as $index => $obj){
                            $obj = $obj->{$args['field']['name']};
                            $objs[$index] = $obj;
                            if($args_type)
                                $objs[$index] = self::apply($obj, $args_type);
                        }
                    }
                    
                    $object_field_objects[$field] = $objs;
                    
                }elseif($args['type'] === 'partial'){
                    if(is_null($object_ids))
                        $object_ids = array_column($objects, 'id');
                    
                    $model = $args['model'];
                    if(!autoload_class_exists($model))
                        continue;
                    $prop  = $args['object'];
                    $objs  = $model::get([
                        $prop . ' IN :ids',
                        'bind' => [
                            'ids' => $object_ids
                        ]
                    ]);
                    
                    if(!$objs)
                        continue;
                    
                    if(isset($args['format']))
                        $objs = self::formatMany($args['format'], $objs, $prop, $new_fetch[$field]);
                    else
                        $objs = prop_as_key($objs, $prop);
                    
                    if(isset($args['field'])){
                        $args_type = isset($args['field']['type']) ? $args['field']['type'] : false;
                        foreach($objs as $index => $obj){
                            $obj = $obj->{$args['field']['name']};
                            $objs[$index] = $obj;
                            if($args_type)
                                $objs[$index] = self::apply($obj, $args_type);
                        }
                    }
                    
                    $object_field_objects[$field] = $objs;
                }
            }
        }
        
        $result = [];
        
        foreach($objects as $object){
            $obj_id = $object->id;
            foreach($rules as $field => $args){
                if(is_string($args)){
                    if($args === 'delete')
                        unset($object->$field);
                    else
                        $object->$field = self::apply($object->$field, $args);
                }else{
                    switch($args['type']){
                    
                    case 'chain':
                        if($fetch && isset($object_field_objects[$field][$obj_id])){
                            $object->$field = $object_field_objects[$field][$obj_id];
                        }else{
                            $object->$field = [];
                        }
                        break;
                        
                    case 'enum':
                        $form = $args['form'];
                        $options = Phun::$config['form'][$form['name']][$form['field']]['options'];
                        $object->$field = new Enum($options, $object->$field);
                        break;
                        
                    case 'join':
                        $jflds = $args['fields'];
                        $value = '';
                        foreach($jflds as $fld){
                            if(substr($fld,0,1) === '@'){
                                $value.= $object->{(substr($fld,1))};
                            }else{
                                $value.= $fld;
                            }
                        }
                        $object->$field = $value;
                        break;
                    
                    case 'multiple-object':
                        $obj_field = $object->$field;
                        $obj_field_ids = explode($args['separator'], $obj_field);
                        if($fetch){
                            $obj_values = [];
                            foreach($obj_field_ids as $obj_field_id){
                                if(isset($object_field_objects[$field][$obj_field_id]))
                                    $obj_values[] = $object_field_objects[$field][$obj_field_id];
                            }
                            $object->$field = $obj_values;
                        }
                        break;
                        
                    case 'object':
                        $obj_field = $object->$field;
                        if($fetch && isset($object_field_objects[$field][$obj_field])){
                            $object->$field = $object_field_objects[$field][$obj_field];
                        }
                        break;
                    
                    case 'partial':
                        if(isset($object_field_objects[$field][$obj_id])){
                            $object->$field = $object_field_objects[$field][$obj_id];
                        }else{
                            $object->$field = null;
                        }
                        break;
                        
                    case 'router':
                        $jparams = $args['params'];
                        $jparams = array_merge((array)$object, $jparams);
                        $object->$field = Phun::$dispatcher->router->to($jparams['for'], $jparams);
                        break;
                    
                    }
                }
            }
            
            if(!$arraykey)
                $result[] = $object;
            else
                $result[$object->$arraykey] = $object;
        }
        
        return $result;
    }
}