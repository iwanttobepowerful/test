<?php
namespace Admin\Controller;
use Think\Controller;
class AuditController extends Controller {
	public $user = array();
    //初始化方法
    public function _initialize(){
        load('@.functions');
        $this->user = D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }

	public function add(){
        $cid = I("contractno");
        if(!empty($cid)){
            //查询合同编号
            $contract = "";
        }
        $this->assign("body",$body);
        $this->display();
    }
    public function reportList(){
        $this->assign("body",$body);
        $this->display();
    }
}