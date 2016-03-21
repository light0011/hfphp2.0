<?php


//优化的require_once,判断是否为文件并且引入
function require_cache($filename){
    static $_importFiles = array();

    if(!isset($_importFiles[$filename])){
        if(is_file($filename)){
            require $filename;
            $_importFiles[$filename] = true;
        } else {
            $_importFiles[$filename] = false;
        }
    }

    return $_importFiles[$filename];
}


//获取配置值
function C($name=null,$value=null){

    static $_config = array();

    //无参数时，获取所有
    if(empty($name)) return $_config;

    //优先执行赋值或者获取
    if(is_string($name)){
        $name = strtolower($name);
        if(is_null($value))
            return isset($_config[$name]) ? $_config[$name] : null;
        $_config[$name] = $value;
    }

    //批量设置
    if(is_array($name)){
        return $_config = array_merge($_config,array_change_key_case($name));
    }

    //传入非法参数，直接返回null
    return null;

}


//A函数用于实例化Action类，传入类的名称即可

function A($name){
    static $_controller = array();

    if(isset($_controller[$name]))
        return $_controller[$name];

    $class = $name.'Controller';

    //class_exists($class,[bool $autoload = true])会自动调用autoload方法，除非第二个参数填false
    if(class_exists($class)){
        $controller = new $class();
        $_controller[$name] = $controller;
        return $controller;
    } else {
        return false;
    }
}



//D函数用于实例化Model类，传入类的名称即可

function D($name){
    static $_model = array();

    if(isset($_model[$name]))
        return $_model[$name];

    $class = $name.'Model';

    //class_exists($class,[bool $autoload = true])会自动调用autoload方法，除非第二个参数填false
    if(class_exists($class)){
        $model = new $class();
        $_model[$name] = $model;
        return $model;
    } else {
        return false;
    }
}


//获取和设置语言定义
function L($name=null,$value=null){

    static $lang = array();

    //空参数返回所有定义
    if(empty($name))
        return $lang;

    //判断语言是否存在，存在则返回，不存在返回大写的$name
    if(is_string($name)){
        $name = strtoupper($name);
        if(is_null($value)){

            return isset($lang[$name]) ? $lang[$name] : $name;
        }
        $lang[$name] = $value;
        return;
    }

    //批量定义
    if(is_array($name)){
        $lang = array_merge($lang,array_change_key_case($name,CASE_UPPER));
    }

    return;

}


//URL组装，支持不同模式
//格式：U('[分组/模块/操作]?参数','参数','伪静态后缀')
function U($url,$vars='',$suffix=true){
    //解析url
    $info = parse_url($url);

    $url = !empty($info['path']) ? $info['path'] : ACTION_NAME;

    //解析参数
    if(is_string($vars)){  //例如aaa=1&bbb=2 转换为数组
        parse_str($vars,$vars);
    } elseif (!is_array($vars)){
        $vars = array();
    }

    //url分割线
    $depr = C('URL_PATHINFO_DEPR');

    if($url){
        if('/' != $depr){ //安全替换
            $url = str_replace('/',$depr,$url);
        }

        //解析分组、模块和操作
        $url = trim($url,$depr);

        $path = explode($depr,$url);

        $var =array();

        $var[C('VAR_ACTION')] = !empty($path) ? array_pop($path) : ACTION_NAME;
        $var[C('VAR_CONTROLLER')] = !empty($path) ? array_pop($path) : CONTROLLER_NAME;

       if(C('MODULE_ALLOW_LIST')){
           if(!empty($path)){
               $module = array_pop($path);
               $var[C('VAR_MODULE')] = $module;
           } else {
               if(MODULE_NAME != C('DEFAULT_MODULE')){
                   $var[C('VAR_MODULE')] = MODULE_NAME;
               }
           }
       }


    }


    if(C('URL_MODEL') == 0){ //普通模式URL转换
        $url = PHP_FILE.'?'.http_build_query($var);
        if(!empty($vars)){
            $vars = http_build_query($vars);
            $url .= '&'.$vars;
        }
    } else {  //PATHINFO模式
        $url = PHP_FILE.'/'.implode($depr,array_reverse($var));


        if(!empty($vars)){  //添加参数
            $vars = http_build_query($vars);
            $url .= $depr.str_replace(array('=','&'),$depr,$vars);
        }

        if($suffix){
            $suffix = $suffix === true ? C('URL_HTML_SUFFIX') : $suffix;
            if($suffix){
                $url .= '.'.ltrim($suffix,'.');
            }
        }


    }


    return $url;


}



