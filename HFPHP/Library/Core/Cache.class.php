<?php

namespace Core;

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
     * 本方法是静态方法，在静态方法中无法调用非静态方法，因为静态方法生成的时候非静态的方法尚未生成。
     * @return mixed
     */
    static function getInstance($type = 'memcache') {
        static $_instance = array();
        if(!isset($_instance[$type])) {
            $_instance[$type] = self::connect($type);
        }
        return $_instance[$type];
    }

    /**
     *连接缓存
     *
     * @return object
     */

    static private function connect($type) {
        $cache = null;
        if(empty($type)) $type = C('DATA_CACHE_TYPE');
        $class = __CLASS__.ucfirst(strtolower(trim($type)));

        if(is_file(LIB_PATH.'Driver/Cache/'.$class.'.class.php')) {
            import('HF/Driver/Cache/'.$class);
            $cache = new $class();
        } else {
            halt(L('_CACHE_TYPE_INVALID_').':'.$type);
        }

        return $cache;

    }








}
