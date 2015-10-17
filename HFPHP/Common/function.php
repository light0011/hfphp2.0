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


//A函数用于实例化Action类，传入类的名称即可

function A($name){
    static $_controller = array();

    if(isset($_controller[$name]))
        return $_controller[$name];

    $class = $name.'Controller';

    //class_exists($class,[bool $autoload = true])会自动调用autoload方法，除非第二个参数填false
    if(class_exists($class)){
        $controller = new $class();
        $_controller[$name] = $controller;
        return $controller;
    } else {
        return false;
    }
}



//D函数用于实例化Model类，传入类的名称即可

function D($name){
    static $_model = array();

    if(isset($_model[$name]))
        return $_model[$name];

    $class = $name.'Model';

    //class_exists($class,[bool $autoload = true])会自动调用autoload方法，除非第二个参数填false
    if(class_exists($class)){
        $model = new $class();
        $_model[$name] = $model;
        return $model;
    } else {
        return false;
    }
}










?>