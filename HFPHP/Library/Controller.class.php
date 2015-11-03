<?php

//HFPHP控制器基类  抽象类

class Controller{

    //视图实例对象
    protected $view = null;


    public function __construct(){
        $this->view = Templates::getInstance();
    }

    protected function display($tplFile='',$status=1){
        $this->view->display($tplFile,$status);
    }

    protected function assign($var,$value){
        $this->view->assign($var,$value);
    }


    //成功跳转
    protected function success($message,$jumpUrl=''){
        $this->dispatchJump($message,1,$jumpUrl);
    }

    //失败跳转
    protected function error($message,$jumpUrl=''){
        $this->dispatchJump($message,0,$jumpUrl);
    }


    //支持错误导向和正确跳转
    private function dispatchJump($message,$status=1,$jumpUrl=''){
        //跳转地址
        if(!empty($jumpUrl)) $this->assign('jumpUrl',$jumpUrl);
        //提示标题
        $this->assign('msgTitle',$status ? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));

        $this->assign('status',$status);

        if($status){ //发送成功消息
            $this->assign('message',$message);
            //成功操作默认停留一秒
            $this->assign('waitSecond','100');
            //没有跳转地址，默认到操作前页面
            if(!$jumpUrl) $this->assign('jumpUrl',$_SERVER["HTTP_REFERER"]);
            $this->display(C('TMPL_ACTION_SUCCESS'),0);
        }else{
            $this->assign('message',$message); //错误提示信息
            //错误操作默认停留3秒
            $this->assign('waitSecond','3');
            //默认发生错误的话返回上页
            if(!$jumpUrl) $this->assign('jumpUrl','javascript:history.back(-1);');
            $this->display(C('TMPL_ACTION_ERROR'),0);
            //中止执行
            exit;
        }




    }






}













?>

