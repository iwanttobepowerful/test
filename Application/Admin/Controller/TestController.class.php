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
        $result=$work_inform_form->where($where)->find();
        $body=array(
            'one'=>$result,
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
        $result=M('sampling_form')->where($where)->find();
		
		$admin_auth = session("admin_auth");
		$if_admin = $admin_auth['super_admin'];
		$roleid = $admin_auth['gid'];
		
		$role = D('common_role')->where('id='.$roleid)->find();
		if($role['rolename']=="领导" || $if_admin==1 || $role['rolename']=="前台人员"){
			$if_edit = 1;	
		}else{
			$if_edit = 0;	
		}

        $simsigndateyear = $result['simsigndate'] ? date("Y",strtotime($result['simsigndate'])):"";
        $simsigndatemonth =  $result['simsigndate'] ? date("m",strtotime($result['simsigndate'])):"";
        $simsigndateday =  $result['simsigndate'] ? date("d",strtotime($result['simsigndate'])):"";
        array_push($result,$simsigndateyear);
        array_push($result,$simsigndatemonth);
        array_push($result,$simsigndateday);

        $seasingdateyear =  $result['seasingdate'] ? date("Y",strtotime($result['seasingdate'])):"";
        $seasingdatemonth =  $result['seasingdate'] ? date("m",strtotime($result['seasingdate'])):"";
        $seasingdateday =  $result['seasingdate'] ? date("d",strtotime($result['seasingdate'])):"";
        array_push($result,$seasingdateyear);
        array_push($result,$seasingdatemonth);
        array_push($result,$seasingdateday);

        $entsigndateyear =  $result['entsigndate'] ? date("Y",strtotime($result['entsigndate'])):"";
        $entsigndatemonth =  $result['entsigndate'] ? date("m",strtotime($result['entsigndate'])):"";
        $entsigndateday =  $result['entsigndate'] ? date("d",strtotime($result['entsigndate'])):"";
        array_push($result,$entsigndateyear);
        array_push($result,$entsigndatemonth);
        array_push($result,$entsigndateday);

        $body=array(
            'one'=>$result,
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
        $role = D('common_role')->where('id='.$user)->find();
        if ($role['rolename']=="检测员" || $if_admin ==1) {//只有报告编制员，超级管理员才能操作
            $view="visible";
        }
        else
        {
            $view="hidden";
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
        $user=$admin_auth['gid'];
        $where= "centreno='{$centreno}'";
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        $role = D('common_role')->where('id='.$user)->find();
        if ($role['rolename']=="检测员" || $if_admin ==1) {
        $data=array(
            'status'=>7,
            'takelist_time'=>date("Y-m-d H:i:s"),
            'takelist_user_id'=>$userid,
        );
        if(D("contract_flow")->where($where)->save($data)){
            $rs['msg'] = 'succ';
        }}
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
    //批量打印
    public function doPrint(){
        $id=I("id");//获取勾选的id值
        $data=explode(',',$id);
        $where['id'] = array('in', $data);
        $rs=D("test_record")->where($where)->field('path')->select();
        //换成字符串后再替换
        foreach ($rs as $v){
            $v = join(",",$v); //可以用implode将一维数组转换为用逗号连接的字符串，join是别名
            $temp[] = $v;
        }
        foreach($temp as $v){
            $s .=$v.",";
        }
        $s=substr($s,0,-1);//利用字符串截取函数消除最后一个逗号
        $list=str_replace("_thumb","",$s);
        $path=explode(',',$list);
        $this->assign('list',$path);
        $this->display();
    }
//以上是检测记录的图片上传

//上传检测报告显示页面
    public function record(){
        $page = I("p",'int');
        $pagesize = 20;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $where['status']=1;
        $result=D("contract_flow as c")->where($where)
            ->join('left join contract as a on c.centreno=a.centreno left join test_report as t on c.centreno=t.centreno')
            ->field('c.*,a.*,t.path,t.doc_path,t.pdf_path')
            ->order('c.report_time desc,a.id desc')->limit("{$offset},{$pagesize}")->select();
            //当已经生成报告，状态为1的时候，才能上传检测报告
        $count = D("contract_flow")->where($where)->count();//!!!!!!!!!!!!!!
        $Page       = new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination       = $Page->show();// 分页显示输出
            $body=array(
                'pagination'=>$pagination,
                'lists'=>$result,
            );
            $this->assign($body);
        $this->display();
    }
//上传检测报告显示列表
    public function recordList(){
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
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

//上传检测报告页面
    public function recordUpload(){
        //$id =I("id",0,'intval');
        $centreno=I("centreno");
        //if($id){
        $report = D('test_report')->where("centreno='{$centreno}'")->find();
        //}
        $body = array(
            'report' => $report,
            'centreno'=>$centreno,
        );
        $this->assign($body);
        $this->display();
    }

//上传检测报告提交word
    public function doUp(){
        $id =I("id",0,'intval');//test_report的id
        $centreno=I("centreno");
        $where= "centreno='{$centreno}'";
        $fileurl = I("fileurl");
        $remark = I("remark");
        $result = array("msg"=>"fail");

        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        if(empty($fileurl)){
            $result['msg'] = "无效的提交！";
            $this->ajaxReturn($result);
        }
        $data = array(
            //"centreNo"=>$centreno,
            "path"=>$fileurl,
            "remark"=>$remark,
        );
        //pdf转换
        $docUrl = getCurrentHost().$data['path'];
        //$docUrl = "http://adm.qooce.cn/Public/attached/word/2017-11-15/1510741227.docx";
        $res = convert2Pdf($docUrl);
        $res = json_decode($res,true);
        if($res['retCode']===0){
            $outputURLs = $res['outputURLs'];
            $pdfUrl = $outputURLs[0];
            $data['pdf_path'] = $pdfUrl;
        }else{
            $result['msg'] = "转换pdf失败";
            $this->ajaxReturn($result);
        }
       /*$data1=array(
            'status'=>2,
            'uploadreport_user_id'=>$userid,
            'uploadreport_time'=>date("Y-m-d H:i:s"),
        );*/
        M()->startTrans();
        if(D("test_report")->where("centreno='{$centreno}'")->save($data)){
            //if(D("contract_flow")->where("centreno='{$centreno}'")->save($data1)){
                $result['msg'] = "succ";
                M()->commit();
            }else{
                M()->rollback();
          // }
       // }else{
           // M()->rollback();
        }
        $this->ajaxReturn($result);
    }
    //提交审核按钮
    public function doUpd(){
        $centreno=I("centerno");
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $data1=array(
            'status'=>2,
            'uploadreport_user_id'=>$userid,
            'uploadreport_time'=>date("Y-m-d H:i:s"),
        );
        M()->startTrans();
        if(D("contract_flow")->where("centreno='{$centreno}'")->save($data1)){
            $rs['msg'] = "succ";
            M()->commit();
        }else{
            M()->rollback();
        }
        $this->ajaxReturn($rs);
    }
        //删除（不用改）
    public function doDeleteReport(){
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        if(M("test_report")->where("id=".$id)->delete()){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }

}