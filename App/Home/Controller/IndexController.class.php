<?php
class IndexController extends Controller {
    public function index(){
        $this->assign('name','李军光');
        $this->display();
    }
}