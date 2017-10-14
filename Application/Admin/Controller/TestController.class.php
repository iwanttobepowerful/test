<?php

namespace Admin\Controller;
use Think\Controller;
class TestController extends Controller{
    public $user = null;

    public function _initialize(){
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }

    //工作通知单查询
    public function infoList(){
        $this->display();
    }

    //抽样单查询
    public function sampleList(){
        $this->display();
    }

    //检测记录上传
    public function recordUpload(){
        $this->display();
    }


}