<?php
namespace Wap\Controller;
use Think\Controller;
class ContractController extends Controller {
    public $user = array();
    public $statusArr = array(2,3,4,5,6);
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
        $status = I("status",0,'intval');
        if($status==2){
            $pagetitle = "待提交审核报告";
        }elseif($status==3){
            $pagetitle = "待审核报告";
        }elseif($status==4){
            $pagetitle = "待审批报告";
        }elseif($status==5){
            $pagetitle = "待签发报告";
        }elseif($status==6){
            $pagetitle = "已出报告";
        }
        $body = array(
            "pagetitle"=>$pagetitle,
            'backed'=>true,
            'status'=>$status,
        );
        $this->assign($body);
        $this->display();
    }
    public function contractDetail(){
        $centreno = I("centreno");
        $body = array(
            'pagetitle'=>"合同详情",
        );
        if($centreno){
            $contract = D("contract")->where("centreno='{$centreno}'")->find();
            $body['contract'] = $contract;
        }
        $this->assign($body);
        $this->display("Contract/chouyangdan");
    }

    public function add(){
        $body = array(
            "pagetitle"=>"新增",
            'backed'=>true,
        );
        $this->assign($body);
        $this->display();
    }

    public function over(){
        $body = array(
            "pagetitle"=>"合同收入",
            'backed'=>true,
        );
        $this->assign($body);
        $this->display();
    }

    public function updatePassword(){
        $body = array(
            "pagetitle"=>"修改密码",
            'backed'=>true,
        );
        $this->assign($body);
        $this->display();
    }

    public function sp(){
        $body = array(
            "pagetitle"=>"特殊编号申请",
            'backed'=>true,
        );
        $this->assign($body);
        $this->display();}
    //报告审批
    public function reportList(){
        $rs = array("msg"=>"","status"=>"succ","list"=>array());
        $if_admin = $this->user['super_admin'];
        if(!$this->user['super_admin'] && $this->user['gid']){
            $role = D('common_role')->where('id='.$this->user['gid'])->find();
        }
        list($offset,$pagesize,$page) = pageOffset(1);
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
            M()->startTrans();
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
                    if($status==4){
                        //同步修改其它表
                        if(D("contract")->where("centreno='{$centreno}'")->save(array("ifedit"=>1))){
                            $rs['status'] = 'succ';
                            M()->commit();
                        }else{
                            M()->rollback();
                        }
                    }else{
                        $rs['status'] = 'succ';
                        M()->commit();
                    }

                }else{
                    M()->rollback();
                }
            }
        }
        $this->ajaxReturn($rs);
    }

    //特殊号段列表
    public function specialCodeList(){
        $id = I("id");
        switch ($id)
        {
            case '2':
                $department='B';
                break;
            case '3':
                $department='C';
                break;
            case '4':
                $department='D';
                break;
            case '5':
                $department='E';
                break;
            case '6':
                $department='F';
                break;
            default:
                    $department='A';
                break;

        }

        $where['department']=$department;
        $list = D("special_centre_code")->where($where)->field('*,getNum-remainNum as useNum')->select();
        $body = array(
            "special_list"=>$list,
            "id"=>$id ? $id:1
        );
        $this->assign($body);
        $this->display();
    }


    //特殊号段添加
    public function addsp(){
        $department = I("department");
        $year = I("year");
        $month = I("month");
        $getNum = I("getNum");
        $remark = I("remark",'');

        $rs = array("msg"=>'fail');
        if(empty($year)||$year==''||empty($getNum)||$getNum==''){
            $rs['msg'] = '信息填写不完整!';
            $this->ajaxReturn($rs);
        }
        $where['department']=$department;
        $where['year']=$year;
        $where['month']=$month;
        $list = D("special_centre_code")->field('id,getNum,remainNum,count(*) as count')->where($where)->find();
        $count = $list['count'];
        $remainNumOld =  $list['remainnum'];
        $getNumOld =  $list['getnum'];
        $id = $list['id'];
        $data = array(
            "department"=>$department,
            "year"=>$year,
            "month"=>$month,
            //"getNum"=>$getNum,
            //'remainNum'=>$getNum,
            "remark"=>$remark,
            'getDate'=>Date("Y-m-d H:i:s")
        );
        M()->startTrans();
        if($count>0){
            $remainNumNew = $remainNumOld + $getNum;
            $getNumNew = $getNumOld +$getNum;
            $data['getNum'] = $getNumNew;
            $data['remainNum'] = $remainNumNew;
            if(D("special_centre_code")->where("id=".$id)->save($data)){
                $rs['msg'] = 'succ';
                M()->commit();
            }
            else{
                $rs['msg'] = '输入信息有误';
                M()->rollback();
            }
        }else{
            $data['getNum'] = $getNum;
            $data['remainNum'] = $getNum;
            if(D("special_centre_code")->data($data)->add()){
                $rs['msg'] = 'succ';
                M()->commit();
            }
            else{
                $rs['msg'] = '输入信息有误';
                M()->rollback();
            }
        }

        $this->ajaxReturn($rs);
    }


    public function hetong(){//按收样日期算
        $begin_time = I("begin_time");
        $end_time = I("end_time");
        $where = " a.status in(5,6)";

        //来样日期(在contract表中)
        $begin_time && $where .=" and date_format(b.collectdate,'%Y-%m-%d') >='{$begin_time}'";
        $end_time && $where .=" and date_format(b.collectdate,'%Y-%m-%d') <='{$end_time}'";

        //份数
        //$countlist = ;
        //金额
        $sumlist = D("contract_flow")->alias("a")->join(C("DB_PREFIX")."contract b on a.centreno=b.centreno","LEFT")->join(C("DB_PREFIX")."test_cost c on a.centreno=c.centreno","LEFT")->where($where)->field("sum(b.testcost) as testcost,sum(c.arecord) as arecord,sum(c.brecord) as brecord,sum(c.crecord) as crecord,sum(c.drecord) as drecord,sum(c.erecord) as erecord,sum(c.frecord) as frecord,sum(c.dcopy) as dcopy,sum(c.drevise) as drevise,sum(c.dother) as dother")->find();

        $body = array(
            'sum'=>$sumlist,
            'begin_time'=>$begin_time,
            'end_time'=>$end_time,
        );
        $this->assign($body);
        $this->display();
    }

    //contract_flow: a   |  contract: b   |  test_cost: c
    public function shiji(){//按盖样日期算
        $begin_time = I("begin_time");
        $end_time = I("end_time");
        $where = " a.status in(5,6)";

        $begin_time && $where .=" and date_format(a.inner_sign_time,'%Y-%m-%d') >='{$begin_time}'";
        $end_time && $where .=" and date_format(a.inner_sign_time,'%Y-%m-%d') <='{$end_time}'";

        //份数
        $countlist =  D("contract_flow")->alias("a")->join(C("DB_PREFIX")."contract b on a.centreno=b.centreno","LEFT")->where($where)->field('collector_partment,count(collector_partment)')->group('collector_partment')->select();//->field('testdepartment,count(testdepartment)')->group('testdepartment')->select()
        //dump($countlist);
        //if($countlist[0])
        //$countlist->query("select fileformat, count(fileformat) as icount from gallery group by fileformat");
        //金额
        $sumlist = D("contract_flow")->alias("a")->join(C("DB_PREFIX")."contract b on a.centreno=b.centreno","LEFT")->join(C("DB_PREFIX")."test_cost c on a.centreno=c.centreno","LEFT")->where($where)->field("sum(b.testcost) as testcost,sum(c.arecord) as arecord,sum(c.brecord) as brecord,sum(c.crecord) as crecord,sum(c.drecord) as drecord,sum(c.erecord) as erecord,sum(c.frecord) as frecord,sum(c.dcopy) as dcopy,sum(c.drevise) as drevise,sum(c.dother) as dother")->find();
       // dump($sumlist);
        $body = array(
            'count'=>$countlist[0],
            'sum'=>$sumlist,
            'begin_time'=>$begin_time,
            'end_time'=>$end_time,
        );
        $this->assign($body);
        $this->display();
    }

}