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
        $useraudit= $admin_auth['audit'];
        $if_G1 = 0;
        $if_G2 = 0;//能审核的部门是否包含G1\G2，默认没有
        if($user==18){
            $de=I("de",'A');
            if ($de=='A') {
                $where = "contract_flow.status = 2 ";
            }
            elseif ($de=='B'){
                $where =" contract_flow.status in('3','4','5','6')";
            }
        }
        else{
            $where="contract_flow.status = 2";
        }
        if(!empty($useraudit)){

            //先检查是否有G1\G2
            if(strstr($useraudit,'G1')){
               $if_G1 = 1;
                $useraudit = str_replace('1','',$useraudit);
            }
            if(strstr($useraudit,'G2')){
                $useraudit = str_replace('2','',$useraudit);
                $if_G2 = 1;
            }
            $data=explode(',',$useraudit);
            foreach($data as $v){
                $s .="'".$v."',";
            }
            $s=substr($s,0,-1);//利用字符串截取函数消除最后一个逗号
            if ($if_G1 == 0 and $if_G2 == 1){//只选了G2
                $where .= " and ((SUBSTR(contract_flow.centreno,7,1) in({$s}) and SUBSTR(contract_flow.centreno,7,1) !='G') or (SUBSTR(contract_flow.centreno,7,1) ='G' and SUBSTR(contract_flow.centreno,9,3) > 500))";
            }
            elseif ($if_G1 == 1 and $if_G2 == 0){
                $where .=" and ((SUBSTR(contract_flow.centreno,7,1) in({$s}) and SUBSTR(contract_flow.centreno,7,1) !='G') or (SUBSTR(contract_flow.centreno,7,1) ='G' and SUBSTR(contract_flow.centreno,9,3) <= 500))";
            }
            else{
                $where .=" and SUBSTR(contract_flow.centreno,7,1) in({$s})";
            }

        }//不选的话都能看到
        //elseif(($user==17 or $user==13) and empty($useraudit)){//要是角色是审核员，但是什么都不选，默认看不到
            //$where="contract_flow.status = -100";
       // }
        if($user==8 || $if_admin==1 || $user==13 || $user==18) {//只有领导，审核人员，超级管理员，审核批准员才能审核
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
            ->join('left join common_system_user ON contract_flow.uploadreport_user_id = common_system_user.id left join test_report on contract_flow.centreno=test_report.centreno left join contract as a on contract_flow.centreno=a.centreno')
            ->field('contract_flow.ifback,contract_flow.gz_back,contract_flow.sh_back,contract_flow.bz_back,contract_flow.id,contract_flow.centreNo,contract_flow.status,contract_flow.uploadreport_time,common_system_user.name,test_report.pdf_path,a.centreno1,a.centreno2,a.centreno3')
            ->limit("{$offset},{$pagesize}")
            ->order('case when (contract_flow.status = 2 and contract_flow.back_time) then contract_flow.back_time else contract_flow.report_time end desc')->select();
        if($rs){
            $con_list = array();//反馈
            foreach($rs as $contract){
                array_push($con_list,"'".$contract['centreno']."'");
            }
            $centreno_str = implode(',',$con_list);
            $no_feed_list = D('report_feedback')->where(' id in (select max(id) from report_feedback where centreNo in ('.$centreno_str.') group by centreNo)')->group('centreNo')->select();
            $con_list = array();
            if($no_feed_list){
                foreach($no_feed_list as $no_feed){
                    $con_list[$no_feed['centreno']]	= $no_feed;
                }
            }
            foreach($rs as $key=>$val){
                if($con_list[$val['centreno']]){
                    $val['sub_status'] = $con_list[$val['centreno']]['status'];
                    $val['if_outer'] = $con_list[$val['centreno']]['if_outer'];
                    $val['if_report'] = $con_list[$val['centreno']]['if_report'];
                }else{
                    $val['sub_status'] = -1;
                    $val['if_outer'] = -1;
                    $val['if_report'] = -1;
                }
                $rs[$key] = $val;
            }
        }
        $count = D("contract_flow")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        if($user==18){
            $body = array(
                'rs'=>$rs,
                'de'=>$de,
                'pagination'=>$pagination,
                'view'=>$view,
                'user'=>$user
            );
        }
        else{
            $body = array(
                'rs'=>$rs,
                'pagination'=>$pagination,
                'view'=>$view,
                'user'=>$user
            );
        }

        $this->assign($body);
        $this->display();
    }
    //审核通过
    public function isApprove(){
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $userid=$admin_auth['id'];
        $if_admin = $admin_auth['super_admin'];
        $check = D('contract_flow')->where('id='.$id)->find();
        $centreno =$check['centreno'];
        $where1 = "centreno = '$centreno' and type = 2";
        //$role = D('common_role')->where('id='.$user)->find();
        if($user==8 || $if_admin==1 || $user==13 || $user==18) {
        $data=array(
            'status'=>4,
            'isaudit'=>1,
            'ifback'=>0,
            'back_time'=>null,
            'sh_back'=>0,
            'verify_user_id'=>$userid,
            'verify_time'=>date("Y-m-d H:i:s"),
        );
        M()->startTrans();
        if(D("contract_flow")->where("id=".$id)->save($data)){
            if( D('back_report')->where($where1)->find()){
                $tmp = D('back_report')->where($where1)->field('img_path,pic_path')->select();
                foreach ($tmp as $value){
                    $temp = '.'.$value['img_path'];
                    if (file_exists($temp)) {
                        @unlink($temp);
                    }
                    $temp1 = '.'.$value['pic_path'];
                    if (file_exists($temp1)) {
                        @unlink($temp1);
                    }
                }
                D('back_report')->where($where1)->delete();
            }

            M()->commit();
            $rs['msg'] = 'succ';
        }
        else{
           M()->rollback();
        }
        }
        $this->ajaxReturn($rs);
    }
    //审核不通过，退回
    //清空记录
    public function clean(){
        $rs = array("msg"=>"fail");
        $centreno = I('centreno');
        $type = 2;
        $where ="centreno = '$centreno' and type = $type";
        if(D('back_report')->where($where)->find()){
            $tmp = D('back_report')->where($where)->field('img_path,pic_path')->select();
            foreach ($tmp as $value){
                $temp = '.'.$value['img_path'];
                if (file_exists($temp)) {
                    @unlink($temp);
                }
                $temp1 = '.'.$value['pic_path'];
                if (file_exists($temp1)) {
                    @unlink($temp1);
                }
            }
        }
        $result = D('back_report')->where($where)->delete();
        if($result !== false){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }
    //审核退回单子填写
    public function auditBack(){
        $centreno = I('id');
        $type = I('type');
        $where ="centreno = '$centreno' and type = $type";
        $result =D('back_report')->where($where)->select();
        $body = array(
          'rs'=>$result,
          'centreno'=>$centreno,
          'type'=>$type
        );
        $this->assign($body);
        $this->display();
    }

    public function auditBackSave(){
        $rs = array("msg"=>"fail");
        $centreno =I("centreno");
        $type = I("type");
        $reason = I('reason');
        $img_path = I('imgurl');
        $pic_path = str_replace("_thumb",'',$img_path);
        $data = array(
            'centreNo'=>$centreno,
            'type'=>$type,
            'back_reason'=>$reason,
            "img_path"=>$img_path,
            "pic_path"=>$pic_path,
            'back_time'=>date("Y-m-d H:i:s")
        );
        M()->startTrans();
        if(D("back_report")->add($data)){
            M()->commit();
            $rs['msg'] = 'succ';
        }
        else{
            M()->rollback();
        }
        $this->ajaxReturn($rs);
    }
    public function notApprove(){
        $centreno =I("centreno");
        $sortby= I('sortby');
        $data1['back_to'] = $sortby;
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        //$role = D('common_role')->where('id='.$user)->find();
        if($user==8 || $if_admin==1 || $user==13 || $user==18) {
            if($sortby ==7){
                $data=array(
                    'status'=>7,
                    'ifback'=>2,
                    'sh_back'=>1,
                    'back_time'=>date("Y-m-d H:i:s"),
                    'verify_user_id'=>$userid,
                    'verify_time'=>date("Y-m-d H:i:s"),

                );
            }
            else{
                $data=array(
                    'status'=>8,
                    'ifback'=>2,
                    'sh_back'=>1,
                    'back_time'=>date("Y-m-d H:i:s"),
                    'verify_user_id'=>$userid,
                    'verify_time'=>date("Y-m-d H:i:s"),

                );
            }

            M()->startTrans();
            if(D("contract_flow")->where("centreno = '$centreno'")->save($data)){
                D("back_report")->where("centreno = '$centreno' and type = 2")->save($data1);
                M()->commit();
                $rs['msg'] = '退回成功！';
            }
            else{
                M()->rollback();
            }
        }
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
        $de = I("de",'A');
        $centreno = I("centreno");
        $centreno = trim($centreno);
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
        $where="a.isaudit=1";//审核过了
        $orderby = "a.verify_time desc";
        if ($de=='A'){
            $where .= " and a.status !=5 and a.status !=6";
        }
        elseif ($de=='B'){
            $where .= " and a.status=5";
        }
        elseif ($de=='C'){
            $where .= " and a.status=6";
        }
        if(!empty($centreno)){
            $where .=" and a.centreno like'%{$centreno}%'";
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
        $rs = D("contract_flow")->alias("a")->join(C("DB_PREFIX")."common_system_user b on a.verify_user_id=b.id","LEFT")->join(C("DB_PREFIX")."test_report c on a.centreno=c.centreno","LEFT")->join(C("DB_PREFIX")."common_system_user f on a.inner_sign_user_id=f.id","LEFT")->join(C("DB_PREFIX")."common_system_user y on a.external_sign_user_id=y.id","LEFT")->join(C("DB_PREFIX")."contract as con on a.centreno=con.centreno","LEFT")
            ->where($where)
            ->field('a.id,a.status,a.internalpass,a.centreNo,a.inner_sign_time,a.external_sign_time,a.inner_sign_user_id,a.verify_user_id,a.verify_time,a.ifback,a.bz_back,a.sh_back,a.gz_back,b.name,c.tplno,c.pdf_path,c.path,f.name as innername,y.name as externalname,con.centreno1,con.centreno2,con.centreno3')
            ->limit("{$offset},{$pagesize}")
            ->order($orderby)->select();
        if($rs){
            $con_list = array();//反馈
            foreach($rs as $contract){
                array_push($con_list,"'".$contract['centreno']."'");
            }
            $centreno_str = implode(',',$con_list);
            $no_feed_list = D('report_feedback')->where(' id in (select max(id) from report_feedback where centreNo in ('.$centreno_str.') group by centreNo)')->group('centreNo')->select();
            $con_list = array();
            if($no_feed_list){
                foreach($no_feed_list as $no_feed){
                    $con_list[$no_feed['centreno']]	= $no_feed;
                }
            }
            foreach($rs as $key=>$val){
                if($con_list[$val['centreno']]){
                    $val['sub_status'] = $con_list[$val['centreno']]['status'];
                    $val['if_outer'] = $con_list[$val['centreno']]['if_outer'];
                    $val['if_report'] = $con_list[$val['centreno']]['if_report'];
                }else{
                    $val['sub_status'] = -1;
                    $val['if_outer'] = -1;
                    $val['if_report'] = -1;
                }
                $rs[$key] = $val;
            }
        }
        //dump($where);die;
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
            'de'=>$de,
        );

        $this->assign($body);
        $this->display();
    }
    //盖章签发通过

    public function doUpd(){
// 要修改的数据对象属性赋值
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
       $check = D('contract_flow')->where('id='.$id)->find();
       $centreno =$check['centreno'];
       $where1 = "centreno = '$centreno' and type = 1";
        if($if_admin==1 || $user==15) {
            $data=array(
                'status'=>5,
                'internalpass'=>1,
                'inner_sign_time'=>date("Y-m-d H:i:s"),
                'inner_sign_user_id'=>$userid,
                'gz_back'=>0,
                'ifback'=>0,
                'back_time'=>null,
            );
            M()->startTrans();
            if(D("contract_flow")->where("id=".$id)->save($data)){
                if(D('back_report')->where($where1)->find()){
                    D('back_report')->where($where1)->delete();//通过记录全清
                }
                M()->commit();
                $rs['msg'] = 'succ';
            }
        else{
                M()->rollback();
        }}
        $this->ajaxReturn($rs);
    }
    //盖章签发不通过，退回修改
    public function doneBack(){
        $id =I("id",0,'intval');
        $sortby= I('sortby');
        $reason = I('reason');
        $check = D("contract_flow")->where("id=".$id)->find();
        $centreno = $check['centreno'];
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        //$role = D('common_role')->where('id='.$user)->find();
        if($if_admin==1 || $user==15) {
            if($sortby == 0){
                $data=array(
                    'status'=>3,//退回前台费用
                    'inner_sign_time'=>date("Y-m-d H:i:s"),
                    'inner_sign_user_id'=>$userid,
                    'back_time'=>date("Y-m-d H:i:s"),
                    'gz_back'=>1,
                    'ifback'=>1
                );
                $data1=array(
                    'type'=>1,//退回前台费用
                    'back_reason'=>$reason,
                    'back_time'=>date("Y-m-d H:i:s"),
                    'back_to'=>0,
                    'centreNo'=>$centreno
                );
            }
            elseif($sortby == 1){
                $data=array(
                    'status'=>7,//退回实验员
                    'back_reason'=>$reason,
                    'inner_sign_time'=>date("Y-m-d H:i:s"),
                    'inner_sign_user_id'=>$userid,
                    'back_time'=>date("Y-m-d H:i:s"),
                    'gz_back'=>1,
                    'ifback'=>1
                );
                $data1=array(
                    'type'=>1,//退回实验员
                    'back_reason'=>$reason,
                    'back_time'=>date("Y-m-d H:i:s"),
                    'back_to'=>7,
                    'centreNo'=>$centreno
                );
            }
            elseif($sortby == 2){
                $data=array(
                    'status'=>8,//退回编制员
                    'back_reason'=>$reason,
                    'inner_sign_time'=>date("Y-m-d H:i:s"),
                    'inner_sign_user_id'=>$userid,
                    'back_time'=>date("Y-m-d H:i:s"),
                    'gz_back'=>1,
                    'ifback'=>1
                );
                $data1=array(
                    'type'=>1,//退回前台费用
                    'back_reason'=>$reason,
                    'back_time'=>date("Y-m-d H:i:s"),
                    'back_to'=>8,
                    'centreNo'=>$centreno
                );
            }
            elseif($sortby ==3){
                $data=array(
                    'status'=>2,//退回审核员
                    'back_reason'=>$reason,
                    'inner_sign_time'=>date("Y-m-d H:i:s"),
                    'inner_sign_user_id'=>$userid,
                    'back_time'=>date("Y-m-d H:i:s"),
                    'gz_back'=>1,
                    'ifback'=>1
                );
                $data1=array(
                    'type'=>1,//退回前台费用
                    'back_reason'=>$reason,
                    'back_time'=>date("Y-m-d H:i:s"),
                    'back_to'=>2,
                    'centreNo'=>$centreno
                );
            }
            $where = "centreno = '{$centreno}' and type = 1";
            M()->startTrans();
            if(D("back_report")->where($where)->find()){
                D("back_report")->where($where)->delete();//先把之前的记录清掉
            }
            if(D("contract_flow")->where("id=".$id)->save($data) and D("back_report")->add($data1)){
                M()->commit();
                $rs['msg'] = '操作成功！';
            }
            else{
                M()->rollback();
            }
        }
        $this->ajaxReturn($rs);
    }
    //盖章价格审核
    public function priceList(){
        $centreno=I("id");//获取中心编号
        $where= "centreno='{$centreno}'";
        $rs=D("test_cost")->where($where)->find();
        $cost=D("contract")->where($where)->field('testcost')->find();

        //$arr = array(
          //'a'=>array(1,2,3,4),
            //'b'=>array(5,6,7,8),
        //);
        //$arrstring = serialize($arr);
        //dump($arrstring);
        $attr = unserialize($rs['idlist']);
        //$attr = unserialize($arrstring);
        //dump($attr);
        if(!empty($attr['a'])) {
            $where1['id'] = array('in', $attr['a']);
            $a = D("test_fee")->where($where1)->select();
        }
        if(!empty($attr['b'])) {
            $where2['id'] = array('in', $attr['b']);
            $b = D("test_fee")->where($where2)->select();
        }
        if(!empty($attr['c'])) {
            $where3['id'] = array('in', $attr['c']);
            $c = D("test_fee")->where($where3)->select();
        }
        if(!empty($attr['d'])) {
            $where4['id'] = array('in', $attr['d']);
            $d = D("test_fee")->where($where4)->select();
        }
        if(!empty($attr['e'])) {
            $where5['id'] = array('in', $attr['e']);
            $e = D("test_fee")->where($where5)->select();
        }
        if(!empty($attr['f'])) {
            $where6['id'] = array('in', $attr['f']);
            $f = D("test_fee")->where($where6)->select();
        }
        if(!empty($attr['g1'])) {
            $where7['id'] = array('in', $attr['g1']);
            $g1 = D("test_fee")->where($where7)->select();
        }
        if(!empty($attr['g2'])) {
            $where8['id'] = array('in', $attr['g2']);
            $g2 = D("test_fee")->where($where8)->select();
        }
        if(!empty($attr['h'])) {
            $where9['id'] = array('in', $attr['h']);
            $h = D("test_fee")->where($where9)->select();
        }
        //dump($a);
        $body=array(
            'one'=>$rs,
            'cost'=>$cost,
            'a'=>$a,
            'b'=>$b,
            'c'=>$c,
            'd'=>$d,
            'e'=>$e,
            'f'=>$f,
            'g1'=>$g1,
            'g2'=>$g2,
            'h'=>$h
        );
        $this->assign($body);
        $this->display();
    }
    //外部签发
    public function externalIssue(){
        $keyword = I("keyword");
        $keyword = trim($keyword);
        $begin_time = I("begin_time");
        $end_time = I("end_time");
        $where = "(c.status=5 or c.status=6)";
        if(!empty($keyword)){
            //查询合同编号
            $where .=" and c.centreno like '%{$keyword}%'";
        }
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $department = $admin_auth['department'];
        if($user==8 || $user==15 || $user==13 || $if_admin==1){
            //
        }else{
            //判断G1/G2的特殊化
            if($department == 'G1'){
                $where .= " and SUBSTR(c.centreno,7,1) = 'G' and SUBSTR(c.centreno,9,3) <='500'";
            }elseif ($department == 'G2'){
                $where .= " and SUBSTR(c.centreno,7,1) = 'G' and SUBSTR(c.centreno,9,3) >'500'";
            }
            else{
                $where .= " and SUBSTR(c.centreno,7,1) = '{$department}'";
            }
        }
        if(!empty($begin_time)){
            $where .=" and date_format(c.external_sign_time,'%Y-%m-%d') >='{$begin_time}'";
        }
        if(!empty($end_time)){
            $where .=" and date_format(c.external_sign_time,'%Y-%m-%d') <='{$end_time}'";
        }
        if($if_admin==1 || $user==7) {//只有前台，超级管理员才能签发
            $view="";
        }else{
            $view="disabled";
        }
        //dump($where);die;
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $rs=D("contract_flow as c")->where($where)
            ->join('common_system_user as b ON c.inner_sign_user_id = b.id')
            ->join('contract as a ON c.centreno = a.centreno')
            ->join('test_report as t on c.centreno=t.centreno')
            ->field('c.id,c.status,c.centreno,c.inner_sign_time,b.name,a.productUnit,a.clientSign,a.telephone,a.postmethod,a.address,a.centreno1,a.centreno2,a.centreno3,t.pdf_sign_path')
            ->limit("{$offset},{$pagesize}")
            ->order('c.status!=5,c.status=5,c.inner_sign_time desc,c.id desc')->select();
            //->order('c.inner_sign_time desc,c.id desc')->select();
        //查找条件为已经批准并且外部尚未签发的报告
        if($rs){
            $con_list = array();//反馈
            foreach($rs as $contract){
                array_push($con_list,"'".$contract['centreno']."'");
            }
            $centreno_str = implode(',',$con_list);
            $no_feed_list = D('report_feedback')->where(' id in (select max(id) from report_feedback where centreNo in ('.$centreno_str.') group by centreNo)')->group('centreNo')->select();
            $con_list = array();
            if($no_feed_list){
                foreach($no_feed_list as $no_feed){
                    $con_list[$no_feed['centreno']]	= $no_feed;
                }
            }
            foreach($rs as $key=>$val){
                if($con_list[$val['centreno']]){
                    $val['sub_status'] = $con_list[$val['centreno']]['status'];
                    $val['if_outer'] = $con_list[$val['centreno']]['if_outer'];
                    $val['if_report'] = $con_list[$val['centreno']]['if_report'];
                }else{
                    $val['sub_status'] = -1;
                    $val['if_outer'] = -1;
                    $val['if_report'] = -1;
                }
                $rs[$key] = $val;
            }
        }
        $count = D("contract_flow as c")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            'rs'=>$rs,
            'pagination'=>$pagination,
            'view'=>$view,
            'begin_time'=>$begin_time,
            'end_time'=>$end_time,
        );
        $this->assign($body);
        $this->display();
    }
    public function passUpd(){
// 要修改的数据对象属性赋值
        $centreno =I("id");
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        if($if_admin==1 || $user==7) {
            $where="centreno='{$centreno}'";
        $data=array(
            'status'=>6,
            'external_sign_time'=>date("Y-m-d H:i:s"),
            'external_sign_user_id'=>$userid,
        );
        $find=D("inspection_report")->where($where)->find();
        M()->startTrans();
        if(!empty($find)){
            $data1=array('if_edit'=>0);
            D("inspection_report")->where($where)->save($data1);
        }
        if(D("contract_flow")->where($where)->save($data)){
            M()->commit();
            $rs['msg'] = 'succ';
        }else{
            M()->rollback();
        }
        }
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
        $subtype=I("subtype",0,'intval');
        $result = array("msg" => "fail");
        if (empty($imgurl)) {
            $result['msg'] = "无效的提交！";
            $this->ajaxReturn($result);
        }
        $data = array("path" => $imgurl, "filename" => $filename,"type"=>$type,"subtype"=>$subtype);
        $report = D("tpl")->where("id=" . $id)->find();
        M()->startTrans();
        if ($report) {
            if (D("tpl")->where("id=" . $report['id'])->save($data)) {
                M()->commit();
                $result['msg'] = 'succ';
            }
        } else {
            if (D("tpl")->data($data)->add()) {
                M()->commit();
                $result['msg'] = 'succ';
            }
            else{
                M()->rollback();
                $result['msg'] = 'fail';

            }
        }
        $this->ajaxReturn($result);
    }
    public function updateReport()
    {

        $id = I("id", 0, 'intval');
        if ($id) {
            $report = D('tpl')->where("id=" . $id)->find();
            $type=$report['type'];
            $subtype=$report['subtype'];
        }

        $body = array(
            'report' => $report,
            'type'=>$type,
            'subtype'=>$subtype
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

    //报告编制退回实验员
    public function bianzhiBack()
    {
        $reason = I('reason');
        if(empty($reason)){
            $rs = array("msg" => "fail");
            $this->ajaxReturn($rs);

        }
        $centreno = I('centreno');
        $where = "centreno='{$centreno}'";
        $where1 = "centreno='{$centreno}' and type =3";
        $rs = array("msg" => "fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid = $admin_auth['id'];
        $user = $admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $data = array(
            'status' => 7,
            'ifback'=>3,
            'bz_back'=>1,
            'report_user_id'=>$userid,
            'report_time'=>date("Y-m-d H:i:s"),
            'back_time'=>date("Y-m-d H:i:s"),

        );
        $data1 = array(
            'centreNo'=>$centreno,
            'type'=>'3',
            'back_reason' => $reason,
            'back_time'=>date("Y-m-d H:i:s"),
            'back_to'=>7
        );

        M()->startTrans();
        //检查是否是还在修改已通过的状态
        $no_feed_list = D('report_feedback')->where("id in (select max(id) from report_feedback where if_report=1 and centreno = '{$centreno}') ")->find();
        $feed_id = $no_feed_list['id'];
        if( $no_feed_list['status'] == 1){
            $update_data =array(
                'status'=>3
            );
            D('report_feedback')->where('id = '.$feed_id)->save($update_data);
        }
        if(D("back_report")->where($where1)->find()){
            D("back_report")->where($where1)->delete();//之前的记录先清掉
        }
        if (D("contract_flow")->where($where)->save($data) and D("back_report")->add($data1)) {
            M()->commit();
            $rs['msg'] = '退回成功！';
        } else {
            M()->rollback();
        }
        $this->ajaxReturn($rs);

    }
    //退回原因显示框
    public function backShowPage(){
        $centreno = I('centreno');
        $data = D('contract_flow as c')->where("c.centreno ='{$centreno}'")
            ->join('LEFT JOIN common_system_user as a ON c.inner_sign_user_id = a.id LEFT JOIN common_system_user as d ON c.verify_user_id = d.id left join common_system_user as e ON c.report_user_id = e.id LEFT JOIN back_report as b ON c.centreNo = b.centreNo')
            ->field("b.*,c.inner_sign_user_id,c.verify_user_id,c.report_user_id,a.name as innername,d.name as verifyname,e.name as reportname")
            ->order("b.id desc")->select();
        $body = array(
        'list'=>$data
        );
        $this->assign($body);
        $this->display();
    }
}
