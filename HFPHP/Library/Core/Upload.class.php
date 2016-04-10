<?php
/**
 * 上传文件类
 */
namespace Core;

class Upload{


    /**
     * 上传文件配置
     * @var array
     */

    private $config = array(
        'maxSize' => 0, //上传文件大小限制(0-不做限制)
        'ext' => array(), //允许上传的文件后缀
        'rootPath' => './Uploads/', //保存根路径
        'subPath' => '', //保存子路径
        'subPathMethod' => array('date','Y-m-d'),
        'saveExt' => '', //文件保存后缀，空则使用原后缀
        'replace' => false, //存在文件同名是否覆盖
        'hash' => true, //是否生成hash编码
    );


    /**
     * 上传错误信息
     * @var string
     */
    private $error = '';

    /**
     * 构造方法，用于构造上传实例
     * @param array $config
     */
    public function __construct($config = array()) {
        /**
         * 获取配置
         */
        $this->config = array_merge($this->config,$config);

    }

    /**
     * 使用 $this->name 获取配置
     * @param $name
     * @return mixed
     */
    public function __get($name) {
        return $this->config[$name];
    }

    /**
     * 设置配置
     * @param $name
     * @param $value
     */
    public function __set($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }


    /**
     * 判断是否有该配置
     * @param $name
     * @return bool
     */
    public function __isset($name) {
        return isset($this->config[$name]);
    }

    /**
     * 获取最后一次上传的错误信息
     * @return string
     */
    public function getError() {
        return $this->error;
    }

    public function upload()
    {
        $files = $_FILES;
        if (empty($files)) {
            $this->error = '没有上传的文件';
            return false;
        }

        //检测上传根目录
        if(!$this->checkRootPath($this->rootPath)){
            return false;
        }

        //创建子目录
        if(!$this->createSubPath()) {
            return false;
        }

        //对上传文件数组信息处理







    }

    /**
     * 创建上传根目录
     * @param $rootPath
     * @return bool
     */
    public function checkRootPath($rootPath) {
        if (!(is_dir($this->$rootPath) && is_writable($this->$rootPath))) {
            $this->error = '上传根目录不存在！请尝试手动创建:' . $rootPath;
            return false;
        }
    }

    /**
     * 创建子路径
     * @return bool
     */
    public function createSubPath() {
        $sub_path_name = call_user_func_array($this->rule[0],(array)$$this->rule[1]);

        $this->sub_path = $sub_path_name.'/';

        $dir = $this->rootPath.$this->sub_path;

        if(is_dir($dir)) {
            return true;
        }

        if(mkdir($dir,0777,true)) {
            return true;
        }else{
            $this->error = '目录'.$dir.'创建失败';
            return false;
        }

    }


    private function dealFiles($files) {

    }



}



















