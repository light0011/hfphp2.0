<?php

class Model{

    //操作状态
    const MODEL_INSERT = 1;  //插入模型数据
    const MODEL_UPDATE = 2;  //更新模型数据
    const MODEL_BOTH = 3;   //包含上面两种方式

    const EXISTS_VALIDATE = 0; //表单存在字段验证
    const MUST_VALIDATE = 1; //必须验证
    const VALUE_VALIDATE = 2; //表单值不为空则验证

    protected $db = null;
    protected $tables = array();

    // 字段信息
    protected $fields = array();

    //查询表达式参数
    protected $options = array();

    //自动验证定义
    protected $validate = array();
    //模型名称
    protected $name = '';

    //错误信息
    protected $error = '';

    //表前缀
    protected $tablePrefix = '';

    //主键名称
    protected $pk = 'id';

    //数据信息
    protected $data = array();

    //是否自动检测数据表字段信息
    protected $autoCheckFields = true;

    //是否批处理验证
    protected $patchValidate = false;

    public function __construct($name='',$tablePrefix=''){

        //设置模型名
        if('' != $name){
            $this->name = $name;
        }else{
            $this->name = substr(get_class($this),0,-5);
        }

        //设置表前缀
        if('' != $tablePrefix){
            $this->tablePrefix = $tablePrefix;
        }else{
            $this->tablePrefix = C('DB_PREFIX');
        }

        //获得DB实例
        $this->db = DB::getInstance();

        //获取字段信息并赋值给$this->fields
        $this->flush();

    }

    //利用_call实现连贯操作
    public function __call($method,$args){
        if(in_array(strtolower($method),array('field','table','where','order','limit'),true)){
            $this->options[strtolower($method)] = $args[0];
            return $this;
        }

    }

    //设置数据对象的值
    public function __set($name,$value){
        //设置数据对象属性
        $this->data[$name] = $value;
    }


    public  function add($data = array(),$options=array()){
        //数据处理，删除不是数据库字段的键值对
        if(empty($data)){

            //没有传递数据，获取当前数据对象的值
            if(!empty($this->data)){
                $data = $this->data;
                //重置数据
                $this->data = array();
            }else{
                return false;
            }
        }
        $data = $this->facade($data);
        //分析表达式
        $options = $this->parseOptions($options);
        //写入数据库,返回执行结果
        return $this->db->insert($data,$options);
    }


    public function save($data=array(),$options=array()){

        if(empty($data)){
            //没有传递数据，获取当前数据对象的值
            if(!empty($this->data)){
                $data = $this->data;
                //重置数据
                $this->data = array();
            } else {

                return false;
            }
        }



        //处理数据
        $data = $this->facade($data);

        $options = $this->parseOptions($options);



        if(!isset($options['where'])){
            //如果存在主键数据，则自动座位更新数据
            if(isset($data[$this->getPK()])){
                $pk = $this->getPK();
                $options['where'] = $pk.' = '.$data[$pk];
                unset($data[$pk]);
            } else {
                //没有任何执行条件，则不执行
                return false;
            }
        }

        $result = $this->db->update($data,$options);

        return $result;



    }

    public  function select($options = array()){

        $options = $this->parseOptions($options);

        return $this->db->select($options);
    }

    //find方法,只查询一个
    public function find($options = array()){
        $options = $this->parseOptions($options);
        //总是查找一条记录
        $options['limit'] = 1;

        $resultSet = $this->db->select($options);

        return $resultSet[0];

    }

    public function delete($options=array()){
        //分析表达式
        $options = $this->parseOptions($options);
        if(empty($options['where'])){
            //如果条件为空，返回false
            return false;
        }

        return $this->db->delete($options);

    }

    //分析options，得到组装sql语句的参数
    protected function parseOptions($options = array()){
        if(is_array($options))
            $options = array_merge($this->options,$options);

        //每次都先清空，以免影响下次查询
        $this->options = array();

        //获取表名
        if(!isset($options['table'])){
            $options['table'] = $this->getTableName();
        }

        //记录操作的模型名
        $options['model'] = $this->name;

        return $options;

    }

    //得到表名
    private function getTableName(){

        return $this->tablePrefix.strtolower($this->name);
    }


    //获取字段信息并赋值
    public function flush(){

        //得到字段信息
        $fields = $this->db->getFields($this->getTableName());

        //无法获取字段信息
        if(!$fields){
            return false;
        }

        $this->fields = array_keys($fields);

        $this->fields['_autoinc'] = false;

        foreach($fields as $key=>$value){
            if($value['primary']){
                $this->fields['_pk'] = $key;
                if($value['autoinc']){
                    $this->fields['_autoinc'] = true;
                }
            }
        }



    }

    //对保存到数据库的数据进行处理
    private  function facade($data){

        //检查非数据库字段
        if(!empty($this->fields)){
            foreach($data as $key=>$value){
                if(!in_array($key,$this->fields,true)){
                    unset($data[$key]);
                }
            }
        }

        return $data;

    }



    //获取主键名称
    public function getPK(){
        return isset($this->fields['pk']) ? $this->fields['pk'] : $this->pk;
    }


    //创建数据对象，但不保存到数据库
    public function create($type=''){
        $data = $_POST;

        if(empty($data) || !is_array($data)){
            return false;
        }

        //状态
        $type = $type ? $type : (!empty($data[$this->getPK()]) ? self::MODEL_UPDATE : self::MODEL_INSERT);


        //数据自动验证
        if(!$this->autoValidation($data,$type)) return false;

        //验证完成生成数据对象
        if($this->autoCheckFields){ //开启字段检测，过滤非法字段数据
            $vo = array();

            foreach($this->fields as $key=>$value){
               if(substr($key,0,1) == '_') continue;

                $val = isset($data[$value]) ? $data[$value] : null;

                //保证赋值有效
                if(!is_null($val)){
                    $vo[$value] = (get_magic_quotes_gpc() && is_string($val)) ? stripslashes($val) : $val;
                }
            }
        }else{
            $vo = $data;
        }


        //赋值当前数据对象
        $this->data = $vo;
        //返回创建的数据对象以供其他调用
        return $vo;

    }


