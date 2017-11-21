<?php
/**
 * Created by PhpStorm.
 * User: Ail
 * Date: 2017/10/14
 * Time: 16:49
 */
namespace Admin\Controller;
use Think\Controller;
class ContractController extends Controller
{
    public $user = null;

    public function _initialize()
    {
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active', strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active', strtolower(ACTION_NAME));
    }
    public function getTestReport(){
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $test_reprot=M("test_reprot");//实例化对象
        $where['authorizer']=1;
        $where['ifinnerissue']=0;
        $rs=$test_reprot->where($where)->field('id,centreNo')->order('id')->limit("{$offset},{$pagesize}")->select();//查找条件为已经批准并且内部尚未领取的报告
        $count = D("test_reprot")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            'lists'=>$rs,
            'pagination'=>$pagination,
        );
        $this->assign($body);
        $this->display();
    }
    //外部签发检验报告
    public function issueTestReport(){
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $test_reprot=M("test_reprot");//实例化对象
        $contract=M("contract");//实例化对象
        //显示条件
        $where['authorizer']=1;
        $where['ifinnerissue']=1;
        $where['ifouterissue']=0;
        //从contract数据表中找出和test_reprot数据表centreno匹配的那一行
        //返回对应的clientSign和telephone
        $rs=$test_reprot->where($where)
            ->join('contract ON test_reprot.centreNo = contract.centreNo')
            ->field('test_reprot.id,test_reprot.centreNo,test_reprot.productUnit,contract.clientSign,contract.telephone')
            ->limit("{$offset},{$pagesize}")->order('test_reprot.id')->select();

        $count = D("test_reprot")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            'lists'=>$rs,
            'pagination'=>$pagination,
        );
        $this->assign($body);
        $this->display();
    }
    //标记签发报告，更新数据库
    public function doUpd(){
// 要修改的数据对象属性赋值
        $data['ifouterissue'] = 1;
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        if(D("test_reprot")->where("id=".$id)->save($data)){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }



//领取检验报告


    //进入合同录入页面
    public function input(){
        $admin_auth = session("admin_auth");
        $collector = $admin_auth['name'];
        $department = $admin_auth['department'];
        $body=array(
            'collector'=>$collector,
            'department'=>$department
        );
        $this->assign($body);
        $this->display();
    }

    //合同录入
    public function doAddContract(){
        $clientName = I("clientName");
        $productUnit = I("productUnit");
        $sampleName = I("sampleName");
        $sampleCode = I("sampleCode");
        $grade = I("grade");
        $specification = I("specification");
        $trademark = I("trademark");
        $productionDate = I("productionDate");
        $sampleQuantity = I("sampleQuantity");
        //$sampleunti = I("sampleunti");
        $sampleStatus = I("sampleStatus");
        $ration = I("ration");
        $testCriteria = I("testCriteria");
        $testItem = I("testItem");
        $testCategory = I("testCategory");
        $ifOnline = I("ifOnline",0);
        $postMethod = I("postMethod");
        $ifSubpackage = I("ifSubpackage");
        $clientSign = I("clientSign");
        $telephone = I("telephone");
        $tax = I("tax");
        $postcode = I("postcode");
        $email = I("email");
        $address = I("address");
        $remark = I("remark");
        $sampleStaQuan = I("sampleStaQuan");
        $collector = I("collector");
        $centreNo = I("centreNo");
        $testCost = I("testCost",0,'intval');
        $collectDate = I("collectDate");
        $reportDate = I("reportDate");
        $ifHighQuantity = I("ifHighQuantity");
        $package_remark = I("package_remark");

        //费用详情
        $Arecord = I("Arecord",0,'intval');
        $Brecord = I("Brecord",0,'intval');
        $Crecord = I("Crecord",0,'intval');
        $Drecord = I("Drecord",0,'intval');
        $Erecord = I("Erecord",0,'intval');
        $Frecord = I("Frecord",0,'intval');
		
		$RArecord = I("RArecord",0,'intval');
        $RBrecord = I("RBrecord",0,'intval');
        $RCrecord = I("RCrecord",0,'intval');
        $RDrecord = I("RDrecord",0,'intval');
        $RErecord = I("RErecord",0,'intval');
        $RFrecord = I("RFrecord",0,'intval');
		
		$fee_remark = I("fee_remark");
		
        $Dcopy = I("Dcopy",0,'intval');
        $Donline = I("Donline",0,'intval');
        $Drevise = I("Drevise",0,'intval');
        $Dother = I("Dother",0,'intval');

        $ifspecial = I("ifspecial");//是否是特殊编码

        $rs = array("msg"=>'fail');
        if(empty($clientName)||empty($productUnit)||empty($sampleName)||empty($testCriteria)||empty($testItem)||empty($sampleQuantity)||empty($sampleStatus)||empty($sampleStaQuan)||empty($collector)||empty($testCost)||empty($collectDate)||empty($reportDate)){
            $rs['msg'] = '信息填写不完整!';
            $this->ajaxReturn($rs);
        }
        if(empty($productionDate)){
            $productionDate=null;
        }
        $admin_auth = session("admin_auth");
        $collector_partment=$admin_auth['department'];
        $data = array(
            "clientName"=>$clientName,
            "productUnit"=>$productUnit,
            "sampleName"=>$sampleName,
            "sampleCode"=>$sampleCode,
            "grade"=>$grade,
            "specification"=>$specification,
            "trademark"=>$trademark,
            "productionDate"=>$productionDate,
            "sampleQuantity"=>$sampleQuantity,
            //"sampleunti"=>$sampleunti,
            "sampleStatus"=>$sampleStatus,
            "ration"=>$ration,
            "testCriteria"=>$testCriteria,
            "testItem"=>$testItem,
            "testCategory"=>$testCategory,
            "ifOnline"=>$ifOnline,
            "postMethod"=>$postMethod,
            "ifSubpackage"=>$ifSubpackage,
            "package_remark"=>$package_remark,
            "clientSign"=>$clientSign,
            "telephone"=>$telephone,
            "tax"=>$tax,
            "postcode"=>$postcode,
            "email"=>$email,
            "address"=>$address,
            "remark"=>$remark,
            "sampleStaQuan"=>$sampleStaQuan,
            "collector"=>$collector,
            "centreNo"=>$centreNo,
            "testCost"=>$testCost,
            "collectDate"=>$collectDate,
            "reportDate"=>$reportDate,
            "ifHighQuantity"=>$ifHighQuantity,
            'input_time'=>Date("Y-m-d H:i:s"),
            'collector_partment'=>$collector_partment,
			'ifedit'=>0
        );
        $de = substr($centreNo,6,1);

        $filler = $admin_auth['name'];
        //检验工作通知单入库
        $data_work = array(
            "centreNo"=>$centreNo,
            "sampleName"=>$sampleName,
            "testCreiteria"=>$testCriteria,
            "testItem"=>$testItem,
            'testDepartment'=>$de,
            "ration"=>$ration,
            'workDate'=>$collectDate,
            'finishDate'=>$reportDate,
            "sampleAuantity"=>$sampleQuantity,
            "sampleStatus"=>$sampleStatus,
            'otherComments'=>$remark,
            'filler'=>$filler,
            'fillDate'=>Date("Y-m-d H:i:s"),
            //"sampleunti"=>$sampleunti,
        );

        //费用表
        $date_cost =array(
            "centreNo"=>$centreNo,
            "Arecord"=>$Arecord,
            "Brecord"=>$Brecord,
            "Crecord"=>$Crecord,
            "Drecord"=>$Drecord,
            "Erecord"=>$Erecord,
            "Frecord"=>$Frecord,
			"RArecord"=>$RArecord,
            "RBrecord"=>$RBrecord,
            "RCrecord"=>$RCrecord,
            "RDrecord"=>$RDrecord,
            "RErecord"=>$RErecord,
            "RFrecord"=>$RFrecord,
            "Dcopy"=>$Dcopy,
            "Donline"=>$Donline,
            "Drevise"=>$Drevise,
            "Dother"=>$Dother,
			"remark"=>$fee_remark,
            'costDate'=>Date("Y-m-d H:i:s")
        );

        $contract_user_id = $admin_auth['id'];
        //pr($contract_user_id);
        $data_flow = array(
            "centreNo"=>$centreNo,
            'contract_user_id'=>$contract_user_id,
            'contract_time'=>Date("Y-m-d H:i:s"),
        );

		$type = substr($centreNo,7,1);
		if($type=='W'){
			$data['ifedit']=1;
		}
        M()->startTrans();
        try{

            //合同入库
            D("contract")->data($data)->add();


            //特殊编码操作
            if($ifspecial==1){
                $year = substr($centreNo,0,4);
                $month = substr($centreNo,4,2);
                $where['year']=$year;
                $where['month']=$month;
                $specialItem = D("special_centre_code")->field('id,getNum')->where($where)->find();
                $num = (int)$specialItem['getnum'];
                //pr("num=".$num);
                $special_id = $specialItem['id'];
                //if($num==1){
                //D("special_centre_code")->delete($special_id);
                //}else{
                $num = $num-1;
                $editData['remainNum'] = $num;
                D("special_centre_code")->where('id='.$special_id)->save($editData);
                //}
            }

            //费用入库
            if(!D("test_cost")->data($date_cost)->add()) $flag=false;

            //通知单入库
            if(!D("work_inform_form")->data($data_work)->add()) $flag=false;

            //抽样单入库
           
            if($type=='C'){
                $data_sample = array(
                    "centreNo"=>$centreNo,
                    "productUnit"=>$clientName,
                    "sampleName"=>$sampleName,
                    "specification"=>$specification,
                    //缺产品批号
                    "testCriteria"=>$testCriteria,
                    "trademark"=>$trademark,
                    "sampleQuantity"=>$sampleQuantity,
                    //"sampleUnit"=>$sampleunti,
                    //"productionDate"=>$productionDate,
                    "testItem"=>$testItem,
                    "ifOnline"=>$ifOnline,
                    "ifSubpackage"=>$ifSubpackage,
                    "package_remark"=>$package_remark,
                );
                D("sampling_form")->data($data_sample)->add();
            }else{
                D("contract_flow")->data($data_flow)->add();
            }
            M()->commit();
            $rs['msg'] = 'succ';
        }catch(Exception $e){
            $rs['msg'] = '信息有误，录入不成功';
            M()->rollback();
        }
        ////if($flag){
        //$rs['msg'] = 'succ';
        //M()->commit();
        //}else{
        //	$rs['msg'] = '信息有误，录入不成功';
        //M()->rollback();
        //}
        $this->ajaxReturn($rs);
    }

    //合同修改页面
    public function editContract(){
        $contreno = I('id');
		$type_status = I('type_status');
        $where['centreNo']=$contreno;
        //$testCategory = substr($contreno,7,1);
        $contractItem = D('contract')->where($where)->find();
        $feeItem = D('test_cost')->where($where)->find();
        $body = array(
            'contract'=>$contractItem,
            'feeItem'=>$feeItem,
			'type_status'=>$type_status,
            //'$testCategory'=>$testCategory
        );

        $this->assign($body);
        $this->display();
    }

    //合同修改入库
    public function doEditContract(){
		//是否为外部签发后的修改
		$type_status = I('type_status');
		if($type_status==6){	
			$centreNo = I("centreNo");
			$testCost = I("testCost",0,'intval');
			$Dcopy = I("Dcopy",0,'intval');
			$Drevise = I("Drevise",0,'intval');
			$Dother = I("Dother",0,'intval');
			$fee_remark = I("fee_remark");
			
			$data = array(
				"testCost"=>$testCost,
			);
			//费用表
			$date_cost =array(
				"Dcopy"=>$Dcopy,
				"Drevise"=>$Drevise,
				"Dother"=>$Dother,
				"remark"=>$fee_remark
			);
			M()->startTrans();
			try{
				$where['centreNo']=$centreNo;
				//合同入库
				D("contract")->where($where)->save($data);
	
				//费用入库
				D("test_cost")->where($where)->save($date_cost);
	
				M()->commit();
				$rs['msg'] = 'succ';
			}catch(Exception $e){
				$rs['msg'] = '信息有误，修改不成功';
				M()->rollback();
			}
		}else{
			$clientName = I("clientName");
			$productUnit = I("productUnit");
			$sampleName = I("sampleName");
			$sampleCode = I("sampleCode");
			$grade = I("grade");
			$specification = I("specification");
			$trademark = I("trademark");
			$productionDate = I("productionDate");
			$sampleQuantity = I("sampleQuantity");
			//$sampleunti = I("sampleunti");
			$sampleStatus = I("sampleStatus");
			$ration = I("ration",0,'intval');
			$testCriteria = I("testCriteria");
			$testItem = I("testItem");
			//$testCategory = I("testCategory");
			$ifOnline = I("ifOnline");
			$postMethod = I("postMethod");
			$ifSubpackage = I("ifSubpackage");
			$package_remark = I("package_remark");
			$clientSign = I("clientSign");
			$telephone = I("telephone");
			$tax = I("tax");
			$postcode = I("postcode");
			$email = I("email");
			$address = I("address");
			$remark = I("remark");
			$sampleStaQuan = I("sampleStaQuan");
			$collector = I("collector");
			$centreNo = I("centreNo");
			$testCost = I("testCost",0,'intval');
			$collectDate = I("collectDate");
			$reportDate = I("reportDate");
			$ifHighQuantity = I("ifHighQuantity");
	
			//费用详情
			$Arecord = I("Arecord",0,'intval');
			$Brecord = I("Brecord",0,'intval');
			$Crecord = I("Crecord",0,'intval');
			$Drecord = I("Drecord",0,'intval');
			$Erecord = I("Erecord",0,'intval');
			$Frecord = I("Frecord",0,'intval');
			
			$RArecord = I("RArecord",0,'intval');
			$RBrecord = I("RBrecord",0,'intval');
			$RCrecord = I("RCrecord",0,'intval');
			$RDrecord = I("RDrecord",0,'intval');
			$RErecord = I("RErecord",0,'intval');
			$RFrecord = I("RFrecord",0,'intval');
			
			$Dcopy = I("Dcopy",0,'intval');
			$Donline = I("Donline",0,'intval');
			$Drevise = I("Drevise",0,'intval');
			$Dother = I("Dother",0,'intval');
			$fee_remark = I("fee_remark");
	
			if(empty($clientName)||empty($productUnit)||empty($sampleName)||empty($testCriteria)||empty($testItem)||empty($sampleQuantity)||empty($sampleStatus)||empty($sampleStaQuan)||empty($collector)||empty($testCost)||empty($collectDate)||empty($reportDate)){
				$rs['msg'] = '信息填写不完整!';
				$this->ajaxReturn($rs);
			}
	
			$data = array(
				"clientName"=>$clientName,
				"productUnit"=>$productUnit,
				"sampleName"=>$sampleName,
				"sampleCode"=>$sampleCode,
				"grade"=>$grade,
				"specification"=>$specification,
				"trademark"=>$trademark,
				"productionDate"=>$productionDate,
				"sampleQuantity"=>$sampleQuantity,
				//"sampleunti"=>$sampleunti,
				"sampleStatus"=>$sampleStatus,
				"ration"=>$ration,
				"testCriteria"=>$testCriteria,
				"testItem"=>$testItem,
				//"testCategory"=>$testCategory,
				"ifOnline"=>$ifOnline,
				"postMethod"=>$postMethod,
				"ifSubpackage"=>$ifSubpackage,
				"package_remark"=>$package_remark,
				"clientSign"=>$clientSign,
				"telephone"=>$telephone,
				"tax"=>$tax,
				"postcode"=>$postcode,
				"email"=>$email,
				"address"=>$address,
				"remark"=>$remark,
				"sampleStaQuan"=>$sampleStaQuan,
				"collector"=>$collector,
				"centreNo"=>$centreNo,
				"testCost"=>$testCost,
				"collectDate"=>$collectDate,
				"reportDate"=>$reportDate,
				"ifHighQuantity"=>$ifHighQuantity
			);
			$de = substr($centreNo,6,1);
			$admin_auth = session("admin_auth");
			$filler = $admin_auth['name'];
			//检验工作通知单入库
			$data_work = array(
				"centreNo"=>$centreNo,
				"sampleName"=>$sampleName,
				"testCreiteria"=>$testCriteria,
				"testItem"=>$testItem,
				'testDepartment'=>$de,
				"ration"=>$ration,
				'workDate'=>$collectDate,
				'finishDate'=>$reportDate,
				"sampleAuantity"=>$sampleQuantity,
				"sampleStatus"=>$sampleStatus,
				'otherComments'=>$remark,
				'filler'=>$filler,
				'fillDate'=>Date("Y-m-d H:i:s"),
				//"sampleunti"=>$sampleunti,
			);
	
			//费用表
			$date_cost =array(
				"centreNo"=>$centreNo,
				"Arecord"=>$Arecord,
				"Brecord"=>$Brecord,
				"Crecord"=>$Crecord,
				"Drecord"=>$Drecord,
				"Erecord"=>$Erecord,
				"Frecord"=>$Frecord,
				"RArecord"=>$RArecord,
				"RBrecord"=>$RBrecord,
				"RCrecord"=>$RCrecord,
				"RDrecord"=>$RDrecord,
				"RErecord"=>$RErecord,
				"RFrecord"=>$RFrecord,
				"Dcopy"=>$Dcopy,
				"Donline"=>$Donline,
				"Drevise"=>$Drevise,
				"Dother"=>$Dother,
				"remark"=>$fee_remark,
				'costDate'=>Date("Y-m-d H:i:s")
			);
			M()->startTrans();
			try{
				$where['centreNo']=$centreNo;
				//合同入库
				D("contract")->where($where)->save($data);
	
				//费用入库
				D("test_cost")->where($where)->save($date_cost);
	
				//通知单入库
				D("work_inform_form")->where($where)->save($data_work);
	
				//抽样单入库
				$type = substr($centreNo,7,1);
				if($type=='C'){
					$data_sample = array(
						"centreNo"=>$centreNo,
						"productUnit"=>$clientName,
						"sampleName"=>$sampleName,
						"specification"=>$specification,
						//缺产品批号
						"testCriteria"=>$testCriteria,
						"trademark"=>$trademark,
						"sampleQuantity"=>$sampleQuantity,
						//"sampleUnit"=>$sampleunti,
						//"productionDate"=>$productionDate,
						"testItem"=>$testItem,
						"ifOnline"=>$ifOnline,
						"ifSubpackage"=>$ifSubpackage,
						"package_remark"=>$package_remark
					);
					D("sampling_form")->where($where)->save($data_sample);
				}
	
				M()->commit();
				$rs['msg'] = 'succ';
			}catch(Exception $e){
				$rs['msg'] = '信息有误，修改不成功';
				M()->rollback();
			}
		}
        $this->ajaxReturn($rs);
    }

    //申请修改完毕
    public function doUpdateEditState(){
        $centreno = I('centreno');
		$type_status = I('type_status')==6?1:0;
		
        $rs = array('msg'=>'fail');
        $where['centreNo']=$centreno;
        $data_apply = array(
            "status"=>0
        );
		
		if($type_status == 1) $data_apply['status']=7;
        //M()->startTrans();
        D("contract_flow")->where($where)->save($data_apply);
        //D("report_feedback")->where($where)->delete();
		
		//修改完毕后，逻辑删除
		$data_feedback = array(
            "status"=>3
        );
		D("report_feedback")->where($where)->save($data_feedback);
        //M()->commit();
        $rs['msg']='修改提交成功';
        /*}else{
            M()->rollback();
            $rs['msg']='修改提交失败';
        }
    }else{
        M()->rollback();
        $rs['msg']='修改提交失败';
    }*/
        $this->ajaxReturn($rs);
    }

    //生成检验工作单
    public function checkWorkList(){
        $this->display();
    }

    //跳转抽样单修改页面
    public function sampleEdit(){
        $centreno = I("id");
        $where['centreNo']=$centreno;
        $result=M('sampling_form')->where($where)->find();
        
        $ifedit=M('contract')->where($where)->find();
        $sub_status=M('report_feedback')->where($where)->find();
        if(empty($sub_status)){
            $sub_status['status']=-1;
        }
        //判断角色，确定是否可以修改
        $admin_auth = session("admin_auth");
        $if_admin = $admin_auth['super_admin'];
        $roleid = $admin_auth['gid'];

        $role = D('common_role')->where('id='.$roleid)->find();
        if($role['rolename']=="领导" || $if_admin==1 || $role['rolename']=="前台人员"){
            $if_edit = 1;
        }else{
            $if_edit = 0;
        }
		
		//判断是否已经提交完毕，目的判断是否出现抽样单录入完毕按钮
		if(empty($result['samplebase']) && empty($result['sampledate']) && empty($result['productiondate']) && empty($result['sampleplace']) && empty($result['samplemethod']) && empty($result['simplersign']) && empty($result['simsigndate']) && empty($result['sealersign']) && empty($result['seasingdate']) && empty($result['enterprisesign']) && empty($result['entsigndate'])){
			$if_save = 0;
		}else{
			$if_save = 1;	
		}
		
		//判断是否上传现成照片，目的判断是否出现抽样单录入完毕按钮
		$where_p['centreNo']=$centreno;
		$where_p['type']=1;
		$if_picture = M('sample_picture')->where($where_p)->count();
		
		//判断是否所有抽样信息录入完毕，目的判断是否出现打印按钮
		$if_submit=M('contract_flow')->where($where)->count();
		
		
		

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

        //查看是否已录入完毕
        $sample_count = D('contract_flow')->where($where)->count();

        $body=array(
            'one'=>$result,
            'if_edit'=>$if_edit,
            'sample_count'=>$sample_count,
            //'status'=>$status,
            'ifedit'=>$ifedit,
            'sub_status'=>$sub_status,
			'if_save'=>$if_save,
			'if_picture'=>$if_picture,
			'if_submit'=>$if_submit,
        );
        $this->assign($body);
        $this->display();
    }

    //抽样单修改
    public function doEditSample(){
        $centreno = I("centreno");
        $samplebase=I("samplebase");
        $sampledate=I("sampledate");
        $sampleplace=I("sampleplace");
        $samplemethod=I("samplemethod");
        $productiondate=I("productiondate");
        $batchno=I("batchno");
        $simplerSign=I("simplerSign");
        $simSignDate=I("simSignDate");
        //$simplerYear=I("simplerYear");
        //$simplerMonth=I("simplerMonth");
        //$simplerDay=I("simplerDay");
        //$simSignDate = $simplerYear."-".$simplerMonth."-".$simplerDay;
        $sealerSign=I("sealerSign");
        $seaSingDate=I("seaSingDate");
        //$sealerYear=I("sealerYear");
        //$sealerMonth=I("sealerMonth");
        //$sealerDay=I("sealerDay");
        //$seaSingDate = $sealerYear."-".$sealerMonth."-".$sealerDay;
        $enterpriseSign=I("enterpriseSign");
        $entSignDate=I("entSignDate");
        //$enterpriseYear=I("enterpriseYear");
        //$enterpriseMonth=I("enterpriseMonth");
        //$enterpriseDay=I("enterpriseDay");
        //$entSignDate = $enterpriseYear."-".$enterpriseMonth."-".$enterpriseDay;
        $telephone=I("telephone");
        $tax=I("tax");
        $address=I("address");
        $data=array(
            'sampleBase'=>$samplebase,
            'sampleDate'=>$sampledate,
            'samplePlace'=>$sampleplace,
            'sampleMethod'=>$samplemethod,
            'productionDate'=>$productiondate,
            'batchNo'=>$batchno,
            'simplerSign'=>$simplerSign,
            'simSignDate'=>$simSignDate,
            'sealerSign'=>$sealerSign,
            'seaSingDate'=>$seaSingDate,
            'enterpriseSign'=>$enterpriseSign,
            'entSignDate'=>$entSignDate,
            'telephone'=>$telephone,
            'tax'=>$tax,
            'address'=>$address
        );
        //pr($data);
        $where['centreNo']=$centreno;
        //pr($centreno);
        $rs['msg']='fail';
        M()->startTrans();
        if(D('sampling_form')->where($where)->save($data)){
            M()->commit();
            $rs['msg']='修改成功！';
        }else{
            M()->rollback();
            $rs['msg']='数据未更改！';
        }
        $this->ajaxReturn($rs);
    }

    //抽样单上传
    public function doUploadSampleImage(){
        $centreno = I('centreno');
        $type = I('type');

        $where['centreNo']=$centreno;
        //$result=M('sampling_form')->where($where)->find();
        //$status=M('contract_flow')->where($where)->find();
        $ifedit=M('contract')->where($where)->find();
        $sub_status=M('report_feedback')->where($where)->find();
        if(empty($sub_status)){
            $sub_status['status']=-1;
        }

        if($type=='sample'){
            $list = D('sample_picture')->where('type=0 and centreno="'.$centreno.'"')->select();
        }else{
            $list = D('sample_picture')->where('type=1 and centreno="'.$centreno.'"')->select();
        }
        $body=array(
            'centreno'=>$centreno,
            'type'=>$type,
            'list'=>$list,
            'ifedit'=>$ifedit,
            'sub_status'=>$sub_status,
			'type'=>$type,
        );
        $this->assign($body);
        $this->display();
    }

    //检验抽样现场图片个数
    public function checkFinish(){
        $centreno = I('centreno');
        $count = D('sample_picture')->where('type=1 and centreno="'.$centreno.'"')->count();
        $rs=array(
            'count'=>$count
        );
        $this->ajaxReturn($rs);
    }

    //抽样单图片删除
    public function doDeleteSample(){
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        if(D("sample_picture")->where("id=".$id)->delete()){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }

    //保存抽样图片
    public function saveSampleImage(){
        $id = I("id");
        $type = I("type")=='sample'?0:1;
        $imgurl = I("imgurl");
        $remark = I("remark");
        $result = array("msg"=>"fail");

        if(empty($imgurl)){
            $result['msg'] = "无效的提交！";
            $this->ajaxReturn($result);
        }

        $data = array(
            'centreNo'=>$id,
            'picture_name'=>$imgurl,
            'remark'=>$remark,
            'type'=>$type
        );
        M()->startTrans();
        if(D(sample_picture)->add($data)){
            $result['msg'] = 'succ';
            M()->commit();
        }else{
            M()->rollback();
        }
        $this->ajaxReturn($result);
	}
	
	//抽样单提交
	public function doUpdateState(){
		$rs = array('msg'=>'fail');
		$centreNo = I('centreno');
		$admin_auth = session("admin_auth");
		$contract_user_id = $admin_auth['id'];
		$where['centreNo']=$centreNo;
		//pr($centreNo);
		$data_flow = array(
			"centreNo"=>$centreNo,
			'contract_user_id'=>$contract_user_id,
			'contract_time'=>Date("Y-m-d H:i:s"),
		);
		$data_contract = array(
			'ifedit'=>1
		);
		M()->startTrans();	
		if(D("contract_flow")->data($data_flow)->add()){
			D("contract")->where($where)->save($data_contract);
			M()->commit();
			$rs['msg']='已提交';
		}else{
			$rs['msg']='提交失败';
			M()->rollback();	
		}
		$this->ajaxReturn($rs);
	}

	//特殊号段查询
	public function specialCodeSelect(){
		$list = D("special_centre_code")->where('remainNum>0')->select();
		
		$body = array(
			"special_list"=>$list,
		);
		//dump($body);
	    $this->assign($body);
		$this->display();
	}
	
	//外部签发
	/*public function externalSign(){
		$rs = array('msg'=>'fail');
		$centreNo = I('centreno');
		//pr($centreNo);
		$admin_auth = session("admin_auth");
		$external_sign_user_id = $admin_auth['id'];
		//pr($centreNo);
		$data_flow = array(		
			'status'=>6,
			'external_sign_user_id'=>$external_sign_user_id,
			'contract_time'=>Date("Y-m-d H:i:s"),
            'external_sign_time'=>Date("Y-m-d H:i:s")
		);
		$where['centreNo']=$centreNo;
		M()->startTrans();	
		if(D("contract_flow")->where($where)->save($data_flow)){
			M()->commit();
			$rs['msg']='已外部签发';
		}else{
			$rs['msg']='签发失败';
			M()->rollback();	
		}
		$this->ajaxReturn($rs);
	}*/

	//合同列表
	public function showList(){
		//判断角色，确定是否可以修改
		$admin_auth = session("admin_auth");
		$if_admin = $admin_auth['super_admin'];
		$roleid = $admin_auth['gid'];
		$department = $admin_auth['department'];
		$begin_time = I("begin_time");
        $end_time = I("end_time");
		
		$role = D('common_role')->where('id='.$roleid)->find();
		if($role['rolename']=="领导" || $if_admin==1 || $role['rolename']=="前台人员"){
			$if_edit = 1;	
		}else{
			$if_edit = 0;	
		}
		$keyword = I("keyword");//获取参数
        $where = "1=1";
        $keyword && $where .= " and c.centreNo like '%{$keyword}%'";

		if($role['rolename']=="领导" || $role['rolename']=="审核员" || $role['rolename']=="盖章人员" || $if_admin==1){
			//
		}else{
			$where .= " and SUBSTR(c.centreNo,7,1) = '{$department}'";
		}
		if(!empty($begin_time)){
			$where.=" and date_format(c.collectDate,'%Y-%m-%d') >='{$begin_time}'";
		}
		if(!empty($end_time)){
			$where.=" and date_format(c.collectDate,'%Y-%m-%d') <='{$end_time}'";
		}

		$page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
		
		//判断是接单还是签发
		//$ifstatus = 
		$list = D("contract as c")->field('if(f.id is null,-1,f.id) as flow_id,if(r.status is null,-1,r.status) as sub_status,c.*,f.status,f.inner_sign_user_id,f.inner_sign_time,f.takelist_user_id,f.takelist_time,u.name as takename,u1.name as innername')->join('left join contract_flow as f on c.centreNo=f.centreNo LEFT JOIN common_system_user u on f.takelist_user_id=u.id LEFT JOIN common_system_user u1 on f.inner_sign_user_id=u1.id LEFT JOIN report_feedback r on r.centreNo = c.centreNo')->where($where)->order('c.input_time DESC')->limit("{$offset},{$pagesize}")->select();
		$count = D("contract as c")->field('if(r.status is null,-1,r.status) as sub_status,c.*,f.status,f.inner_sign_user_id,f.inner_sign_time,f.takelist_user_id,f.takelist_time,u.name as takename,u1.name as innername')->join('left join contract_flow as f on c.centreNo=f.centreNo LEFT JOIN common_system_user u on f.takelist_user_id=u.id LEFT JOIN common_system_user u1 on f.inner_sign_user_id=u1.id LEFT JOIN report_feedback r on r.centreNo = c.centreNo')->where($where)->order('c.input_time DESC')->count();
		$Page= new \Think\Page($count,$pagesize);
		$Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
		$pagination= $Page->show();// 分页显示输出
		
		$body = array(
			"list"=>$list,
			'pagination'=>$pagination,
			'if_edit'=>$if_edit,
			'begin_time'=>$begin_time,
			'end_time'=>$end_time
		);
		//dump($body);
		$this->assign($body);
		$this->display();
	}
	//报告管理下的合同列表
    public function showReportList(){
        //判断角色，确定是否可以修改
        $admin_auth = session("admin_auth");
        $if_admin = $admin_auth['super_admin'];
        $user = $admin_auth['gid'];
        $department = $admin_auth['department'];
        $begin_time = I("begin_time");
        $end_time = I("end_time");
        if($user==10 || $if_admin==1 ){
            $if_edit = 1;
        }else{
            $if_edit = 0;
        }
        $keyword = I("keyword");//获取参数
        $where= "f.status != 7";
        $keyword && $where .= " and c.centreNo like '%{$keyword}%'";

        if($user==8 || $user==15 || $user==13 || $if_admin==1){
            //
        }else{
            $where .= " and SUBSTR(c.centreNo,7,1) = '{$department}'";
        }
        if(!empty($begin_time)){
            $where.=" and date_format(c.collectDate,'%Y-%m-%d') >='{$begin_time}'";
        }
        if(!empty($end_time)){
            $where.=" and date_format(c.collectDate,'%Y-%m-%d') <='{$end_time}'";
        }

        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;

        //判断是接单还是签发
        //$ifstatus =
        $list = D("contract as c")->field('if(f.id is null,-1,f.id) as flow_id,if(r.status is null,-1,r.status) as sub_status,c.*,f.status,f.inner_sign_user_id,f.inner_sign_time,f.external_sign_time,f.takelist_user_id,f.takelist_time,u.name as takename,u1.name as innername,u2.name as externalname,v.doc_path')->join('left join contract_flow as f on c.centreNo=f.centreNo LEFT JOIN common_system_user u on f.takelist_user_id=u.id LEFT JOIN common_system_user u2 on f.external_sign_user_id=u2.id LEFT JOIN common_system_user u1 on f.inner_sign_user_id=u1.id LEFT JOIN report_feedback r on r.centreNo = c.centreNo left join test_report as v on c.centreNo=v.centreNo' )->where($where)->order('f.takelist_all_time desc,c.id desc')->limit("{$offset},{$pagesize}")->select();
        $count = D("contract as c")->field('if(r.status is null,-1,r.status) as sub_status,c.*,f.status,f.inner_sign_user_id,f.inner_sign_time,f.takelist_user_id,f.takelist_time,u.name as takename,u2.name as externalname,u1.name as innername')->join('left join contract_flow as f on c.centreNo=f.centreNo LEFT JOIN common_system_user u on f.takelist_user_id=u.id LEFT JOIN common_system_user u1 on f.inner_sign_user_id=u1.id LEFT JOIN common_system_user u2 on f.external_sign_user_id=u2.id LEFT JOIN report_feedback r on r.centreNo = c.centreNo')->where($where)->order('f.takelist_all_time desc,c.id desc')->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出

        $body = array(
            "list"=>$list,
            'pagination'=>$pagination,
            'if_edit'=>$if_edit,
            'begin_time'=>$begin_time,
            'end_time'=>$end_time,
            'keyword'=>$keyword,
        );
        //dump($body);
        $this->assign($body);
        $this->display();
    }
    //确认生成报告
    public function doneConfirm(){
        $rs = array('msg'=>'fail');
        $centreno=I("centreno");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $where= "centreno='{$centreno}'";
        $data=array(
            'status'=>1,
            'report_time'=>date("Y-m-d H:i:s"),
            'report_user_id'=>$userid,
        );
        if(D("contract_flow")->where($where)->save($data)){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }
	//申请修改
	public function doSubmitFeedback(){
		$rs = array('msg'=>'fail');
		$centreno = I('centreno');
		$reason = I('reason');
		
		$type_status = I('type_status',0,'intval')== 6?1:0;
		//pr($type_status);
		$data = array(
			'centreNo'=>$centreno,
			'reason'=>$reason,
			'if_outer'=>$type_status
		);
		M()->startTrans();
		if(D('report_feedback')->add($data)){
			$rs['msg']='申请成功';
			M()->commit();	
		}else{
			$rs['msg']='申请失败';
			M()->rollback();		
		}
		$this->ajaxReturn($rs);
	}
	
	//更改补充检验报告单
	public function addorEditReport(){
		$centreNo = I('id');
		$count = D('inspection_report')->where('centreNo="'.$centreNo.'"')->count();
		$one = D('inspection_report')->where('centreNo="'.$centreNo.'"')->find();
		$body = array(
			'centreNo'=>$centreNo,
			'count'=>$count,
			'one'=>$one
		);
		
		$this->assign($body);
		$this->display();
	}
	
	//检验报告单录入
	public function saveInspecReport(){
		$edit_No = I('edit_No');
		$centreNo = I('centreNo');
		$sampleName = I('sampleName');
		$clientName = I('clientName');
		$update_item = I('update_item');
		$update_item_list = "";
		for($i = 0;$i < 6;$i++){
			$update_item_list.=$update_item[$i]."/&&/";
		}
		$update_reason = I('update_reason');
		$applicant = I('applicant');
		$handler = I('handler');
		$handleDate = I('handleDate');
		$data_list = array(
			"handler"=>$handler,
			'handleDate'=>$handleDate,
			'edit_No'=>$edit_No,
			'centreNo'=>$centreNo,
			'sampleName'=>$sampleName,
			'clientName'=>$clientName,
			'update_item'=>$update_item_list,
			'update_reason'=>$update_reason,
			'applicant'=>$applicant,
		);
		$rs['msg']='fail';
		if(D('inspection_report')->add($data_list)){
			$rs['msg']='succ';
		}
		$this->ajaxReturn($rs);
		
	}

	//获取最中心编号
	public function getLastCode(){
		$centreNo['re']='none';
		$year=I("year");
		$month=I("month");
		$centreHead=$year.$month;
		$list = D("contract")->field('centreNo',SUBSTR(centreNo,9,3))->where('centreNo like "'.$centreHead.'%" and SUBSTR(centreNo,9,3)>100')->order('SUBSTR(centreNo,9,3) desc')->select();
		//pr(D("contract")->getLastSql());
		if(count($list)>0){
			$centreNo['re']= $list[0]['centreno'];	
		}
		
		//dump($list[0]['centreno']);
		$this->ajaxReturn($centreNo);
	}
	
		//获取新最优质中心编号
	public function getHighCode(){
		$centreNo['re']='none';
		$year=I("year");
		$month=I("month");
		$centreHead=$year.$month;
		$list = D("contract")->field('centreNo',SUBSTR(centreNo,9,3))->where('centreNo like "'.$centreHead.'%" and SUBSTR(centreNo,9,3)<100')->order('SUBSTR(centreNo,9,3) desc')->select();
		//$count = D("contract")->field('count(*) as num')->where('centreNo like "'.$centreHead.'%" and SUBSTR(centreNo,9,3)<100')->order('SUBSTR(centreNo,9,3) desc')->select();
		//pr(D("contract")->getLastSql());
		if(count($list)>0){
			$centreNo['re']= $list[0]['centreno'];	
		}
		//$centreNo['count'] = $count[0]['num'];
		//pr($centreNo['count']);
		//dump($list[0]['centreno']);
		$this->ajaxReturn($centreNo);
	}
	
	//费用查询
	public function feeManage(){
		$criteria = I('criteria');
		//$test_fee_list = D("test_fee")->where('criteria like %'.$criteria.'%')->select();
		$admin_auth = session("admin_auth");
		$if_admin = $admin_auth['super_admin'];
		$roleid = $admin_auth['gid'];
		
		$role = D('common_role')->where('id='.$roleid)->find();
		if($role['rolename']=="领导" || $if_admin==1){
			$if_leader = 1;	
		}else{
			$if_leader = 0;	
		}
		$page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;

        $list = D("test_fee")->where('criteria like "%'.$criteria.'%"')->limit("{$offset},{$pagesize}")->select();
        //pr(D("test_fee")->getLastSql());
        $count = D("test_fee")->where('criteria like "%'.$criteria.'%"')->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            "fee_list"=>$list,
            'pagination'=>$pagination,
            'if_leader'=>$if_leader,
			'pagination'=>$pagination,
        );
        $this->assign($body);
        $this->display();
    }


    //费用列表
    public function findMetList(){
        $meterial_list=D("test_fee")->field("meterial")->group("meterial")->select();
        if($productname!=0 && $meterial!=0){
            $productname_list=D("test_fee")->field("productname")->where('meterial="'.meterial.'"')->group("productname")->select();
        }

        $rs = array(
            "meterial_list"=>$meterial_list,
            'productname_list'=>$productname_list,
        );
        $this->ajaxReturn($rs);
    }

    public function findProList(){
        $meterial = I('m_select');
        $productname_list=D("test_fee")->field("productname,criteria")->where('meterial="'.$meterial.'"')->group("productname")->select();
        //pr(D("test_fee")->getLastSql());
        $rs = array(
            'productname_list'=>$productname_list,
        );
        $this->ajaxReturn($rs);
    }

    public function findItemList(){
        $meterial = I('m_select');
        $productname = I('p_select');
        $item_list=D("test_fee")->field("item,fee")->where('meterial="'.$meterial.'" and productname="'.$productname.'"')->select();
        //pr(D("test_fee")->getLastSql());
        $rs = array(
            'item_list'=>$item_list,
        );
        $this->ajaxReturn($rs);
    }

    //显示特殊编码
    public function findSpecialCode(){
        $admin_auth = session("admin_auth");
        $department = $admin_auth['department'];
        $if_admin = $admin_auth['super_admin'];
        $where=array();

        //特殊编码管理员和该部门都可见
        if($if_admin!=1) $where['department']=$department;

        $specialList = D("special_centre_code")->where($where)->select();
        //pr(D("special_centre_code")->getLastSql());
        //$year=array();
        $codeList=array();
        $numList=array();
        foreach($specialList as $special){
            //array_push($year,$special->year);
            $year = $special['year'];
            $month = str_pad($special['month'],2,"0",STR_PAD_LEFT);
            $num = $special['remainnum'];
            $department = $special['department'];
            $centreHead=$year.$month;
            //SELECT centreNo,SUBSTR(centreNo,9,3) from contract where ifHighQuantity=0 order by SUBSTR(centreNo,9,3) desc
            $special = D("contract")->field('centreNo,SUBSTR(centreNo,9,3) as codes')->where('centreNo like "'.$centreHead.'%" and ifHighQuantity=0')->order('SUBSTR(centreNo,9,3) desc')->find();
            //pr($special);
            //pr(D("contract")->getLastSql());
            //pr(count($special));
            if(count($special)==0){
                $code=100;
            }else{
                $code = (int)$special['codes'];
                //pr($code);
            }
            /*for($i=0;$i<$num;$i++){
                $code = $code+1;
                $code3=str_pad($code,3,"0",STR_PAD_LEFT);
                $special_no=$centreHead.$department.'W'.$code3;
                array_push($codeList,$special_no);
            }*/
            $code = $code+1;
            $code3=str_pad($code,3,"0",STR_PAD_LEFT);
            $special_no=$centreHead.$department.'W'.$code3;
            array_push($codeList,$special_no);
            array_push($numList,$num);
        }

        //pr(D("contract")->getLastSql());
        //if(count($list)>0){
        //	$centreNo['re']= $list[0]['centreno'];
        //}

        $rs = array(
            //'special_list'=>$specialList,
            'codeList'=>$codeList,
            'numList'=>$numList
        );
        $this->ajaxReturn($rs);
    }


    //检验报告单详情
    public function checkDetail(){
        $body=array();
        $this->assign($body);
        $this->display();
    }

    //抽样单
    public function sampleDetail(){
        $centreno = I("id");
        $samdetail = D("contract")->where("centreNo=".$centreno)->find();
        $body = array();
        $this->assign($body);
        $this->display();
    }

    //费用标准修改数据回显
    public function doUpdateFee(){
        $id = I('id');

        $rs = D("test_fee")->where('id='.$id)->find();
        $this->ajaxReturn($rs);
    }


    //费用标准修改
    public function updateFee(){
        $rs['msg'] = 'fail';
        $id = I('id');
        $meterial = I('meterial_edit');
        $criteria = I('criteria_edit');
        $productname = I('productname_edit');
        $item = I('item_edit');
        $samplequantity = I('samplequantity_edit');
        $testperiod = I('testperiod_edit');
        $remark = I('remark_edit');
        $fee = I('fee_edit');
        $quantity = I('quantity_edit');

        $where['meterial']=$meterial;
        $where['criteria']=$criteria;
        $where['productname']=$productname;
        $where['item']=$item;
        $where['sampleQuantity']=$samplequantity;
        $where['testPeriod']=$testperiod;
        $where['remark']=$remark;
        $where['fee']=$fee;
        $where['quantity']=$quantity;

        if(D("test_fee")->where('id='.$id)->save($where)){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }

    //费用标准添加
    public function doAddFee(){
        $rs['msg'] = 'fail';
        $meterial = I('meterial');
        $criteria = I('criteria');
        $productname = I('productname');
        $item = I('item');
        $samplequantity = I('samplequantity');
        $testperiod = I('testperiod');
        $remark = I('remark');
        $fee = I('fee');
        $quantity = I('quantity');

        $where['meterial']=$meterial;
        $where['criteria']=$criteria;
        $where['productname']=$productname;
        $where['item']=$item;
        $where['sampleQuantity']=$samplequantity;
        $where['testPeriod']=$testperiod;
        $where['remark']=$remark;
        $where['fee']=$fee;
        $where['quantity']=$quantity;
        M()->startTrans();
        try{
            if(D("test_fee")->add($where)){
                $rs['msg'] = 'succ';
                M()->commit();
            }
        }catch(Exception $e){
            M()->rollback();
        }
        $this->ajaxReturn($rs);
    }


    //费用标准删除
    public function doDeleteFee(){
        $id = I('id');
        M()->startTrans();
        if(D("test_fee")->where('id='.$id)->delete()){
            $rs['msg'] = '删除成功';
            M()->commit();
        }else{
            $rs['msg'] = '删除失败';
            M()->rollback();
        }
        $this->ajaxReturn($rs);
    }
}
?>