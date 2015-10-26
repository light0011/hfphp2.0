<?php
class IndexController extends Controller {
    public function index(){
        $attr = D('Attr');

        $attr->where('id = 1')->delete();

    }
}