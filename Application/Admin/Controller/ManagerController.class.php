<?php
/**
 * Created by PhpStorm.
 * User: Ail
 * Date: 2017/10/18
 * Time: 13:45
 */
namespace Admin\Controller;
use Think\Controller;
class ManagerController extends Controller
{
    public $user = null;

    public function _initialize()
    {
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active', strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active', strtolower(ACTION_NAME));
    }

    public function InfoSelect(){
        $this->display();
    }
    //检验报告批准
    public function authorizeTestReprot(){
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $test_reprot=D("test_reprot");//实例化对象
        $where['authorizer']=0;
        $where['ifinnerissue']=0;
        $rs=$test_reprot->where($where)->field('id,centreNo')->order('id')->limit("{$offset},{$pagesize}")->select();//查找条件为已经批准并且内部尚未签发的报告
        $count = D("test_reprot")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            'rs'=>$rs,
            'pagination'=>$pagination,
        );
        $this->assign($body);
        $this->display();
    }
    //批准按钮功能实现

    public function doUpd(){
// 要修改的数据对象属性赋值
        $id =I("id",0,'intval');
        $data['authorizer'] = 1;
        $rs = array("msg"=>"fail");
        if(D("test_reprot")->where("id=".$id)->save($data)){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }
    //合同详情查询
    public function contractDetail(){
        $centreno=I("id");
        $contract=D("contract");//实例化
        $where= "centreno='{$centreno}'";
        $data=$contract->where($where)->field('ifHighQuantity,remark1,remark2',ture)->find();
		$cost=D("test_cost")->where('centreno="'.$centreno.'"')->find();
        $body=array(
            'one'=>$data,
			'cost'=>$cost
        );
        $this->assign($body);
        $this->display();
    }
    //检验记录详情
    public function testRecordDetail(){
        $centreno=I("id");
        $test_record=D("test_record");//实例化find
        $where= "centreno='{$centreno}'";
        $data=$test_record->where($where)->field('recordName,remark')->find();
        //$con=$test_record->where($where)->field('remark')->find();
        //$con[‘remark‘] = htmlspecialchars_decode(html_entity_decode($con[‘remark‘]));
        $body=array(
            'data'=>$data,
        );
        $this->assign($body);
        $this->display();
    }
    //检验报告详情
    public function testReportDetail(){
        $centreno=I("id");
        $contract=D("contract");//实例化
        $where= "centreno='{$centreno}'";
        $data=$contract->where($where)->field('centreNo')->find();
        $body=array(
            'data'=>$data,
        );
        $this->assign($body);
        $this->display();
    }
	
	//特殊号段签发
	public function issueSepcialCode(){
		$list = D("special_centre_code")->field('*,getNum-remainNum as useNum')->select();
		
		$body = array(
			"special_list"=>$list,
		);
	    $this->assign($body);
		$this->display();
	}
	
	//特殊号段添加
	public function doAddSpecialCode(){
		$department = I("department");
		$year = I("year");
		$month = I("month");
		$getNum = I("getNum");
		$remark = I("remark",'');
		
		$rs = array("msg"=>'fail');
		if(empty($year)||$year==''||empty($getNum)||$getNum==''){
			$rs['msg'] = '信息填写不完整!';
			$this->ajaxReturn($rs);
		}
		$data = array(
			"department"=>$department,
			"year"=>$year,
			"month"=>$month,
			"getNum"=>$getNum,
			'remainNum'=>$getNum,
			"remark"=>$remark,
			'getDate'=>Date("Y-m-d H:i:s")
		);
		if(D("special_centre_code")->data($data)->add()){
			$rs['msg'] = 'succ';
		}else{
			$rs['msg'] = '输入信息有误';
		}
		$this->ajaxReturn($rs);
	}
	
}