<?php
namespace Wap\Controller;
use Think\Controller;
class ContractController extends Controller {
    //初始化方法
    public function _initialize(){
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
        $this->assign("pagetitle",$this->pagetitle);
    }
    public function contractList(){
        $body = array(
            "pagetitle"=>"合同列表",
        );
        $this->assign($body);
        $this->display();
    }
    public function wait(){
        $body = array(
            "pagetitle"=>"待审核报告",
            'backed'=>true,
        );
        $this->assign($body);
        $this->display();
    }
}