<?php
namespace Wap\Controller;
use Think\Controller;
class ContractController extends Controller {
    public $user = array();
    public $statusArr = array(2,3,5,6);
    //初始化方法
    public function _initialize(){
        load('@.functions');
        $this->user = D("account")->checkLogin();
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
    //报告审批
    public function reportList(){
        $rs = array("msg"=>"","status"=>"fail");
        $if_admin = $this->user['super_admin'];
        if(!$this->user['super_admin'] && $this->user['gid']){
            $role = D('common_role')->where('id='.$this->user['gid'])->find();
        }
        list($offset,$pagesize,$page) = pageOffset();
        $status = I("status",0,'intval');
        $where = " 1=1";
        if($status) $where .= " and status=".$status;
        //status:2,待审，3，待审批，5，已内部签发，6，已外部签发
        //$where['contract_flow.status'] = 3;
         $list = D("contract_flow")->where($where)->order("external_sign_time desc")->limit("{$offset},{$pagesize}")->select();
        if($list){
            $centrenoIds = array();
            foreach ($list as $value) {
                $centrenoIds[] = "'".$value['centreno']."'";
            }
            $cost = D("test_cost")->where("centreno in(".implode(",", $centrenoIds).")")->select();
            $contract = D("contract")->where("centreno in(".implode(",", $centrenoIds).")")->select();
            $cost && $cost = assColumn($cost,'centreno');
            $contract && $contract = assColumn($contract,'centreno');

            foreach ($list as $key => $value) {
                $value['test_cost'] = $cost[$value['centreno']] ? $cost[$value['centreno']]:array();
                $value['contract'] = $contract[$value['centreno']] ? $contract[$value['centreno']]:array();
                $list[$key] = $value;
            }
            $rs['list'] = $list;
            $rs['status'] = "succ";
            $rs['page'] = $page;
        }
        $this->ajaxReturn($rs);
    }
    //状态操作
    public function doUpdate(){
        $rs = array("msg"=>"","status"=>"fail");
        $status = I("status",0,'intval');
        if($status && in_array($status, $this->statusArr)){
            $centreno = I("centreno");//中心编号
            M()->M()->startTrans();
            if($contract = D("contract_flow")->where("centreno='{$centreno}'")->find()){
                //if condition 强制状态不能任意修改
                $data = array(
                    'status'=>$status
                );
                if($status==3){
                    $data['verify_time'] = date("Y-m-d H:i:s");
                    $data['verify_user_id'] = $this->user['id'];
                }elseif($status==4){
                    $data['approve_time'] = date("Y-m-d H:i:s");
                    $data['approve_user_id'] = $this->user['id'];
                }elseif($status==5){
                    $data['inner_sign_time'] = date("Y-m-d H:i:s");
                    $data['inner_sign_user_id'] = $this->user['id'];
                }elseif($status==6){
                    $data['external_sign_time'] = date("Y-m-d H:i:s");
                    $data['external_sign_user_id'] = $this->user['id'];
                }
                if(D("contract_flow")->where("id=".$contract['id'])->save($data)){
                    //如果有其它同步更新则加入
                    $rs['status'] = 'succ';
                    M()->commit();
                }else{
                    M()->rollback();
                }
            }
        }
        $this->ajaxReturn($rs);
    }
}