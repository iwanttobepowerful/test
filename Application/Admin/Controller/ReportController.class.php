<?php
/**
 * Created by PhpStorm.
 * User: Ail
 * Date: 2017/10/18
 * Time: 13:45
 */
namespace Admin\Controller;
use Think\Controller;
class ReportController extends Controller
{
    public $user = null;

    public function _initialize()
    {
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active', strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active', strtolower(ACTION_NAME));
    }
    //报告审核
    public function auditReport(){
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $role = D('common_role')->where('id='.$user)->find();
        if($role['rolename']=="领导" || $if_admin==1 || $role['rolename']=="审核员") {//只有领导，审核人员，超级管理员才能审核
            $view="";
        }else{
            $view="disabled";
        }
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $contract_flow=M("contract_flow");//实例化对象
        $where['contract_flow.status'] = array('in','2,-3');
        $rs=$contract_flow->where($where)
            ->join('common_system_user ON contract_flow.report_user_id = common_system_user.id')
            ->field('contract_flow.id,contract_flow.centreNo,contract_flow.status,contract_flow.report_time,common_system_user.name')
            ->limit("{$offset},{$pagesize}")
            ->order('contract_flow.report_time desc,contract_flow.id desc')->select();
        //查找条件为已经批准并且内部尚未签发的报告
        $count = D("contract_flow")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            'rs'=>$rs,
            'pagination'=>$pagination,
            'view'=>$view,
        );
        $this->assign($body);
        $this->display();
    }
    //审核通过
    public function isApprove(){
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $role = D('common_role')->where('id='.$user)->find();
        if($role['rolename']=="领导" || $if_admin==1 || $role['rolename']=="审核员") {
        $data=array(
            'status'=>3,
            'verify_user_id'=>$userid,
            'verify_time'=>date("Y-m-d H:i:s"),
        );
        if(D("contract_flow")->where("id=".$id)->save($data)){
            $rs['msg'] = 'succ';
        }}
        $this->ajaxReturn($rs);
    }
    //审核未通过
    public function notApprove(){
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $role = D('common_role')->where('id='.$user)->find();
        if($role['rolename']=="领导" || $if_admin==1 || $role['rolename']=="审核员") {
        $data=array(
            'status'=>-3,
            'verify_user_id'=>$userid,
            'verify_time'=>date("Y-m-d H:i:s"),
        );
        if(D("contract_flow")->where("id=".$id)->save($data)){
            $rs['msg'] = 'succ';
        }}
        $this->ajaxReturn($rs);
    }
    //报告审批
    public function authorizeReport(){
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $role = D('common_role')->where('id='.$user)->find();
        if($if_admin==1 || $role['rolename']=="批准员") {
            $view="";
        }else{
            $view="disabled";
        }
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $contract_flow=M("contract_flow");//实例化对象
        $where['contract_flow.status'] = array('in','3,-4');
        $rs=$contract_flow->where($where)
            ->join('common_system_user ON contract_flow.verify_user_id = common_system_user.id')
            ->field('contract_flow.id,contract_flow.centreNo,contract_flow.status,contract_flow.verify_time,common_system_user.name')
            ->limit("{$offset},{$pagesize}")
            ->order('contract_flow.verify_time desc,contract_flow.id desc')->select();
        //查找条件为已经批准并且内部尚未签发的报告
        $count = D("contract_flow")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            'rs'=>$rs,
            'pagination'=>$pagination,
            'view'=>$view,
        );
        $this->assign($body);
        $this->display();
    }
    //审批通过
    public function isAuthorize(){
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $role = D('common_role')->where('id='.$user)->find();
        if($if_admin==1 || $role['rolename']=="批准员") {
        $data=array(
            'status'=>4,
            'approve_time'=>date("Y-m-d H:i:s"),
            'approve_user_id'=>$userid,
        );
        if(D("contract_flow")->where("id=".$id)->save($data)){
            $rs['msg'] = 'succ';
        }}
        $this->ajaxReturn($rs);
    }
    //审批未通过
    public function  notAuthorize(){
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $role = D('common_role')->where('id='.$user)->find();
        if($if_admin==1 || $role['rolename']=="批准员") {
        $data=array(
            'status'=>-4,
            'approve_time'=>date("Y-m-d H:i:s"),
            'approve_user_id'=>$userid,
        );
        if(D("contract_flow")->where("id=".$id)->save($data)){
            $rs['msg'] = 'succ';
        }}
        $this->ajaxReturn($rs);
    }
    //盖章人员签发
    public function internalIssue(){
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $role = D('common_role')->where('id='.$user)->find();
        if($if_admin==1 || $role['rolename']=="盖章人员") {
            $view="";
        }else{
            $view="disabled";
        }
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $contract_flow=M("contract_flow");//实例化对象
        $where['contract_flow.status']=4;
        $rs=$contract_flow->where($where)
            ->join('common_system_user ON contract_flow.approve_user_id = common_system_user.id')
            ->field('contract_flow.id,contract_flow.centreNo,contract_flow.approve_time,common_system_user.name')
            ->limit("{$offset},{$pagesize}")
            ->order('contract_flow.approve_time desc,contract_flow.id desc')->select();
        //查找条件为已经批准并且内部尚未签发的报告
        $count = D("contract_flow")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            'rs'=>$rs,
            'pagination'=>$pagination,
            'view'=>$view,
        );
        $this->assign($body);
        $this->display();
    }
    //签发按钮功能实现

    public function doUpd(){
// 要修改的数据对象属性赋值
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $role = D('common_role')->where('id='.$user)->find();
        if($if_admin==1 || $role['rolename']=="盖章人员") {
        $data=array(
            'status'=>5,
            'inner_sign_time'=>date("Y-m-d H:i:s"),
            'inner_sign_user_id'=>$userid,
        );
        if(D("contract_flow")->where("id=".$id)->save($data)){
            $rs['msg'] = 'succ';
        }}
        $this->ajaxReturn($rs);
    }

    //外部签发
    public function externalIssue(){
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $role = D('common_role')->where('id='.$user)->find();
        if($if_admin==1 || $role['rolename']=="前台人员") {//只有前台，超级管理员才能签发
            $view="";
        }else{
            $view="disabled";
        }
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $contract_flow=M("contract_flow");//实例化对象
        $where['contract_flow.status']=5;
        $rs=$contract_flow->where($where)
            ->join('common_system_user ON contract_flow.inner_sign_user_id = common_system_user.id')
            ->join('contract ON contract_flow.centreNo = contract.centreNo')
            ->field('contract_flow.id,contract_flow.centreNo,contract_flow.inner_sign_time,common_system_user.name,contract.productUnit,contract.clientSign,contract.telephone')
            ->limit("{$offset},{$pagesize}")
            ->order('contract_flow.inner_sign_time desc,contract_flow.id desc')->select();
        //查找条件为已经批准并且内部尚未签发的报告
        $count = D("contract_flow")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            'rs'=>$rs,
            'pagination'=>$pagination,
            'view'=>$view,
        );
        $this->assign($body);
        $this->display();
    }
    public function passUpd(){
// 要修改的数据对象属性赋值
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $role = D('common_role')->where('id='.$user)->find();
        if($if_admin==1 || $role['rolename']=="前台人员") {
        $data=array(
            'status'=>6,
            'external_sign_time'=>date("Y-m-d H:i:s"),
            'external_sign_user_id'=>$userid,
        );
        if(D("contract_flow")->where("id=".$id)->save($data)){
            $rs['msg'] = 'succ';
        }}
        $this->ajaxReturn($rs);
    }

    //报告模板
    public function templateReport()
    {
        $page = I("p", 'int');
        $pagesize = 10;
        if ($page <= 0) $page = 1;
        $offset = ($page - 1) * $pagesize;
        $result = D("tpl")->limit("{$offset},{$pagesize}")->select();
        $count = D("tpl")->count();
        $Page = new \Think\Page($count, $pagesize);
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination = $Page->show();// 分页显示输出
        $body = array(
            'lists' => $result,
            'pagination' => $pagination,
        );
        $this->assign($body);
        $this->display();
    }

    public function doUploadReport()
    {
        $id = I("id", 0, 'intval');
        $imgurl = I("imgurl");
        $filename = I("filename");
        $result = array("msg" => "fail");
        if (empty($imgurl)) {
            $result['msg'] = "无效的提交！";
            $this->ajaxReturn($result);
        }
        $data = array("path" => $imgurl, "filename" => $filename);
        $report = D("tpl")->where("id=" . $id)->find();
        if ($report) {
            if (D("tpl")->where("id=" . $report['id'])->save($data)) {
                $result['msg'] = 'succ';
            }
        } else {
            if (D("tpl")->data($data)->add()) {
                $result['msg'] = 'succ';
            }
        }
        $this->ajaxReturn($result);
    }
//修改
    public function updateReport()
    {

        $id = I("id", 0, 'intval');
        if ($id) {
            $report = D('tpl')->where("id=" . $id)->find();
        }

        $body = array(
            'report' => $report,
        );
        $this->assign($body);
        $this->display();
    }
//删除
    public function doDeleteReport()
    {
        $id = I("id", 0, 'intval');
        $rs = array("msg" => "fail");
        if (D("tpl")->where("id=" . $id)->delete()) {
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }
}
