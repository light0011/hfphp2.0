<?php

namespace Core;

//模板类,采用单例模式
class Templates{

    private static $instance;

    private $vars = array();

    //公共静态方法获取实例化的对象
    static public function getInstance(){
        //判断self::$instance是否已经被实例化
        if(!self::$instance instanceof self){
            self::$instance = new self();
        }
        return self::$instance;
    }

    //私有克隆，防止被克隆
    private function __clone(){

    }

    //私有构造方法，并判断各个目录是否存在
    private function __construct(){
        if(!is_dir(APP_PATH.MODULE_NAME.'/View/') || !is_dir(COMPILE_PATH) || !is_dir(CACHE_PATH)){
            exit('ERROR:模板目录或者编译目录或者缓存目录不存在，请手工设置!');
        }
    }


    //assign方法，用于注入变量
    public function assign($var,$value){
        if(isset($var) && !empty($var)){
            $this->vars[$var] = $value;
        } else {
            exit('ERROR:请设置模板变量！');
        }
    }

    //dispaly()方法,$status参数默认1,加载项目下模板，若为0，则为加载框架下模板
    public function display($file,$status=1){
        //根据传入值设置模板的路径
        if($status){
            if(isset($file) && !empty($file)){
                $tplFile = APP_PATH.MODULE_NAME.'/View/'.$file.'.'.C('URL_HTML_SUFFIX');
            } else {
                $tplFile = APP_PATH.MODULE_NAME.'/View/'.CONTROLLER_NAME.'/'.ACTION_NAME.'.'.C('URL_HTML_SUFFIX');
            }
        } else {
            $tplFile = $file;
        }



        //判断模板是否存在
        if(!file_exists($tplFile)){
            exit('ERROR:模板文件不存在！');
        }

        //编译文件
        $parFile = COMPILE_PATH.md5($tplFile).'.php';

        //缓存文件
        $cacheFile = CACHE_PATH.md5($tplFile).'html';

        //第二次运行相同文件的时候，直接载入缓存文件，避开编译
        if(APP_CACHE){
            //缓存文件和编译文件都要存在
            if(file_exists($cacheFile) && file_exists($parFile)){
                //判断模板文件是否修改过，判断编译文件是否修改过
                if(filemtime($parFile) >= filemtime($tplFile) && filemtime($cacheFile) >= filemtime($parFile)){
                    include $cacheFile;
                    return;
                }
            }
        }

        //当编译文件不存在，或者模板文件修改过，则生成编译文件
        if(!file_exists($parFile) || filemtime($parFile) < filemtime($tplFile)){
            $parser = new Parser($tplFile);
            $parser->compile($parFile);
        }

        //载入编译文件
        include $parFile;

        if(APP_CACHE){
            //获取缓冲区的数据，并且创建缓存文件
            fopen($cacheFile,'w');
            chmod($cacheFile,0777);
            file_put_contents($cacheFile,ob_get_contents());
            //清除缓冲区
            ob_clean();
            //载入缓存文件
            include $cacheFile;
        }


    }

    







}















?>