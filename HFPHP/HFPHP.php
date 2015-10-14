<?php

//HFPHP公共入口文件


//URL  模式定义

const URL_COMMON        =   0;   //普通模式
const URL_PATHINFO      =   1;   //PATHINFO模式


//类文件后缀

const EXT               =   '.class.php';


//常用目录定义

//框架目录
defined('HF_PATH') or define('HF_PATH',__DIR__.'/');


//框架核心类库
defined('LIB_PATH') or define('LIB_PATH',HF_PATH.'Library/');



//应用公共目录
defined('COMMON_PATH') or define('COMMON_PATH',APP_PATH.'Common/');

//应用配置目录
defined('CONF_PATH') or define('CONF_PATH',COMMON_PATH.'Conf/');

//加载运行时所需要运行的文件，并负责目录自动生成
function load_runtime_file(){
    //加载框架基础函数库
    require HF_PATH.'Common/function.php';

    //加载系统核心类文件
    require_cache(LIB_PATH.'HF.class.php');

    //加载底层配置文件
    C(include HF_PATH.'Conf/conf.php');

}




load_runtime_file();

//应用初始化
HF::start();








?>