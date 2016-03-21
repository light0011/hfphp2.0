<?php

//App  项目执行过程管理

class App{


    //应用程序初始化
    static public function init(){
        //设置系统时区
        date_default_timezone_set(C('DEFAULT_TIMEZONE'));

        //开启缓存
         if(APP_CACHE) {
             ob_start();
         }

        //URL调度
        Dispatcher::dispatch();

        //加载分组文件
        if(defined('MODULE_NAME')){
            //加载分组配置文件
            if(is_file(APP_PATH.MODULE_NAME.'/Conf/conf.php')){
                C(include APP_PATH.MODULE_NAME.'/Conf/conf.php');
            }
            //加载分组函数文件
            if(is_file(APP_PATH.MODULE_NAME.'/Common/function.php')){
                include APP_PATH.MODULE_NAME.'/Common/function.php';
            }
        }


    }

    static public function exec(){

        //安全检测控制器名称
        if(!preg_match('/^[A-Za-z0-9_]+$/',CONTROLLER_NAME)){
            $controller = false;
        } else {

            $controller = A(CONTROLLER_NAME);
        }

        if(!$controller){
            exit('该控制器不存在！');
        }

        $action = ACTION_NAME;
        call_user_func(array($controller,$action));

    }


    //项目运行的入口
    static public function run(){

        //项目初始化
        App::init();

        //不过php执行方式不是命令行方式，开启session
        if(PHP_SAPI != 'cli'){
            Session::getInstance()->start(C('SESSION_OPTIONS'));
        }


        //项目执行
        APP::exec();

    }


}











?>