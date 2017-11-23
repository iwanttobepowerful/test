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
		
		//判断是否可以打印
		$ifedit=M('contract')->where($where)->find();
        $sub_status=M('report_feedback')->where('id = (SELECT max(id) from report_feedback WHERE centreNo="'.$keyword.'")')->find();
        if(empty($sub_status)){
            $sub_status['status']=-1;
        }
		
        $body=array(
            'one'=>$result,
			'ifedit'=>$ifedit,
			'sub_status'=>$sub_status,
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

        //后面跟着现场上传图片
        $rs=D("sample_picture")->where($where)->field("picture_name")->select();//取出地址
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
		
		//判断是否可以打印
		$ifedit=M('contract')->where($where)->find();
        $sub_status=M('report_feedback')->where('id = (SELECT max(id) from report_feedback WHERE centreNo="'.$keyword.'")')->find();
        if(empty($sub_status)){
            $sub_status['status']=-1;
        }
		
        $body=array(
            'one'=>$result,
			'if_edit'=>$if_edit,
            'list'=>$path,
			'sub_status'=>$sub_status,
			'ifedit'=>$ifedit
        );
        $this->assign($body);
        $this->display();
    }
//检测记录
    public function recordPicture(){
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $department=$admin_auth['department'];//判断是哪个部门的
        $keyword = I("keyword");//获取参数
        if($user==8||$user==15||$if_admin ==1){
            if(!empty($keyword)){
                $where =" contract_flow.centreno like '%{$keyword}%'";
            }
        }
        else{
        $where = "contract_flow.centreno like '______{$department}%'";
        if(!empty($keyword)){
            $where .="and contract_flow.centreno like '%{$keyword}%'";
        }}
        if ($user==9 || $if_admin ==1) {//只有报告编制员，超级管理员才能操作
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
            ->join('contract as a on contract_flow.centreno=a.centreno')
            ->field('contract_flow.takelist_user_id,contract_flow.status,work_inform_form.workDate,work_inform_form.centreNo,work_inform_form.sampleName,work_inform_form.testCreiteria,a.centreno1,a.centreno2,a.centreno3')
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
//检测记录上传完毕按钮
    public function doAllSave(){
        $centreno=I("centreno");
        $where= "centreno='{$centreno}'";
        $record=D('test_record')->where($where)->select();
        if(empty($record)){
            $rs['msg'] = 'error';
            $this->ajaxReturn($rs);
        }
            $rs = array("msg"=>"fail");

        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];

        $data=array(
            'status'=>8,
            'takelist_all_time'=>date("Y-m-d H:i:s"),
        );
        if(D("contract_flow")->where($where)->save($data)){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }
    //检测记录上传图片
    public function recordPictureUp(){
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        $user=$admin_auth['gid'];//判断是哪个角色
        if($user==9 || $if_admin==1){$gid=1;}else{$gid=0;}
        $id =I("idnum",0,'intval');
        $centreno=I("id");//中心编号
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $where= "centreno='{$centreno}'";
        if($id){
            $pic = D('test_record')->where("id=".$id)->find();
        }
        $result=D('test_record')
            ->limit("{$offset},{$pagesize}")->where($where)->select();
        $status=D("contract_flow")->where($where)->find();
        $view =$status['status'];
        $count = D("test_record")->where($where)->count();//!!!!!!!!!!!!!!
        $Page       = new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination       = $Page->show();// 分页显示输出

        $body=array(
            'pic' => $pic,
            'lists'=>$result,
            'pagination'=>$pagination,
            'centreno'=>$centreno,//!!!!!!!!!!!!!!
            'view'=>$view,
            'gid'=>$gid
        );
        $this->assign($body);
        $this->display();
    }

    /*public function picUp(){
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
    }*/

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
        $type=I("type");//获取type
        $data=explode(',',$id);
        $where['id'] = array('in', $data);
        $where1= "centreno='{$id}'";
        if($type == 1){
            $rs=D("sample_picture")->where($where)->field('picture_name')->select();

        }
        elseif ($type == 2){
            $rs=D("test_record")->where($where1)->field('path')->select();
        }
        else{
            $rs=D("test_record")->where($where)->field('path')->select();

        }
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
//上传检测报告显示页面
    public function record(){
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        $user=$admin_auth['gid'];//判断是哪个角色
        $department = $admin_auth['department'];
        $page = I("p",'int');
        $pagesize = 20;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $where="contract_flow .status=1";
        if($user==8 || $user==15 || $user==13 || $if_admin==1){
            //
        }else{
        $where .= " and SUBSTR(contract_flow .centreno,7,1) = '{$department}'";}
        $result=D("contract_flow ")->where($where)
            ->join('left join contract as a on contract_flow .centreno=a.centreno left join test_report as t on contract_flow .centreno=t.centreno')
            ->field('contract_flow .*,a.*,t.path,t.doc_path,t.pdf_path')
            ->order('contract_flow .report_time desc,a.id desc')->limit("{$offset},{$pagesize}")->select();
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
        $where="centreno='{$centreno}'";
        //if($id){
        $report = D('test_report')->where($where)->find();
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
        $report = D("test_report")->where("centreno='{$centreno}'")->find();
        //pdf转换
        $data = array(
            //"centreNo"=>$centreno,
            "path"=>$fileurl,
            "remark"=>$remark,
            'modify_time'=>date("Y-m-d H:i:s"),
        );
        $distfile = convert2Pdf(ROOT_PATH,$data['path'],'pdf');
        
        $imgFiles = array();//delete image
        //$distfile = "/Public/attached/2017-11-21/SJ-4-77_2017_01.pdf";
        //转image,在测试服务器上测试，本地需要配置环境
        //demo
        $imageFiles = convertPdf2Image(ROOT_PATH,$distfile);
        
        if($imageFiles){
            //转换成功,合并二维码
            //第一页
            if(file_exists($imageFiles[0]) && file_exists(ROOT_PATH . $report['qrcode_path'])){
                $baseinfo = pathinfo($imageFiles[0]);
                $saveFile = $baseinfo['dirname'] . '/'.$baseinfo['filename'].'-tmp.'.$baseinfo['extension'];
                waterMark($imageFiles[0],ROOT_PATH . $report['qrcode_path'],$saveFile,array(2048,2856));
                @rename($saveFile,$imageFiles[0]);
            }
            //最后一页
            if(count($imageFiles) >1 && file_exists($imageFiles[count($imageFiles)-1]) && file_exists(ROOT_PATH . $report['qrcode_path'])){
                $baseinfo = pathinfo($imageFiles[count($imageFiles)-1]);
                $saveFile = $baseinfo['dirname'] . '/'.$baseinfo['filename'].'-tmp.'.$baseinfo['extension'];
                waterMark($imageFiles[count($imageFiles)-1],ROOT_PATH . $report['qrcode_path'],$saveFile,array(2048,2356));
                @rename($saveFile,$imageFiles[count($imageFiles)-1]);
            }

            //再转换成pdf
            $pdf = './Public/attached/report/'.$centreno.'.pdf';
            convertImageToPdf(ROOT_PATH,$pdf,$imageFiles);

            $imgFiles = $imageFiles;
            //对外签加公章
            if(file_exists($imageFiles[0]) && file_exists($imageFiles[1])){

                $baseinfo = pathinfo($imageFiles[0]);
                $tmpSavefile = $baseinfo['dirname'] . '/'.$baseinfo['filename'].'-mark.'.$baseinfo['extension'];
                waterMark($imageFiles[0],'./Public/static/images/sealA.png',$tmpSavefile,array(700,2500));
                //第二个公章
                $tmpSavefile2 = $baseinfo['dirname'] . '/'.$baseinfo['filename'].'-mark2.'.$baseinfo['extension'];
                waterMark($tmpSavefile,'./Public/static/images/sealB.png',$tmpSavefile2,array(1300,2500));
                //左上角章
                $tmpSavefile3 = $baseinfo['dirname'] . '/'.$baseinfo['filename'].'-sign.'.$baseinfo['extension'];
                waterMark($tmpSavefile2,'./Public/static/images/sign.png',$tmpSavefile3,array(100,100));
                //带mark的pdf
                @rename($tmpSavefile3,$imageFiles[0]);

                $imgFiles[] = $tmpSavefile;
                $imgFiles[] = $tmpSavefile2;



                //图二带章
                $baseinfo = pathinfo($imageFiles[1]);
                $tmpSavefile = $baseinfo['dirname'] . '/'.$baseinfo['filename'].'-mark2.'.$baseinfo['extension'];
                waterMark($imageFiles[1],'./Public/static/images/sealA.png',$tmpSavefile,array(1600,2400));
                //左上角章
                $tmpSavefile2 = $baseinfo['dirname'] . '/'.$baseinfo['filename'].'-sign2.'.$baseinfo['extension'];
                waterMark($tmpSavefile,'./Public/static/images/sign.png',$tmpSavefile2,array(100,100));
                @rename($tmpSavefile2,$imageFiles[1]);               

                $imgFiles[] = $tmpSavefile;

                //再转换成pdf
                $signPdf = './Public/attached/report/'.$centreno.'-sign.pdf';
                convertImageToPdf(ROOT_PATH,$signPdf,$imageFiles);

                $imgFiles[] = $tmpSavefile;
                $imgFiles[] = $tmpSavefile2;

                $data['pdf_path'] = substr($signPdf,1);
            }
            //
            //
            if(D("test_report")->where("centreno='{$centreno}'")->save($data)){
                if($imgFiles){
                    foreach ($imgFiles as $value) {
                        if(file_exists($value)){
                            @unlink($value);
                        }
                    }
                }
                $result['msg'] = "succ";
            }
        }else{
            $result['msg'] = "转换pdf失败";
        }
        /*
        $res = json_decode($res,true);
        if($res['retMsg']=='success'){
            $outputURLs = $res['outputURLs'];
            $pdfUrl = $outputURLs[0];
            $data['pdf_path'] = $pdfUrl;
        }else{
            $result['msg'] = "转换pdf失败";
            $this->ajaxReturn($result);
        }
        */
        $this->ajaxReturn($result);
    }
    //提交审核按钮
    public function doUpd(){
        $centreno=I("centreno");
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