<?php


//优化的require_once,判断是否为文件并且引入
function require_cache($filename){
    static $_importFiles = array();

    if(!isset($_importFiles[$filename])){
        if(is_file($filename)){
            require $filename;
            $_importFiles[$filename] = true;
        } else {
            $_importFiles[$filename] = false;
        }
    }

    return $_importFiles[$filename];
}


//获取配置值
function C($name=null,$value=null){

    static $_config = array();

    //无参数时，获取所有
    if(empty($name)) return $_config;

    //优先执行赋值或者获取
    if(is_string($name)){
        $name = strtolower($name);
        if(is_null($value))
            return isset($_config[$name]) ? $_config[$name] : null;
        $_config[$name] = $value;
    }

    //批量设置
    if(is_array($name)){
        return $_config = array_merge($_config,array_change_key_case($name));
    }

    //传入非法参数，直接返回null
    return null;

}












?>