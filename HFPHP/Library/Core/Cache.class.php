<?php

/**
 *
 * 缓存管理类
 *
 */


class Cache {



    //操作句柄
    protected $handler;

    //缓存连接参数
    protected $options = array();



    /**
     * 取得缓存类实例
     *
     * @return mixed
     */
    static function getInstance($type='',$options=array()) {
        static $_instance = array();
        $uniq = $type.to_uniq_string($options);
        if(!isset($_instance[$uniq])) {
            $obj = new Cache();
            $_instance[$uniq] = $obj->connect($type,$options);
        }
        return $_instance[$uniq];
    }

    /**
     *连接缓存
     *
     * @return object
     */

    public function connect($type='', $options=array()) {
        if(empty($type)) $type = C('DATA_CACHE_TYPE');
        $class = ucfirst(strtolower(trim($type)));

        if(is_file(LIB_PATH.'Driver/Cache/'.$class.'.class.php')) {
            $path = LIB_PATH;
        }

        if(require_cache($path.'Driver/Cache/'.$class.'.class.php')) {
            $cache = new $class($options);
        } else {
            halt(L('_CACHE_TYPE_INVALID_').':'.$type);
        }
        return $cache;

    }


    /**
     * 队列缓存
     * @access protected
     * @parram string $key 队列名
     * @return mixed
     *
     */

    protected function queue($key) {
        static $_handler = array(
            'file' => array('F','F'),
        );
        $queue = isset($this->options['queue']) ? $this->options['queue'] : 'file';
        $fun = isset($_handler[$queue]) ? $_handler[$queue] : $_handler['file'];
        $queue_name = isset($this->options['queue_name']) ? $this->options['queue_name'] : 'hf_queue';
        $value = $fun[0]($queue_name);

        if(!$value) {
            $value = array();
        }

        //进列
        if(false === array_search($key,$value)) array_push($value,$key);

        if(count($value) > $this->options['length']) {
            //出列
            $key = array_shift($value);
            //删除缓存
            $this->rm($key);

        }

        return $fun[1]($queue_name,$value);


    }








}
