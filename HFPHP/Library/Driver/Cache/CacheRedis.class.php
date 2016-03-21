<?php

import('HF/Driver/Cache/Abstraction');

/**
 *
 * Redis缓存类
 *
 */


class CacheRedis extends Abstraction {

    const DEFAULT_CONNECT_TIMEOUT = 1;

    private $redis = null;

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
        $this->redis = null;
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

        $ret = $this->getInstance()->set($key, $value,$expire);

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

        $ret = $this->getInstance()->del($key);

        return $ret;
    }

    /**
     * 实现缓存的mget接口
     *
     * @param array $key  包含key值的数组
     * @return bool
     */

    public function mget(array $keys) {
        $ret = $this->getInstance()->mGet($keys);
        return $ret;
    }

    /**
     * 实现缓存的mset接口
     *
     * @param array $values  包含key=>value的数组
     * @param int $expire  过期时间
     * @return bool
     */

    public function mset(array $values,$expire=60) {
        $instance = $this->getInstance();
        $ret = $instance->mSet($values);
        foreach($values as $k=>$v) {
            $instance->expire($k,$expire);
        }
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
     * 将其他方法调用均转向到封装的Redis实例
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
     * @return Redis
     * @throws \S\Exception
     */
    protected function getInstance() {
        if (!$this->redis) {
            $this->redis = new Redis();

            $this->connect();



        }
        return $this->redis;
    }

    /**
     *
     * 连接redis服务器
     *
     */

    public function connect() {
        $config = C('REDIS_CACHE_CONFIG');
        $host = $config['host'];
        $timeout = isset($config['timeout']) ? $config['timeout'] : self::DEFAULT_CONNECT_TIMEOUT;

        //如果host配置是数组，则随机选择其中一台进行服务器连接，如果失败，随机连接另外一台.
        if(is_array($host)) {
            shuffle($host);
            $conn = $this->redis->connect(array_pop($host),$config['port'],$timeout);
            if($conn === false) {
                if(count($host) >= 1) {
                    $host = array_pop($host);
                    $conn = $this->redis->connect($host,$config['port'],$timeout);
                }
            }
        } else {
            $conn = $this->redis->connect($host,$config['port'],$timeout);
        }

        if($conn === false){
            halt("redis connect $host fail");
        }


        if(isset($config['user']) && isset($config['auth'])) {
            if($this->redis->auth($config['user'].":".$config['auth']) == false) {
                halt("redis auth $host fail");
            }
        }

        if(isset($config['db']) && $config['db']) {
            $this->redis->select($config['db']);
        }

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
