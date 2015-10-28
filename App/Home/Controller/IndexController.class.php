<?php
class IndexController extends Controller {
    public function index(){
        $attr = D('Attr');
        $info = $attr->where('id = 2')->find();
    }
}