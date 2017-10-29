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
		$ration = I("ration",0,'intval');
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
		
		//费用详情
		$testFee = I("testCost1",0,'intval');
		$Drecord = I("testCost2",0,'intval');
		$Dcopy = I("testCost3",0,'intval');
		$Drevise = I("testCost4",0,'intval');
		
		$ifspecial = I("ifspecial");//是否是特殊编码
		
		$rs = array("msg"=>'fail');
		if(empty($clientName)||empty($productUnit)||empty($sampleName)||empty($testCriteria)||empty($testItem)){
			$rs['msg'] = '信息填写不完整!';
			$this->ajaxReturn($rs);
		}
		if(empty($productionDate)){
			$productionDate=null;
		}
		if(empty($collectDate)){
			$collectDate=null;
		}
		if(empty($reportDate)){
			$reportDate=null;
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
		

		$date_cost =array(
			"centreNo"=>$centreNo,
			"testFee"=>$testFee,
			"Drecord"=>$Drecord,
			"Dcopy"=>$Dcopy,
			"Drevise"=>$Drevise,
			'costDate'=>Date("Y-m-d H:i:s")
		);
		
		try{
			M()->startTrans();
			$flag = true;
			
			//合同入库
			if(!D("contract")->data($data)->add()) $flag=false;
			
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
			$type = substr($centreNo,7,1);
			if($type=='C'){					
				$data_sample = array(
					"centreNo"=>$centreNo,				
					"productUnit"=>$productUnit,
					"sampleName"=>$sampleName,
					"specification"=>$specification,
					//缺产品批号
					"testCriteria"=>$testCriteria,
					"trademark"=>$trademark,
					"sampleQuantity"=>$sampleQuantity,
					//"sampleUnit"=>$sampleunti,
					"productionDate"=>$productionDate,
					"testItem"=>$testItem,
					"ifOnline"=>$ifOnline,
					"ifSubpackage"=>$ifSubpackage,
				);
				if(!D("sampling_form")->data($data_sample)->add()) $flag=false;	
				
				
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
	
	//生成检验工作单
	public function checkWorkList(){
		$this->display();
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

	//合同列表
	public function showList(){
		
		$keyword = I("keyword");//获取参数
        $where= "centreno like '%{$keyword}%'";
		$page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
		
		$list = D("contract")->where($where)->limit("{$offset},{$pagesize}")->select();
		$count = D("contract")->count();
		$Page= new \Think\Page($count,$pagesize);
		$Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
		$pagination= $Page->show();// 分页显示输出

		$body = array(
			"list"=>$list,
			'pagination'=>$pagination
		);
		//dump($body);
		$this->assign($body);
		$this->display();
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
		$page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
		
		$list = D("test_fee")->limit("{$offset},{$pagesize}")->select();
		$count = D("test_fee")->count();
		$Page= new \Think\Page($count,$pagesize);
		$Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
		$pagination= $Page->show();// 分页显示输出
		$body = array(
			"fee_list"=>$list,
			'pagination'=>$pagination
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
	
	//材料修改
	public function doUpdateFee(){
		$id = I('id');

		$rs = D("test_fee")->where('id='.$id)->find();
		$this->ajaxReturn($rs);
	} 
	
	
	//材料修改
	public function updateFee(){
		$rs['msg'] = 'fail';
		$id = I('id');
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
		
		if(D("test_fee")->where('id='.$id)->save($where)){
			$rs['msg'] = 'succ';
		}
		$this->ajaxReturn($rs);
	} 
	
	
		//材料修改
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
		
		if(D("test_fee")->add($where)){
			$rs['msg'] = 'succ';
		}
		$this->ajaxReturn($rs);
	} 
}
?>