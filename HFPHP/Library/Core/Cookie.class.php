<?php

/*
 * cookie管理
 *
 */


class Cookie{

    //存放实例化的对象
    static private $instance;



    //公共静态方法获取实例化的对象
    static public  function getInstance() {
        //判断self::$instance 是否已经被实例化
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    //私有克隆，防止被克隆
    private function __clone() {}


    //私有构造
    private function __construct() {}


    public function set($key,$value,$expire = 0,$options=array()) {

        $config = $this->getConfig($options);

        //Marks the cookie as accessible only through the HTTP protocol. This means that the cookie won't be accessible by scripting languages, such as JavaScript. This setting can effectively help to reduce identity theft through XSS attacks (although it is not supported by all browsers).
        if(!empty($config['httponly'])){
            ini_set("session.cookie_httponly", 1);
        }

        return setcookie($key, $value, $expire, $config['path'], $config['domain']);

    }

    public function get($key) {
        if(isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        }
        return false;
    }

    public function getAll() {

        return $_COOKIE;

    }

    public function del($key,$options = array()) {
        $config = $this->getConfig($options);
        return setcookie($key,"",time()-3600,$config['path'], $config['domain']);
    }

    public function clear($options = array()) {
        if(!empty($_COOKIE)) {
            foreach($_COOKIE as $key => $value) {
                $this->del($key,$options);
            }
        }
        return true;
    }

    //cookie配置
    private function getConfig($options = array()) {
        $config = array(
            'path'      =>  C('COOKIE_PATH'), // cookie 保存路径
            'domain'    =>  C('COOKIE_DOMAIN'), // cookie 有效域名
            'httponly'  =>  C('COOKIE_HTTPONLY'), // httponly设置
        );

        if(is_array($options) && !empty($options)) {
            $config = array_merge($config,array_change_key_case($options));
        }
        return $config;
    }




}










?>