<?php


//完成URL解析、路由和调度

class Dispatcher{

    static public function dispatch(){

        $depr =C('URL_PATHINFO_DEPR');

        //利用$_SERVER['PATH_INFO']分析出路径所指之处
        if(!empty($_SERVER['PATH_INFO'])){

            //如果有html伪静态后缀，略去
            if(C('URL_HTML_SUFFIX')){
                $_SERVER['PATH_INFO'] = preg_replace('/\.'.trim(C('URL_HTML_SUFFIX'),'.').'$/i','',$_SERVER['PATH_INFO']);
            }

            $paths = explode($depr,trim($_SERVER['PATH_INFO'],'/'));

            $var = array();

            //获取模块
            if(C('MODULE_ALLOW_LIST') && !isset($_GET[C('VAR_MODULE')])){
                $_GET[C('VAR_MODULE')] = in_array(strtolower($paths[0]),explode(',',strtolower(C('MODULE_ALLOW_LIST')))) ? array_shift($paths) : '';
            }

            //获取控制器
            if(!isset($_GET[C('VAR_CONTROLLER')])){
                $_GET[C('VAR_CONTROLLER')] = array_shift($paths);
            }

            //获取方法
            if(!isset($_GET[C('VAR_ACTION')])){

                $_GET[C('VAR_ACTION')] = array_shift($paths);
            }

            //正则分析出剩余的URL参数，不过php的闭包怎么感觉怪怪的
            preg_replace_callback('/(\w+)\/([^\/]+)/',
                function($match) use(&$var){
                    $var[$match[1]]=strip_tags($match[2]);
                },
                implode('/',$paths)
            );

            $_GET = array_merge($var,$_GET);



        }

        //获取模块，控制器和方法
        if(C('MODULE_ALLOW_LIST')){
            define('MODULE_NAME',self::getModule(C('VAR_MODULE')));
        }

        define('CONTROLLER_NAME',self::getController(C('VAR_CONTROLLER')));

        define('ACTION_NAME',self::getAction(C('VAR_ACTION')));


    }


    //获取模块名称
    static public function getModule($var){
        $module = (!empty($_GET[$var])) ? $_GET[$var] : C('DEFAULT_MODULE');
        unset($_GET[$var]);
        return strip_tags(C('URL_CASE_INSENSITIVE') ? ucfirst(strtolower($module)) : $module);
    }


    //获取控制器名称
    static public function getController($var){
        $controller = (!empty($_GET[$var])) ? $_GET[$var] : C('DEFAULT_CONTROLLER');
        unset($_GET[$var]);
        return strip_tags(C('URL_CASE_INSENSITIVE') ? ucfirst(strtolower($controller)) : $controller);
    }


    //获取方法名称
    static public function getAction($var){
        $action = (!empty($_GET[$var])) ? $_GET[$var] : C('DEFAULT_ACTION');
        unset($_GET[$var]);
        return strip_tags(C('URL_CASE_INSENSITIVE') ? ucfirst(strtolower($action)) : $action);
    }





}













?>