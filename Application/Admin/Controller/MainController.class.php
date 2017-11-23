<?php
namespace Admin\Controller;
use Think\Controller;
class MainController extends Controller {
    //初始化方法
    public function _initialize(){
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }
    public function index(){
        $file = "./Public/attached/2017-11-21/tmp.png";
        unlink($file);
        waterMark('./Public/attached/2017-11-21/page-1.jpg','./Public/static/images/sealB.png',$file,array(100,100));
        
       $this->display();
    }
}