<?php

namespace Admin\Controller;
use Think\Controller;
class TestController extends Controller{
    public $user = null;

    public function _initialize(){//系统Action类提供了一个初始化方法_initialize接口，可以用于扩展需要，_initialize方法会在所有操作方法调用之前首先执行
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }

    //工作通知单查询
    public function infoList(){
        $keyword = I("keyword");//获取参数
        $where= "centreno='{$keyword}'";
        $result=M('work_inform_form')->where($where)->field("id,centreno,samplename")->select();
        $body=array(
            'lists'=>$result,

        );
        $this->assign($body);
        $this->display();
    }

    //工作通知单显示
    public function infoShow(){
        $keyword = I("id");//获取参数
        $where= "centreno='{$keyword}'";
        $result=M('work_inform_form')->where($where)->field("id,centreno,samplename")->select();
        $body=array(
            'lists'=>$result,

        );
        $this->assign($body);
        $this->display();
    }



    //抽样单查询
    public function sampleList(){
        $keyword = I("keyword");//获取参数
        $where= "centreno='{$keyword}'";
        $result=M('sampling_form')->where($where)->field("id,centreno,clientname,productunit")->select();

        $body=array(
            'lists'=>$result,

        );
        $this->assign($body);
        $this->display();
    }

    //检测记录上传
    public function recordUpload(){
        $this->display();
    }


}