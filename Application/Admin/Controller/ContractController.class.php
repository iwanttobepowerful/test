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
	
	//进入合同录入页面
	public function input(){
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
		$sampleunti = I("sampleunti");
		$sampleStatus = I("sampleStatus");
		$ration = I("ration");
		$testCriteria = I("testCriteria");
		$testItem = I("testItem");
		$testCategory = I("testCategory");
		$ifOnline = I("ifOnline");
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
		$testCost = I("testCost");
		$collectDate = I("collectDate");
		$reportDate = I("reportDate");
		$ifHighQuantity = I("ifHighQuantity");
		
		
		$rs = array("msg"=>'fail');
		if(empty($clientName)){
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
			"sampleunti"=>$sampleunti,
			"sampleStatus"=>$sampleStatus,
			"ration"=>$ration,
			"testCriteria"=>$testCriteria,
			"testItem"=>$testItem,
			"testCategory"=>$testCategory,
			"ifOnline"=>$ifOnline,
			"postMethod"=>$postMethod,
			"ifSubpackage"=>$ifSubpackage,
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
		);
			
			if(D("contract")->add($data)){
				$rs['msg'] = 'succ';
			}else{
				$rs['msg'] = '输入信息有误';
			}
			$this->ajaxReturn($rs);
	}

    public function getTestReport(){
        $this->display();
    }
    public function issueTestReport(){
        $this->display();
    }
}