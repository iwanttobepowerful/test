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

    /*public function add(){
        $keyword = I("keyword");
        if(!empty($keyword)){
            //查询合同编号
            $where="centreno='{$keyword}'";
            $rs=$contract =D("contract")->where($where)->field('centreNo')->select();
        }
        $this->assign("lists",$rs);
        $this->display();
    }

    //提交修改申请
    public function doAudit(){
        $rs = array("msg"=>"fail");
        $centreno=I("centreno");
        $reason=I("reason");
        $data=array(
            'contractno'=>$centreno,
            'reason'=>$reason,
            'create_time'=>date("Y-m-d H:i:s"),
        );
        if(D("audit_report")->data($data)->add()){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }*/
    //申请列表
    public function reportList(){
        $de = I('de','A');
        $keyword = I("keyword");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $useraudit=$admin_auth['audit'];
        $if_admin = $admin_auth['super_admin'];
        if($if_admin==1 || $user==16 ) {
            $view="1";
        }else{
            $view="0";
        }
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        if($de =='A'){//内部修改申请
            if(!empty($keyword)){
                $where="r.centreno like '%{$keyword}% ' and r.if_outer=0 and r.if_report=0";
            }else{
                $where="r.if_outer=0 and r.if_report=0";
            }
        }
        elseif($de =='B'){//报告修改申请
            if(!empty($keyword)){
                $where="r.centreno like '%{$keyword}% ' and r.if_report=1";
            }else{
                $where="r.if_report=1";
            }}
        elseif($de =='C'){//外部修改申请
            if(!empty($keyword)){
                $where="r.centreno like '%{$keyword}% ' and r.if_outer=1";
            }
            else{
                $where="r.if_outer=1";
            }}
        elseif($de =='D'){//合同作废申请
            if(!empty($keyword)){
                $where="r.centreno like '%{$keyword}% ' and r.if_invalid = 1";
            }
            else{
                $where="r.if_invalid=1";
            }}
        /*if(!empty($useraudit)){
            $data=explode(',',$useraudit);
            foreach($data as $v){
                $s .="'".$v."',";
            }
            $s=substr($s,0,-1);//利用字符串截取函数消除最后一个逗号
            $where .=" and SUBSTR(r.centreno,7,1) in({$s})";
        }*/
        $rs=D("report_feedback")->alias("r")
            ->field('if(r.status is null,-1,r.status) as sub_status,r.reason,r.create_time,r.centreno,r.id as reid,a.clientname,a.samplename,a.testcriteria,a.testitem,c.*')
            ->join(' left join contract as a on r.centreNo=a.centreNo left join contract_flow as c on r.centreNo=c.centreNo')
            ->where($where)
            ->limit("{$offset},{$pagesize}")
            ->order('r.create_time desc')->select();
        if($de =='B'){
            $rs=D("report_feedback")->alias("r")
                ->field('if(r.status is null,-1,r.status) as sub_status,r.reason,r.create_time,r.centreno,r.id as reid,a.clientname,a.samplename,a.testcriteria,a.testitem,c.*,b.pdf_path,d.pdf_path as temp_pdf_path')
                ->join(' left join contract as a on r.centreNo=a.centreNo left join contract_flow as c on r.centreNo=c.centreNo')
                ->join('left join test_report as b on r.centreNo=b.centreNo left join test_report_temp as d on r.centreNo=d.centreNo')
                ->where($where)
                ->limit("{$offset},{$pagesize}")
                ->order('r.create_time desc')->select();
        }

        $count = D("report_feedback")->alias("r")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            'lists'=>$rs,
            'pagination'=>$pagination,
            'view'=>$view,
            'de'=>$de,
            'centreno'=>$keyword
        );
        $this->assign($body);
        $this->display();
    }
    //允许
    public function isAllow(){
        $id =I("id",0,'intval');
        $de=I("de");
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $contract_user_id = $admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $a=D("report_feedback")->where("id=".$id)->find();
        $centreno=$a['centreno'];
        $if_admin = $admin_auth['super_admin'];
        $data=array(
            'status'=>1,
        );

        if ($user==16||$if_admin==1) {//审核员和超级管理员的权限
            if ($de == 'A' or $de == "C") {
                if (D("report_feedback")->where("id=" . $id)->save($data)) {
                    if($d = D("test_report")->where("centreno='{$centreno}'")->find()){
                        $pdf_path='.'.$d['pdf_path'];
                        $pdf_sign = '.'.$d['pdf_sign_path'];
                        if(file_exists($pdf_path)){
                            @unlink($pdf_path);
                        }
                        if(file_exists($pdf_sign)){
                            @unlink($pdf_sign);
                        }
                    }
                    $rs['msg'] = 'succ';
                }
            } elseif($de == "D"){
                $data1 = array(
                    'status'=>-1
                );
                $data2 = array(
                    'centreNo'=>$centreno,
                    'status'=>-1,
                    'contract_user_id'=>$contract_user_id,
                    'contract_time'=>Date("Y-m-d H:i:s"),
                );
                $data_cost = array(
                    'Arecord'=>0,
                    'Brecord'=>0,
                    'Crecord'=>0,
                    'Drecord'=>0,
                    'Erecord'=>0,
                    'Frecord'=>0,
                    'Dcopy'=>0,
                    'Donline'=>0,
                    'Drevise'=>0,
                    'Dother'=>0,
                    'remark'=>'',
                    'RArecord'=>0,
                    'RBrecord'=>0,
                    'RCrecord'=>0,
                    'RDrecord'=>0,
                    'RErecord'=>0,
                    'RFrecord'=>0,
                    'RGrecord'=>0,
                    'RHrecord'=>0,
                    'idList'=>'',
                );

                $data_contract = array(
                    'testCost'=>0
                );
                M()->startTrans();
                if(D("contract_flow")->where("centreNo='".$centreno."'")->count()==0){
                    D("contract_flow")->add($data2);
                }else{
                    D("contract_flow")->where("centreNo='".$centreno."'")->save($data1);
                }
                if(D("report_feedback")->where("id=" . $id)->save($data)){
                    D("test_cost")->where("centreNo='".$centreno."'")->save($data_cost);
                    D("contract")->where("centreNo='".$centreno."'")->save($data_contract);
                    M()->commit();
                    $rs['msg'] = 'succ';
                }else{
                    M()->rollback();
                }

            }else {//$de =='B'
                $data1=array(
                    'status'=>1,//到提交审核那一步
                    'isaudit'=>0,
                    'internalpass'=>0,
                );
                M()->startTrans();
                if (D("contract_flow")->where("centreno='{$centreno}'")->save($data1) and D("report_feedback")->where("id=" . $id)->save($data)) {
                    $replace = D("test_report_temp")->where("centreno='{$centreno}'")->find();
                    $rp = array(
                        "tplno"=>$replace['tplno'],
                        "path"=>$replace['path'],
                        "doc_path"=>$replace['doc_path'],
                        "pdf_path"=>$replace['pdf_path'],
                        "modify_time"=>$replace['modify_time'],
                        "qrcode_path"=>$replace['qrcode_path'],
                        "pdf_sign_path"=>$replace['pdf_sign_path']
                    );
                    if(D('test_report')->where("centreno = '{$centreno}'")->save($rp)){
                        M()->commit();
                        $rs['msg'] = 'succ';
                    }else{
                        M()->rollback();
                    }

                }

            }
        }

        $this->ajaxReturn($rs);
    }
    //拒绝
    public function notAllow(){
        $id =I("id");
        $de =I("de");
        $where= "id='{$id}'";
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $rf=D("report_feedback")->where($where)->find();
        $arr=$rf['centreno'];
        $data=array(
            'status'=>2,
        );
        $data1=array(
            'if_edit'=>0,
        );
        if ($user==16||$if_admin==1){//审核员和超级管理员的权限
            M()->startTrans();
            $result=D("report_feedback")->where($where)->save($data);
            $result1=D("inspection_report")->where("centreno='{$arr}'")->save($data1);
            if($result!==false and $result1!==false){
                M()->commit();
                $rs['msg'] = 'succ';

            }
            else{
                M()->rollback();
            }}
        $this->ajaxReturn($rs);
    }

}