//M函数用于实例化一个没有函数文件的Model
function M($name='',$tablePrefix=''){

    static $_model = array();

    if(!isset($_model[$name.'_Model'])){
        $_model[$name.'_Model'] = new Model($name,$tablePrefix);
    }

    return $_model[$name.'_Model'];


}


//获取客户端IP地址
function get_client_ip(){
    static $ip = NULL;
    if($ip !== NULL) return $ip;
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }elseif(isset($_SERVER['REMOTE_ADDR'])){
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    //IP地址合法验证
    $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';

    return $ip;
}


//根据PHP各种类型变量生成唯一标示号
function to_uniq_string($mix) {
    if(is_object($mix) && function_exists('spl_object_hash')) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix).strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);

}

/**
 * 快速文件数据读取和保存，针对简单类型数据，包括字符串与数组，本方法相当于S()方法的子集
 * @param string $name 缓存名称
 * @parram mixed $value 缓存名称
 * @param string $path 缓存路径
 * @return mixed
 */

function F($name,$value= '',$path=DATA_PATH) {
    static $_cache = array();
    $filename = $path . $name . '.php';
    //赋值
    if('' !== $value) {
        if(is_null($value)) {
            //删除缓存
            return unlink($filename);
        } else {
            //缓存数据
            $dir = dirname($filename);
            //目录不存在则创建
            if(!is_dir($dir))
                mkdir($dir);
            $_cache[$name] = $value;
            return file_put_contents($filename,"<?php\nreturn ".var_export($value,true).";\n?>");
        }
    }
    //取值，并且先从静态变量中取
    if(isset($_cache[$name]))
        return $_cache[$name];

    //获取缓存数据
    if(is_file($filename)) {
        $value = include $filename;
        $_cache[$name] = $value;
    } else {
        $value = false;
    }
    return $value;

}


/**
 * 取得对象实例，支持调用类的静态方法
 * @param string $name 类的名称
 * @param string $methond 要调用的类的方法
 * @param array $args 调用类的方法中需要传入的参数
 * @return $mixed
 *
 */

function get_instance_of($name, $method='', $args=array()) {
    static $_instance = array();
    $identify = empty($args) ? $name . $method : $name . $method . to_uniq_string($args);
    if(!isset($_instance[$identify])) {
        if(class_exists($name)) {
            $obj = new $name;
            if(method_exists($name,$method)) {
                if(!empty($args)) {
                    $_instance[$identify] = call_user_func_array(array($obj,$method),$args);
                } else {
                    $_instance[$identify] = $obj->$method;
                }
            } else {
                $_instance[$identify] = $obj;
            }
        } else {
            halt(L('_CLASS_NOT_EXIST_') . ':' . $name);
        }
    }
    return $_instance[$identify];
;}




/**
 * 导入所需的类库，但是只支持本项目下，公共目录common下，以及vendor下
 * @param string $class 类命名空间字符串
 * @return boolean
 */
function import($class) {
    static $_file = array();
    if(isset($_file[$class])) {
        return true;
    }
    $class = str_replace('.','/',$class);

    $class_path = explode('/',$class);

    if('@' == $class_path[0] || MODULE_NAME == $class_path[0]) {
        //加载当前模块下的类库
        $baseUrl = MODULE_PATH;
        $class = substr_replace($class,'',0,strlen($class_path[0]) + 1);
    }elseif('Common' == $class_path[0]) {
        //加载公共模块下的类库
        $baseUrl = COMMON_PATH;
        $class = substr($class,7);
    }elseif('HF' == $class_path[0]){
        $baseUrl = LIB_PATH;
        $class = substr_replace($class,'',0,strlen($class_path[0]) + 1);
    }

    if(substr($baseUrl,-1) != '/')
        $baseUrl .= '/';

    $class_file = $baseUrl.$class.EXT;
    if(!class_exists(basename($class),false)) {
        //如果类不存在，则导入类库wenjian
        return require_cache($class_file);
    }

    return true;


}





/**
 * TODO 完善
 * 错误输出
 * @param mixed $error 错误
 * @return void
 */

function halt($error) {
    exit($error);
}



?>