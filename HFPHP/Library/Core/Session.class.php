<?php

/*
 * session管理
 *
 */


class Session{

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

    /**
     * @param array $config
     * @return true
     */
    public function start(array $config) {
        if(C('VAR_SESSION_ID') && isset($_REQUEST[C('VAR_SESSION_ID')])){
            session_id($_REQUEST[C('VAR_SESSION_ID')]);
        }elseif(isset($config['id'])) {
            session_id($config['id']);
        }
        if(isset($config['name']))            session_name($config['name']);
        if(isset($config['path']))            session_save_path($config['path']);
        if(isset($config['domain']))          ini_set('session.cookie_domain', $config['domain']);
        if(isset($config['expire']))          {
            ini_set('session.gc_maxlifetime',   $config['expire']);
            ini_set('session.cookie_lifetime',  $config['expire']);
        }
        if(isset($config['use_trans_sid']))   ini_set('session.use_trans_sid', $config['use_trans_sid']?1:0);
        if(isset($config['use_cookies']))     ini_set('session.use_cookies', $config['use_cookies']?1:0);
        if(isset($config['cache_limiter']))   session_cache_limiter($config['cache_limiter']);
        if(isset($config['cache_expire']))    session_cache_expire($config['cache_expire']);

        // 启动session
        if(C('SESSION_AUTO_START'))  session_start();
    }

    public function set($key,$value) {
        return $_SESSION[$key] = $value;
    }

    public function get($key) {
        if(isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return false;
    }

    public function getAll() {
        if(isset($_SESSION)) {
            return $_SESSION;
        } else {
            return false;
        }
    }

    public function del($key) {
        unset($_SESSION[$key]);
        return true;
    }

    public function destroy() {
        //this does only affect the local $_SESSION variable instance but not the session data in the session storage.
        session_unset();
        //destroys the session data that is stored in the session storage (e.g. the session file in the file system).
        session_destroy();
    }






}










?>