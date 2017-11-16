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
        //$role = D('common_role')->where('id='.$user)->find();
        if($user==8 || $if_admin==1 || $user==13) {//只有领导，审核人员，超级管理员才能审核
            $view="";
        }else{
            $view="disabled";
        }
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $contract_flow=M("contract_flow");//实例化对象
        $where['contract_flow.status'] = 2;
        $rs=$contract_flow->where($where)
            ->join('left join common_system_user ON contract_flow.report_user_id = common_system_user.id left join test_report on contract_flow.centreno=test_report.centreno')
            ->field('contract_flow.id,contract_flow.centreNo,contract_flow.status,contract_flow.report_time,common_system_user.name,test_report.pdf_path')
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
        //$role = D('common_role')->where('id='.$user)->find();
        if($user==8 || $if_admin==1 || $user==13) {
        $data=array(
            'status'=>4,
            'isaudit'=>1,
            'verify_user_id'=>$userid,
            'verify_time'=>date("Y-m-d H:i:s"),
        );
        if(D("contract_flow")->where("id=".$id)->save($data)){
            $rs['msg'] = 'succ';
        }}
        $this->ajaxReturn($rs);
    }
    //审核不通过，退回
    public function notApprove(){
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        //$role = D('common_role')->where('id='.$user)->find();
        if($user==8 || $if_admin==1 || $user==13) {
            $data=array(
                'status'=>7,
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

        $where['internalpass'] = 1;
        $rs=D("contract_flow as c")
            ->field('if(r.status is null,-1,r.status) as sub_status,c.*')->join('left join report_feedback as r on c.centreNo=r.centreNo')
            ->where($where)
            ->limit("{$offset},{$pagesize}")
            ->order('inner_sign_time desc,id desc')->select();
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
        $centreno =I("centreno");
        $where= "centreno='{$centreno}'";
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $data=array(
            'status'=>1,
        );
        if ($user==14||$if_admin==1){//批准员和超级管理员的权限
        if(D("report_feedback")->where($where)->save($data)){
            $rs['msg'] = 'succ';
        }}
        $this->ajaxReturn($rs);
    }
    //审批不通过
    public function notAuthorize(){
        $centreno =I("centreno");
        $where= "centreno='{$centreno}'";
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $data=array(
            'status'=>2,
        );
        if ($user==14||$if_admin==1){//批准员和超级管理员的权限
            $result=D("report_feedback")->where($where)->save($data);
            if($result!==false){
                $rs['msg'] = 'succ';
            }}
        $this->ajaxReturn($rs);
    }
//盖章签发
    public function internalIssue(){
        $centreno = I("centreno");
        $begin_time = I("begin_time");
        $end_time = I("end_time");
        $sortby=I("sortby");
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $where="a.isaudit=1";
        $orderby = "a.verify_time desc";
        if(!empty($centreno)){
            $where .=" and a.centreno='{$centreno}'";
        }
        if($sortby==1){
            $begin_time && $where .=" and date_format(a.verify_time,'%Y-%m-%d') >='{$begin_time}'";
            $end_time && $where .=" and date_format(a.verify_time,'%Y-%m-%d') <='{$end_time}'";
        }
        elseif($sortby==2){
            $begin_time && $where .=" and date_format(a.inner_sign_time,'%Y-%m-%d') >='{$begin_time}'";
            $end_time && $where .=" and date_format(a.inner_sign_time,'%Y-%m-%d') <='{$end_time}'";
        }
        if($if_admin==1 || $user==15) {
            $view="";
        }else{
            $view="disabled";
        }
        $rs = D("contract_flow")->alias("a")->join(C("DB_PREFIX")."common_system_user b on a.verify_user_id=b.id","LEFT")->join(C("DB_PREFIX")."test_report c on a.centreno=c.centreno","LEFT")
            ->where($where)
            ->field('a.id,a.status,a.internalpass,a.centreNo,a.inner_sign_time,a.inner_sign_user_id,a.verify_user_id,a.verify_time,b.name,c.tplno,c.pdf_path,c.path')
            ->limit("{$offset},{$pagesize}")
            ->order($orderby)->select();
        $data = $admin_auth['name'];
        $count = D("contract_flow")->alias("a")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            'rs'=>$rs,
            'pagination'=>$pagination,
            'view'=>$view,
            'begin_time'=>$begin_time,
            'end_time'=>$end_time,
            'sortby'=>$sortby,
            'centreno'=>$centreno,
            'data'=>$data,
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
        //$role = D('common_role')->where('id='.$user)->find();
        if($if_admin==1 || $user==15) {
            $data=array(
                'status'=>5,
                'internalpass'=>1,
                'inner_sign_time'=>date("Y-m-d H:i:s"),
                'inner_sign_user_id'=>$userid,
            );
            if(D("contract_flow")->where("id=".$id)->save($data)){
                $rs['msg'] = 'succ';
            }}
        $this->ajaxReturn($rs);
    }
    //签发不通过，退回修改
    public function doneBack(){
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        //$role = D('common_role')->where('id='.$user)->find();
        if($if_admin==1 || $user==15) {
            $data=array(
                'status'=>7,
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
        $keyword = I("keyword");
        $where = "contract_flow.status=5";
        if(!empty($keyword)){
            //查询合同编号
            $where .=" and contract_flow.centreno='{$keyword}'";
        }
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
        $rs=$contract_flow->where($where)
            ->join('common_system_user ON contract_flow.inner_sign_user_id = common_system_user.id')
            ->join('contract ON contract_flow.centreNo = contract.centreNo')
            ->field('contract_flow.id,contract_flow.centreNo,contract_flow.inner_sign_time,common_system_user.name,contract.productUnit,contract.clientSign,contract.telephone,contract.postmethod,contract.address')
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
        $type = I("type",1,'intval');
        $result = array("msg" => "fail");
        if (empty($imgurl)) {
            $result['msg'] = "无效的提交！";
            $this->ajaxReturn($result);
        }
        $data = array("path" => $imgurl, "filename" => $filename,"type"=>$type);
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
    public function pdf(){
        $centreno = I('no');
        if($centreno){
            $report = D('test_report')->where("centreno='{$centreno}'")->find();
        }
    }
}
