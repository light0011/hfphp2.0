<?php

require 'Abstraction.class.php';

/**
 *
 * memcache缓存类
 *
 */


class CacheMemcache extends Abstraction {

    private $mc = null;

    /**
     * 用于判断是否使用持久连接，如果使用，析构函数中不做close操作
     * @var bool
     */
    private $persistent    = false;





    public function __destruct(){
        if(!$this->persistent) {
            $this->close();
        }
    }



    public function close(){
        $this->getInstance()->close();
        $this->mc = null;
        return true;
    }

    /**
     * 实现缓存的get接口
     *
     * @param string $key  key值
     * @return mixed
     */
    public function get($key) {
        $ret = $this->getInstance()->get($key);
        return $ret;
    }

    /**
     * 实现缓存的set接口
     *
     * @param string $key  key值
     * @param mixed $value  value值
     * @param int $expire  过期时间
     * @return bool
     */
    public function set($key, $value, $expire = self::DEFAULT_EXPIRE) {
        if ($this->global_close_cache) {
            return true;
        }

        $ret = $this->getInstance()->set($key, $value, false, $expire);

        return $ret;
    }

    /**
     * 实现缓存的del接口
     *
     * @param string $key  key值
     * @return bool
     */
    public function del($key) {
        if ($this->global_close_cache) {
            return true;
        }

        $ret = $this->getInstance()->delete($key);

        return $ret;
    }


    /**
     * @param $key
     * @param int $value
     * @return int|false
     */
    public function incr($key, $value=1) {

        $ret = $this->getInstance()->increment($key, $value);

        return $ret;
    }

    /**
     * @param $key
     * @param int $value
     * @return int|false
     */
    public function decr($key, $value=1) {

        $ret = $this->getInstance()->decrement($key, $value);

        return $ret;
    }



    /**
     * 去掉危险操作的功能
     * @return bool
     */
    public function flush() {
        return false;
    }

    /**
     * 将其他方法调用均转向到封装的Memcached实例
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args=array()) {

        $ret = call_user_func_array(array($this->getInstance(), $name), $args);

        return $ret;
    }






    /**
     * @return Memcache
     * @throws \S\Exception
     */
    protected function getInstance() {
        if (!$this->mc) {
            $mc = new Memcache();
            $config = C('MEMCACHE_CACHE_CONFIG');

            foreach ($config as $server) {
                $server = $this->init_conf($server);
                $mc->addServer(
                    $server['host'],
                    $server['port'],
                    $server['persistent'],
                    $server['weight'],
                    $server['timeout'],
                    $server['retry_interval'],
                    $server['status'],
                    $server['failure_callback']
                );
            }
            $this->mc = $mc;
        }
        return $this->mc;
    }



    /**
     * 初始化配置信息，使用默认值完善其他为填写参数
     *
     * @see http://php.net/manual/en/memcache.addserver.php
     * @param array $config
     * @return array
     */
    protected function init_conf(array $config) {
        //Memcache::addServer(string $host [, int $port = 11211 [, bool $persistent [, int $weight [, int $timeout [, int $retry_interval [, bool $status [, callable $failure_callback [, int $timeoutms ]]]]]]]] )
        $default = array(
            'port'          => 11211,
            'persistent'    => false,//memcache中默认是true
            'weight'        => 1,
            'timeout'       => self::DEFAULT_CONNECT_TIMEOUT,
            'retry_interval'=> 15,  //memcache中默认是15s，-1为禁止
            'status'        => true,
            'failure_callback'  => array($this, 'failureCallback'),
        );
        if (isset($config['persistent']) && $config['persistent']) {
            $this->_persistent = true;
        }
        return array_merge($default, $config);
    }

    /**
     * 连接失败回调方法
     *
     * @param $host
     * @param $port
     * @throws \S\Exception
     */
    public function failureCallback($host, $port) {
        halt(__CLASS__.'::' . __METHOD__ ." Memcache {$host}:{$port} connect fail");
    }







}
