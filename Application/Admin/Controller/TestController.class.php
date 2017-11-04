<?php

namespace Admin\Controller;
use Think\Controller;
class TestController extends Controller{
    public $user = null;

    public function _initialize(){//系统Action类提供了一个初始化方法_initialize接口，可以用于扩展需要，_initialize方法会在所有操作方法调用之前首先执行
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }

    //工作通知单查询
    public function infoList(){
        $keyword = I("keyword");//获取参数
        $where= "centreno='{$keyword}'";
        $result=M('work_inform_form')->where($where)->field("id,centreno,samplename")->select();
        $body=array(
            'lists'=>$result,

        );
        $this->assign($body);
        $this->display();
    }

    //工作通知单显示
    public function infoShow(){
        $keyword = I("id");//获取参数
        $where= "centreNo='{$keyword}'";

        $work_inform_form=M('work_inform_form');
        $result=$work_inform_form->where($where)->select();
        $body=array(
            'lists'=>$result,
        );
        $this->assign($body);
        $this->display();
    }



    //抽样单查询
    public function sampleList(){
        $keyword = I("keyword");//获取参数
        $where= "centreno='{$keyword}'";
        $result=M('sampling_form')->where($where)->field("id,centreno,clientname,productunit")->select();

        $body=array(
            'lists'=>$result,

        );
        $this->assign($body);
        $this->display();
    }

