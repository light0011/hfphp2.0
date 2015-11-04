<?php
class IndexController extends Controller {
    public function index(){

        $attr = M('ad');

        $info = $attr->where('id=2')->find();

    }


    public function dis(){
        var_dump($_SERVER["HTTP_REFERER"]);
    }


}