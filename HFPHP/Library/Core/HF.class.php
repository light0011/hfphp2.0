<?php

namespace Core;

//HFPHP引导类


class HF{

    //类映射
    private static $map = array();


    //初始化
    static public function start(){
        //注册AUTOLOAD方法
        spl_autoload_register('\Core\HF::autoload');

        //自动生成目录
        Build::checkDir();


        //加载项目下配置文件
        if(is_file(CONF_PATH.'config.php'))
            C(include CONF_PATH.'config.php');

        //运行应用
        App::run();

    }


    /**
     * 类库自动加载
     * @param string $class 对象类名
     * @return bool
     */
    static public function autoload($class){
       //检查是否存在映射
        if(isset(self::$map[$class])) {
            include self::$map[$class];
        }elseif(false !== strpos($class,'\\')) {
            $name = strstr($class,'\\',true);

            if(in_array($name,array('Core','Driver','Vendor')) || is_dir(LIB_PATH.$name)) {
                //Library目录下面的命名空间自动定位
                $path = LIB_PATH;
                $filename = $path . str_replace('\\','/',$class).EXT;
                if(is_file($filename)) {
                    //win环境下严格区分大小
                    if(IS_WIN && false === strpos(realpath($filename),$class . EXT)){
                        return;
                    }
                    include $filename;
                }
            }elseif(in_array($name,explode(',',C('MODULE_ALLOW_LIST')))) { //目前应用于A()方法
                require_cache(APP_PATH.$class.EXT);
            }
        }else{

            foreach(explode(',',C('APP_AUTOLOAD_LAYER')) as $layer) {
                if(substr($class,-strlen($layer)) == $layer) {
                    if(require_cache(MODULE_PATH.$layer.'/'.$class.EXT)) {
                        return true;
                    }
                }
            }
        }
        return true;

    }





}




?>