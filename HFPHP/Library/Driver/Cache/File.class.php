<?php

/**
 *
 * 文件类型缓存类
 *
 */


class File extends Cache {


    public function __construct($options=array()) {
        $this->options['temp'] = !empty($options['temp']) ? $options['temp'] : C('DATA_CACHE_PATH');
        $this->options['prefix']    =   isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');
        $this->options['expire']    =   isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
        $this->options['length']    =   isset($options['length'])?  $options['length']  :   0;
        if(substr($this->options['temp'],-1) != '/') $this->options['temp'] .= '/';
        $this->init();
    }

    /**
     * 初始化检查
     * @access private
     * @return boolean
     *
     */

    private function init() {
        //创建应用缓存目录
        if(!is_dir($this->options['temp'])) {
            mkdir($this->options['temp']);
        }
    }

    /**
     * 取得变量的存储文件名
     * @access private
     * @param string $name 缓存变量名
     * @return string
     *
     */

    private function filename($name) {
        $name = md5(C('DATA_CACHE_KEY').$name);
        $filename = $this->options['prefix'].$name.'.php';
        return $this->options['temp'].$filename;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     *
     */

    public function get($name) {
        $filename = $this->filename($name);
        if(!is_file($filename)) {
            return false;
        }
        $content = file_get_contents($filename);
        if(false !== $content) {
            $expire = (int)substr($content,8,12);
            if($expire != 0 && time() > filemtime($filename) + $expire) {
                //缓存过期删除缓存文件
                unlink($filename);
                return false;
            }
            if(C('DATA_CACHE_CHECK')) { //开启数据校验
                 $check = substr($content,20,32);
                 $content = substr($content,52,-3);
                if($check != md5($content)) { //校验错误
                     return false;
                }
            } else {
                $content = substr($content,20,-3);
            }
            if(C('DATA_CACHE_COMPRESS') && function_exists('gzcomress')) {
                //解压数据
                $content = gzuncompress($content);
            }

            $content = unserialize($content);
            return $content;

        } else {
            return false;
        }

    }




    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value 缓存数据
     * @param int $expire 有效时间,0为永久
     * @return boolean
     */

    public function set($name,$value,$expire=null) {
        if(is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $filename = $this->filename($name);
        $data = serialize($value);
        if(C('DATA_CACHE_COMPRESS') && function_exists('gzcompress')) {
            $data = gzcompress($data,3);
        }
        //开启数据校验
        if(C('DATA_CACHE_CHECK')) {
            $check = md5($data);
        } else {
            $check = '';
        }

        $data = "<?php\n//".sprintf('%012d',$expire).$check.$data."\n?>";
        $result = file_put_contents($filename,$data);
        if($result) {
            if($this->options['length'] > 0) {
                //记录缓存队列
                $this->queue($name);
            }
            clearstatcache();
            return true;
        } else {
            return false;
        }

    }



    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     *
     */

    public function rm($name) {
        return unlink($this->filename($name));
    }


    /**
     * 清除缓存
     * @access public
     * @return boolean
     *
     */

    public function clear() {
        $path = $this->options['temp'];
        $files = scandir($path);
        if($files) {
            foreach($files as $file) {
                if($file != '.' && $file != '..' && is_dir($path.$file)) {
                    array_map('unlink',glob($path.$file.'/*.*'));
                } elseif(is_file($path.$file)) {
                    unlink($path.$file);
                }
            }
            return true;
        }
        return false;
    }














}
