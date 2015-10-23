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


    protected function __construct(){

        //设置模型名
        $this->name = substr(get_class($this),0,-5);
        //设置表前缀
        $this->tablePrefix = C('DB_PREFIX');

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


    public  function add($data = array(),$options=array()){
        //数据处理，删除不是数据库字段的键值对
        $data = $this->facade($data);
        //分析表达式
        $options = $this->parseOptions($options);
        //写入数据库,返回执行结果
        return $this->db->insert($data,$options);
    }


    protected function update(Array $where,Array $updateData){
        return $this->db->update($this->tables,$where,$updateData);
    }

    public  function select($options = array()){

        $options = $this->parseOptions($options);

        return $this->db->select($options);
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

        $this->fields['autoinc'] = false;

        foreach($fields as $key=>$value){
            if($value['primary']){
                $this->fields['pk'] = $key;
                if($value['autoinc']){
                    $this->fields['autoinc'] = true;
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









}













?>