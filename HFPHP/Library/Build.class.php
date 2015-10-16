<?php


//自动生成目录
class Build{

    static protected $controller   =   '<?php
class IndexController extends Controller {
    public function index(){
        echo "hello hfphp";
    }
}';


    //检测应用目录是否需要自动创建
    static public function checkDir(){
        $module = C('DEFAULT_MODULE');
        if(!is_dir(APP_PATH.$module)){
            //创建模块的目录结构
            self::buildAppDir($module);
        }
    }


    //创建模块的目录结构
    static public function buildAppDir($module){

        //没有的话，自动创建
        if(!is_dir(APP_PATH)) mkdir(APP_PATH,0777,true);

        if(is_writeable(APP_PATH)){
            $dirs = array(
                COMMON_PATH,
                COMMON_PATH.'Common/',
                CONF_PATH,
                RUNTIME_PATH,
                COMPILE_PATH,
                CACHE_PATH,
                APP_PATH.$module,'/',
                APP_PATH.$module.'/Common/',
                APP_PATH.$module.'/Controller/',
                APP_PATH.$module.'/Model/',
                APP_PATH.$module.'/Conf/',
                APP_PATH.$module.'/View/',
            );
            foreach($dirs as $dir){
                if(!is_dir($dir)) mkdir($dir,0777,true);
            }

            //写入应用配置文件
            if(!is_file(CONF_PATH.'config.php')){
                file_put_contents(CONF_PATH.'config.php',"<?php\nreturn array(\n\t//'配置项'=>'配置值'\n);\n?>");

            }

            //创建应用函数文件
            if(!is_file(APP_PATH.$module.'/Common/function.php')){
                file_put_contents(APP_PATH.$module.'/Common/function.php',"<?php\n//请在此输入此模块下的公共函数，并自动为您加载！\n?>");
            }

            //写入测试Action
            if(!is_file(APP_PATH.$module.'/'.'Controller/IndexController.class.php')){
                file_put_contents(APP_PATH.$module.'/'.'Controller/IndexController.class.php',self::$controller);
            }


        } else {
            header('Content-Type:text/html; charset=utf-8');
            exit('项目目录不可写，目录无法自动生成！<BR>请手动生成项目目录~');
        }




    }

}















?>