    //数据验证
    private function autoValidation($data,$type){


        //属性验证
        if(!empty($this->validate)){
            //如果批处理数据验证，重置错误信息
            if($this->patchValidate){
                $this->error = array();
            }

            foreach($this->validate as $key=>$value){
                //验证因子格式： array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])

                if(empty($value[5]) || $value[5] == self::MODEL_BOTH || $value[5] == $type){

                    $value[3] = isset($value[3]) ? $value[3] : self::EXISTS_VALIDATE;

                    $value[4] = isset($value[4]) ? $value[4] : 'regex';

                    //判断验证条件
                    switch($value[3]){
                        //必须验证，不管表单是否设置该字段
                        case self::MUST_VALIDATE:
                            if(false === $this->validationField($data,$value)){
                                return false;
                            }
                            break;
                        case self::VALUE_VALIDATE:
                            if('' != trim($data[$value[0]])){
                                if(false === $this->validationField($data,$value)){
                                    return false;
                                }
                            }
                            break;

                        //默认表单存在该字段就验证
                        default:
                            if(isset($data[$value[0]])){

                                if(false === $this->validationField($data,$value)){
                                    return false;
                                }
                            }

                    }




                }

            }


            //批量验证的时候，最后返回错误
            if(!empty($this->error)) return false;

        }

        return true;

    }


    //验证表单字段，支持批量验证
    private  function validationField($data,$val){
        if(false === $this->validationFieldItem($data,$val)){
           if($this->patchValidate){
               $this->error[$val[0]] = $val[2];
           }else{
               $this->error = $val[2];
               return false;
           }

        }
        return ;
    }



    //根据验证因子验证字段
    private function validationFieldItem($data,$val){

       switch($val[4]){
           case 'function': //使用函数进行验证

           case 'callback': //使用本类中的方法进行验证
               //还可能传参
               $args = isset($val[6]) ? (array)$val[6] : array();
               array_unshift($args,$data[$val[0]]);

               if('function' == $val[4]){
                   return call_user_func_array($val[1],$args);
               }else{
                   return call_user_func_array(array(&$this,$val[1]),$args);
               }


           case 'confirm': //验证两个字段是否相同
               return $data[$val[0]] == $data[$val[1]];

           case 'unique': //验证两个字段是否相同
               //where尚未完善，所以这里先简单只作为字符串处理，留坑再填
               $map = $val[0].' = '.$data[$val[0]];

               if(!empty($data[$this->getPK()]))
                   $map .= ' AND '.$this->getPK().' != '.$data[$this->getPK()];
               if($this->field($this->getPK())->where($map)->find())
                   return false;

               return true;



           default: //检测附加规则,这里面只需要$data[$val[0]],$val[1],$val[4]这三个参数，那单独再写个方法吧
               return $this->check($data[$val[0]],$val[1],$val[4]);

       }
    }



    //验证数据，默认为正则验证
    private function check($value,$rule,$type='regex'){

        switch(strtolower($type)){
            //验证是否在某个指定范围之内，逗号分隔符或者数组
            case 'in':
                $range = is_array($rule) ? $rule : explode(',',$rule);
                return in_array($value,$range);
            //验证是否在某个范围内
            case 'between':
                list($min,$max) = explode(',',$rule);
                return $value>=$min && $value<=$max;
            //验证是否等于某个值
            case 'equal':
                return $value == $rule;
            //验证长度
            case 'length':
                //当前数据长度
                $length = mb_strlen($value,'utf-8');
                if(strpos($rule,',')){
                    list($min,$max) = explode(',',$rule);
                    return $length >= $min && $length <= $max;
                }else{
                    //指定长度
                    return $length == $rule;
                }
            case 'expire':
                list($start,$end) = explode(',',$rule);
                if(!is_numeric($start)) $start = strtotime($start);
                if(!is_numeric($end)) $end = strtotime($end);

                return $_SERVER['REQUEST_TIME'] >= $start && $_SERVER['REQUEST_TIME'] <= $end;
            //IP操作许可验证
            case 'ip_allow':
                return in_array(get_client_ip(),explode(',',$rule));

            //IP操作禁止验证
            case 'ip_deny':
                return in_array(get_client_ip(),explode(',',$rule));

            case 'regex':
            default: //默认只有正则验证
                return $this->regex($value,$rule);



        }


    }


    //正则验证
    private function regex($value,$rule){
        $validate = array(
            'require'=>'/.+/',
            'number' => '/^\d+$/',
            'zip' => '/^[1-9]\d{5}$/',
            'interger' => '/^[-\+]?\d+$/',
            'english' => '/^[A-Za-z]+$/',
            'double' => '/^[-\+]?\d+(\.\d+)?$/',
            'currency' => '/^\d+(\.\d+)?$/',
            'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url' => '/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+\.[A-Za-z0-9]+(\.[A-Za-z0-9]+)*$/'
            //暂时没有网址类型
        );
        //检测是否有内置的正则表达式
        if(isset($validate[strtolower($rule)]))
            $rule = $validate[strtolower($rule)];
        //有，返回1,。无，返回0
        return preg_match($rule,$value) == 1;
    }



    //返回$validate中的验证信息
    public function getError(){
        return $this->error;
    }




}













?>