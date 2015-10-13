<?php


//HFPHP引导类


class HF{


    //初始化
    static public function start(){
        //注册AUTOLOAD方法
        spl_autoload_register('HF::autoload');

        //自动生成目录
        Build::checkDir();

        //加载项目下的每个配置文件

    }


    //类库自动加载
    static public function autoload($class){
        list($module0,$module1) = C('MODULE_ALLOW_LIST');
        if(substr($class,-5) == 'Model'){
            if(require_cache(APP_PATH.$module0.'/Model/'.$class.EXT)
                || require_cache(APP_PATH.$module1.'/Model/'.$class.EXT)
                || require_cache(LIB_PATH.$class.EXT)){
                return;
            }
        } elseif (substr($class,-6) == 'Action'){
            if(require_cache(APP_PATH.$module0.'/Action/'.$class.EXT)
                || require_cache(APP_PATH.$module1.'/Action/'.$class.EXT)
                || require_cache(LIB_PATH.$class.EXT)){
                return;
            }
        }
        require_cache(LIB_PATH.$class.EXT);
    }





}




?>