    //抽样单显示
    public function sampleShow(){
        $keyword = I("id");//获取参数
        $where= "centreno='{$keyword}'";
        $result=M('sampling_form')->where($where)->select();
		
		$admin_auth = session("admin_auth");
		$if_admin = $admin_auth['super_admin'];
		$roleid = $admin_auth['gid'];
		
		$role = D('common_role')->where('id='.$roleid)->find();
		if($role['rolename']=="领导" || $if_admin==1 || $role['rolename']=="前台人员"){
			$if_edit = 1;	
		}else{
			$if_edit = 0;	
		}

        $simsigndateyear = $result[0]['simsigndate'] ? date("Y",strtotime($result[0]['simsigndate'])):"";
        $simsigndatemonth =  $result[0]['simsigndate'] ? date("m",strtotime($result[0]['simsigndate'])):"";
        $simsigndateday =  $result[0]['simsigndate'] ? date("d",strtotime($result[0]['simsigndate'])):"";
        array_push($result[0],$simsigndateyear);
        array_push($result[0],$simsigndatemonth);
        array_push($result[0],$simsigndateday);

        $seasingdateyear =  $result[0]['seasingdate'] ? date("Y",strtotime($result[0]['seasingdate'])):"";
        $seasingdatemonth =  $result[0]['seasingdate'] ? date("m",strtotime($result[0]['seasingdate'])):"";
        $seasingdateday =  $result[0]['seasingdate'] ? date("d",strtotime($result[0]['seasingdate'])):"";
        array_push($result[0],$seasingdateyear);
        array_push($result[0],$seasingdatemonth);
        array_push($result[0],$seasingdateday);

        $entsigndateyear =  $result[0]['entsigndate'] ? date("Y",strtotime($result[0]['entsigndate'])):"";
        $entsigndatemonth =  $result[0]['entsigndate'] ? date("m",strtotime($result[0]['entsigndate'])):"";
        $entsigndateday =  $result[0]['entsigndate'] ? date("d",strtotime($result[0]['entsigndate'])):"";
        array_push($result[0],$entsigndateyear);
        array_push($result[0],$entsigndatemonth);
        array_push($result[0],$entsigndateday);

        $body=array(
            'lists'=>$result,
			'if_edit'=>$if_edit

        );
        $this->assign($body);
        $this->display();
    }
//检测记录
    public function recordPicture(){
        $keyword = I("keyword");//获取参数
        $where= "contract_flow.centreno like '%{$keyword}%'";
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        if ($user==10 || $if_admin ==1) {//只有报告编制员，超级管理员才能操作
            $view="";
        }
        else
        {$view="hidden";
        }
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $result=M('contract_flow')->where($where)
            ->join('work_inform_form ON contract_flow.centreNo = work_inform_form.centreNo')//从工作通知单取数据
            ->field('contract_flow.takelist_user_id,contract_flow.status,work_inform_form.workDate,work_inform_form.centreNo,work_inform_form.sampleName,work_inform_form.testCreiteria')
            ->order('work_inform_form.workDate desc,work_inform_form.id desc')
            ->limit("{$offset},{$pagesize}")->select();//从合同表!!!!里取出对应中心编号的信息
        $count = D("contract_flow")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body=array(
            'lists'=>$result,
            'pagination'=>$pagination,
            'userid'=>$userid,
            'view'=>$view,
        );
        $this->assign($body);
        $this->display();
    }
    //接单操作
    public function doneTakeList(){
        $centreno=I("centreno");
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $where= "centreno='{$centreno}'";
        $data=array(
            'status'=>7,
            'takelist_time'=>date("Y-m-d H:i:s"),
            'takelist_user_id'=>$userid,
        );
        if(D("contract_flow")->where($where)->save($data)){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }
//上传完毕
    public function doAllSave(){
        $centreno=I("centreno");
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $where= "centreno='{$centreno}'";
        $data=array(
            'status'=>8,
            //'takelist_time'=>date("Y-m-d H:i:s"),
            //'takelist_user_id'=>$userid,
        );
        if(D("contract_flow")->where($where)->save($data)){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }
    //上传图片
    public function recordPictureUp(){
        $page = I("p",'int');
        $pagesize = 20;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $orderby = "create_time desc";
        $keyword = I("id");//获取中心编号
        $where= "centreno='{$keyword}'";
        $result=D('test_record')
            ->limit("{$offset},{$pagesize}")->where($where)->select();
        $status=D("contract_flow")->where($where)->find();
        $view=$status['status'];
        $count = D("test_record")->where($where)->count();//!!!!!!!!!!!!!!
        $Page       = new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination       = $Page->show();// 分页显示输出

        $body=array(
            'lists'=>$result,
            'pagination'=>$pagination,
            'centreno'=>$keyword,//!!!!!!!!!!!!!!
            'view'=>$view,
        );
        $this->assign($body);
        $this->display();
    }

    public function picUp(){
        $id =I("id",0,'intval');
        $centreno=I("centreno");//!!!!!!!!!!!!!!!!!!!!!!!
        if($id){
            $pic = D('test_record')->where("id=".$id)->find();
        }
        $body = array(
            'pic' => $pic,
            'centreno'=>$centreno,//!!!!!!!!!!!!!!!!!!!!!!!
        );
        $this->assign($body);
        $this->display();
    }

    public function doUploadPic(){
        $id = I("id",0,'intval');
        $centreno=I("centreno");//!!!!!!!!!!!!!!!!
        $imgurl = I("imgurl");
        $remark = I("remark");
        $result = array("msg"=>"fail");
        if(empty($imgurl)){
            $result['msg'] = "无效的提交！";
            $this->ajaxReturn($result);
        }
        $data = array("centreno"=>$centreno,"path"=>$imgurl,"remark"=>$remark,'lastmodifytime'=>date("Y-m-d H:i:s"));//!!!!!!!!!"centreno"=>$centreno,  'lastmodifytime'=>date("Y-m-d H:i:s")
        $pic = D("test_record")->where("id=".$id)->find();
        if($pic){
            if(D("test_record")->where("id=".$pic['id'])->save($data)){
                $result['msg'] = 'succ';
            }
        }else{
            if(D("test_record")->data($data)->add()){
                $result['msg'] = 'succ';
            }
        }
        $this->ajaxReturn($result);
    }

    public function doDeletePic(){
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        if(D("test_record")->where("id=".$id)->delete()){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }

    public function record(){
        $centreno = I("centreno");//获取参数
        $where= "centreno='{$centreno}'";
        $result=M('contract')->where($where)->select();//从合同表!!!!里取出对应中心编号的信息     ->field("centreno")
        $body=array(
            'lists'=>$result,

        );
        $this->assign($body);
        $this->display();
    }

    public function recordList(){
        $page = I("p",'int');
        $pagesize = 20;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $orderby = "create_time desc";

        $centreno = I("id");//获取中心编号

        $where= "centreno='{$centreno}'";
        $result=M('test_report')->limit("{$offset},{$pagesize}")->where($where)->select();
        $count = M("test_report")->where($where)->count();//!!!!!!!!!!!!!!
        $Page       = new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination       = $Page->show();// 分页显示输出

        $body=array(
            'lists'=>$result,
            'pagination'=>$pagination,
            'centreno'=>$centreno,//!!!!!!!!!!!!!!
        );
        $this->assign($body);
        $this->display();
    }


    public function recordUpload(){
        $id =I("id",0,'intval');
        $centreno=I("centreno");//!!!!!!!!!!!!!!!!!!!!!!!
        if($id){
            $pic = D('test_report')->where("id=".$id)->find();
        }
        $body = array(
            'pic' => $pic,
            'centreno'=>$centreno,//!!!!!!!!!!!!!!!!!!!!!!!
        );
        $this->assign($body);
        $this->display();
    }

    public function doUp(){
        //$id = I("record_id",0,'intval');//获取参数
        $centreNo = I("centreno");//获取参数
        $str =I("str");
        $data = array("centreNo"=>$centreNo,"htmltable"=>$str);
        if(M("test_report")->add($data)){
            $rs = array("msg"=>"succ");
        }
        $this->ajaxReturn($rs);
    }

    public function doDeleteReport(){
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        if(M("test_report")->where("id=".$id)->delete()){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }

}