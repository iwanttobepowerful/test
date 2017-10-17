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
        $where['authorizer']=1;
        $where['ifinnerissue']=1;
        $where['ifouterissue']=0;
        $rs1=$test_reprot->where($where)->field('id,centreNo,productUnit')->select();
        $rs2=$contract->field('clientSign,telephone')->select();
        $rs=$rs1+$rs2;
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



}
?>