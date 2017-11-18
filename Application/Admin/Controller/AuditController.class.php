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
    }
    //申请列表
    public function reportList(){
        $keyword = I("keyword");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        if($if_admin==1 || $user==14 ||$user==8) {
            $view="1";
        }else{
            $view="0";
        }
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        if(!empty($keyword)){
            $where="r.centreno like '%{$keyword}%'";
            $rs=D("report_feedback")->alias("r")
                ->field('if(r.status is null,-1,r.status) as sub_status,r.reason,r.create_time,r.centreno,a.clientname,a.samplename,a.testcriteria,a.testitem')
                ->join(' left join contract as a on r.centreNo=a.centreNo')
                ->where($where)
                ->limit("{$offset},{$pagesize}")
                ->order('r.create_time desc')->select();
            $count = D("report_feedback")->alias("r")->where($where)->count();
        }else{
        $rs=D("report_feedback")->alias("r")
            ->field('if(r.status is null,-1,r.status) as sub_status,r.reason,r.create_time,r.centreno,a.clientname,a.samplename,a.testcriteria,a.testitem')
            ->join(' left join contract as a on r.centreNo=a.centreNo')
            ->limit("{$offset},{$pagesize}")
            ->order('r.create_time desc')->select();
        $count = D("report_feedback")->alias("r")->count();
        }
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            'lists'=>$rs,
            'pagination'=>$pagination,
            'view'=>$view,
        );
        $this->assign($body);
        $this->display();
    }
    //允许
    public function isAllow(){
        $centreno =I("centreno");
        $where= "centreno='{$centreno}'";
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $data=array(
            'status'=>1,
        );
        if ($user==14||$if_admin==1||$user==14){//领导、批准员和超级管理员的权限
            if(D("report_feedback")->where($where)->save($data)){
                $rs['msg'] = 'succ';
            }}
        $this->ajaxReturn($rs);
    }
    //拒绝
    public function notAllow(){
        $centreno =I("centreno");
        $where= "centreno='{$centreno}'";
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $data=array(
            'status'=>2,
        );
        if ($user==14||$if_admin==1||$user==8){//领导、批准员和超级管理员的权限
            $result=D("report_feedback")->where($where)->save($data);
            if($result!==false){
                $rs['msg'] = 'succ';
            }}
        $this->ajaxReturn($rs);
    }
}