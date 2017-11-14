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
        $where="contractno like '%{$keyword}%'";
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        //$role = D('common_role')->where('id='.$user)->find();
        if($user==8 || $if_admin==1){//只有领导，超级管理员才能审核
            $view="";
        }else{
            $view="disabled";
        }
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $audit_report=M("audit_report");//实例化对象
        $rs=$audit_report->where($where)
            ->join('contract ON audit_report.contractno=contract.centreno')
            ->order('create_time desc,audit_report.id desc')->limit("{$offset},{$pagesize}")->select();
        $count = D("audit_report")->where($where)->count();
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
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $role = D('common_role')->where('id='.$user)->find();
        if($role['rolename']=="领导" || $if_admin==1){//只有领导，超级管理员才能审核
            $data=array(
                'status'=>1,
                'modify_time'=>date("Y-m-d H:i:s"),
            );
            if(D("audit_report")->where("id=".$id)->save($data)){
                $rs['msg'] = 'succ';
            }}
        $this->ajaxReturn($rs);
    }
    //拒绝
    public function notAllow(){
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $role = D('common_role')->where('id='.$user)->find();
        if($role['rolename']=="领导" || $if_admin==1){//只有领导，超级管理员才能审核
            $data=array(
                'status'=>2,
                'modify_time'=>date("Y-m-d H:i:s"),
            );
            if(D("audit_report")->where("id=".$id)->save($data)){
                $rs['msg'] = 'succ';
            }}
        $this->ajaxReturn($rs);
    }
}