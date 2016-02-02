<?php


//HFPHP引导类


class HF{


    //初始化
    static public function start(){
        //注册AUTOLOAD方法
        spl_autoload_register('HF::autoload');

        //自动生成目录
        Build::checkDir();

        //加载项目下配置文件
        if(is_file(CONF_PATH.'config.php'))
            C(include CONF_PATH.'config.php');


        //运行应用
        App::run();

    }


    //类库自动加载
    static public function autoload($class){

        if(defined('MODULE_NAME')){
            $module = MODULE_NAME;
        } else {
            $module = C('DEFAULT_MODULE');
        }

        if(substr($class,-5) == 'Model'){
            if(require_cache(APP_PATH.$module.'/Model/'.$class.EXT) || require_cache(LIB_PATH.$class.EXT)){
                return;
            }
        } elseif (substr($class,-10) == 'Controller'){
            if(require_cache(APP_PATH.$module.'/Controller/'.$class.EXT) || require_cache(LIB_PATH.$class.EXT)){
                return;
            }
        }
        require_cache(LIB_PATH.$class.EXT);
    }





}




?>