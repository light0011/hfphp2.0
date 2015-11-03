<?php
class IndexController extends Controller {
    public function index(){

        $this->error('新增失败',U('Index/dis'));

    }


    public function dis(){
        var_dump($_SERVER["HTTP_REFERER"]);
    }


}