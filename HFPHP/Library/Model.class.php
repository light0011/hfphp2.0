<?php

class Model extends DB{

    protected $db = null;
    protected $tables = array();

    // 字段信息
    protected $fields = array();

    //查询表达式参数
    protected $options = array();

    //模型名称
    protected $name = '';

    //表前缀
    protected $tablePrefix = '';

    //主键名称
    protected $pk = 'id';

    //数据信息
    protected $data = array();

    //是否自动检测数据表字段信息
    protected $autoCheckFields = true;

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
        $this->db = parent::getInstance();

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
    public function create(){
        $data = $_POST;


        if(empty($data) || !is_array($data)){
            return false;
        }

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


















}













?>