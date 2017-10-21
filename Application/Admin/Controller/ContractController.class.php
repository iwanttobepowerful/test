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
		$sampleQuantity = I("sampleQuantity",0,'intval');
		$sampleunti = I("sampleunti");
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
		
		
		$rs = array("msg"=>'fail');
		if(empty($reportDate)||empty($collectDate)||empty($productionDate)){
			$rs['msg'] = '信息填写不完整(把日期都填上测试)!';
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
			"ifHighQuantity"=>$ifHighQuantity
		);
		//pr($data);
			if(D("contract")->data($data)->add()){
				$rs['msg'] = 'succ';
			}else{
				$rs['msg'] = '输入信息有误';
			}
			$this->ajaxReturn($rs);
	}
	
	//生成检验工作单
	public function checkWorkList(){
		$this->display();
	}

	//特殊号段查询
	public function specialCodeSelect(){
		$list = D("special_centre_code")->select();
		
		$body = array(
			"special_list"=>$list,
		);
		//dump($body);
	    $this->assign($body);
		$this->display();
	}

	//合同列表
	public function showList(){
		
		$page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
		
		$list = D("contract")->limit("{$offset},{$pagesize}")->select();
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
		$productname_list=D("test_fee")->field("productname")->where('meterial="'.$meterial.'"')->group("productname")->select();
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
		$specialList = D("special_centre_code")->select();
		//$year=array();
		$codeList=array();
		foreach($specialList as $special){
			//array_push($year,$special->year);
			$year = $special['year']; 
			$month = $special['month'];
			$num = $special['getnum'];
			$department = $special['department'];
			$centreHead=$year.$month;
			//SELECT centreNo,SUBSTR(centreNo,9,3) from contract where ifHighQuantity=0 order by SUBSTR(centreNo,9,3) desc
			$special = D("contract")->field('centreNo,SUBSTR(centreNo,9,3) as codes')->where('centreNo like "'.$centreHead.'%" and ifHighQuantity=0')->order('SUBSTR(centreNo,9,3) desc')->find();
			//pr($special);
			//pr(D("contract")->getLastSql());
			//pr(count($special));
			if(count($special)==0){
				$code=0;
			}else{
				$code = (int)$special->codes;
				
			}
			for($i=1;$i<=$num;$i++){
				$code = $code+$i;
				$code=str_pad($code,3,"0",STR_PAD_LEFT);
				$code=$centreHead.$department.'W'.$code;
				array_push($codeList,$code);
			}
		}

		//pr(D("contract")->getLastSql());
		//if(count($list)>0){
		//	$centreNo['re']= $list[0]['centreno'];	
		//}

		$rs = array(
			//'special_list'=>$specialList,
			'codeList'=>$codeList
		);
		$this->ajaxReturn($rs);
	}
}
?>