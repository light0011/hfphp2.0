<?php

//模板解析类
class Parser{

    //字段，保存模板内容
    private $tpl;

    //构造方法，用于获取模板文件里的内容
    public function __construct($tplFile){
        if(!$this->tpl = file_get_contents($tplFile)){
            exit('ERROR:模板文件读取有误！');
        }
    }

    //解析普通变量
    private function parVar(){
        $patten = '/\{\$([\w]+)\}/';
        if(preg_match($patten,$this->tpl)){
            $this->tpl = preg_replace($patten,"<?php echo \$this->vars['$1'];?>",$this->tpl);
        }
    }

    //解析使用函数
    private function parFunction(){
        //{:U('User/insert')}

        //<?php echo U('User/insert');

        $patten = '/\{\:(\w\(.*\))\}/';
        if(preg_match($patten,$this->tpl)){
            $this->tpl = preg_replace($patten,"<?php echo $1;?>",$this->tpl);
        }
    }

    //解析if语句
    private function parIf(){
        $pattenIf = '/\{if\s+\$([\w]+)\}/';
        $pattenEndIf = '/\{\/if\}/';
        $pattenElse = '/\{else\}/';

        if(preg_match($pattenIf,$this->tpl)){
            if(preg_match($pattenEndIf,$this->tpl)){
                $this->tpl = preg_replace($pattenIf,"<?php if (\$this->vars['$1']){ ?>",$this->tpl);
                $this->tpl = preg_replace($pattenEndIf,"<?php }?>",$this->tpl);
                if(preg_match($pattenElse,$this->tpl)){
                    $this->tpl = preg_replace($pattenElse,"<?php } else { ?>",$this->tpl);
                }
            } else {
                exit('ERROR:if语句没有关闭！');
            }
        }
    }


    //解析foreach语句
    private function parForeach(){
        $pattenForeach = '/\{foreach\s+\$([\w]+)\(([\w]+),([\w]+)\)\}/';
        $pattenEndForeach = '/\{\/foreach\}/';
        $pattenVar = '/\{@([\w]+)\}/';

        if(preg_match($pattenForeach,$this->tpl)){
            if(preg_match($pattenEndForeach,$this->tpl)){
                $this->tpl = preg_replace($pattenEndForeach,"<?php foreach(\$this->vars['$1'] as \$$2=>\$$3 { ?>",$this->tpl);
                $this->tpl = preg_replace($pattenEndForeach,"<?php } ?>",$this->tpl);
                if(preg_match($pattenVar,$this->tpl)){
                    $this->tpl = preg_replace($pattenVar,"<?php echo \$$1 ?>",$this->tpl);
                }
            } else {
                exit('ERROR：foreach语句必须有结尾标签！');
            }
        }

    }




    //解析include语句
    private function parInclude(){
        $patten = '/\{include\s+file=\"([\w\.\-]+)\"\}/';
        if(preg_match($patten,$this->tpl,$file)){
            if(!file_exists($file[1]) || empty($file)){
                exit('ERROR:包含文件出错！');
            }
            $this->tpl = preg_replace($patten,"<?php include '$1' ;?>",$this->tpl);
        }
    }



    //对外公共方法
    public function compile($parFile){
        //解析模板内容
        $this->parVar();
        $this->parFunction();
        $this->parIf();
        $this->parForeach();
        $this->parInclude();
        if(!file_put_contents($parFile,$this->tpl)){
            exit('ERROR:编译文件生成出错！');
        }
    }













}



















?>