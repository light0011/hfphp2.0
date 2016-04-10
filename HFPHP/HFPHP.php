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





//框架类库
defined('LIB_PATH') or define('LIB_PATH',HF_PATH.'Library/');

//框架核心类库
defined('CORE_PATH') or define('CORE_PATH',LIB_PATH.'Core/');

//框架各种驱动类库
defined('DRIVER_PATH') or define('DRIVER_PATH',LIB_PATH.'Driver/');

//框架供应文件夹
defined('VENDOR_PATH') or define('VENDOR_PATH',LIB_PATH.'Vendor/');


//系统运行时目录
defined('RUNTIME_PATH') or define('RUNTIME_PATH',   APP_PATH.'Runtime/');

// 应用编译目录
defined('COMPILE_PATH') or define('COMPILE_PATH',      RUNTIME_PATH.'Compile/');

// 应用模板缓存目录
defined('CACHE_PATH') or define('CACHE_PATH',     RUNTIME_PATH.'Cache/');

//应用数据目录
defined('DATA_PATH') or define('DATA_PATH',RUNTIME_PATH.'Data/');

//项目缓存目录
defined('TEMP_PATH') or define('TEMP_PATH',RUNTIME_PATH.'Temp/');

//应用公共目录
defined('COMMON_PATH') or define('COMMON_PATH',APP_PATH.'Common/');

//应用配置目录
defined('CONF_PATH') or define('CONF_PATH',COMMON_PATH.'Conf/');


//定义当前文件名
define('PHP_FILE',rtrim($_SERVER['SCRIPT_NAME'],'/'));


//判断php运行环境
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);


//加载运行时所需要运行的文件，并负责目录自动生成
function load_runtime_file(){
    //加载框架基础函数库
    require HF_PATH.'Common/function.php';

    //加载系统核心类文件
    require_cache(CORE_PATH.'HF.class.php');

    //加载smarty库
    require_cache(VENDOR_PATH.'smarty/Smarty.class.php');

    //加载底层配置文件
    C(include HF_PATH.'Conf/conf.php');

    //加载框架底层语言包
    L(include HF_PATH.'Lang/'.strtolower(C('DEFAULT_LANG')).'.php');

}




load_runtime_file();

//应用初始化
Core\HF::start();








?>