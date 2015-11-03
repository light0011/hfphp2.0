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


//获取和设置语言定义
function L($name=null,$value=null){

    static $lang = array();

    //空参数返回所有定义
    if(empty($name))
        return $lang;

    //判断语言是否存在，存在则返回，不存在返回大写的$name
    if(is_string($name)){
        $name = strtoupper($name);
        if(is_null($value)){

            return isset($lang[$name]) ? $lang[$name] : $name;
        }
        $lang[$name] = $value;
        return;
    }

    //批量定义
    if(is_array($name)){
        $lang = array_merge($lang,array_change_key_case($name,CASE_UPPER));
    }

    return;

}


//URL组装，支持不同模式
//格式：U('[分组/模块/操作]?参数','参数','伪静态后缀')
function U($url,$vars='',$suffix=true){
    //解析url
    $info = parse_url($url);

    $url = !empty($info['path']) ? $info['path'] : ACTION_NAME;

    //解析参数
    if(is_string($vars)){  //例如aaa=1&bbb=2 转换为数组
        parse_str($vars,$vars);
    } elseif (!is_array($vars)){
        $vars = array();
    }

    //url分割线
    $depr = C('URL_PATHINFO_DEPR');

    if($url){
        if('/' != $depr){ //安全替换
            $url = str_replace('/',$depr,$url);
        }

        //解析分组、模块和操作
        $url = trim($url,$depr);

        $path = explode($depr,$url);

        $var =array();

        $var[C('VAR_ACTION')] = !empty($path) ? array_pop($path) : ACTION_NAME;
        $var[C('VAR_CONTROLLER')] = !empty($path) ? array_pop($path) : CONTROLLER_NAME;

       if(C('MODULE_ALLOW_LIST')){
           if(!empty($path)){
               $module = array_pop($path);
               $var[C('VAR_MODULE')] = $module;
           } else {
               if(MODULE_NAME != C('DEFAULT_MODULE')){
                   $var[C('VAR_MODULE')] = MODULE_NAME;
               }
           }
       }


    }


    if(C('URL_MODEL') == 0){ //普通模式URL转换
        $url = PHP_FILE.'?'.http_build_query($var);
        if(!empty($vars)){
            $vars = http_build_query($vars);
            $url .= '&'.$vars;
        }
    } else {  //PATHINFO模式
        $url = PHP_FILE.'/'.implode($depr,array_reverse($var));


        if(!empty($vars)){  //添加参数
            $vars = http_build_query($vars);
            $url .= $depr.str_replace(array('=','&'),$depr,$vars);
        }

        if($suffix){
            $suffix = $suffix === true ? C('URL_HTML_SUFFIX') : $suffix;
            if($suffix){
                $url .= '.'.ltrim($suffix,'.');
            }
        }


    }


    return $url;


}







?>