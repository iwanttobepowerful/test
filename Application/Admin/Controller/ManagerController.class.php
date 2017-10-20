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
        $data=$contract->where($where)->field('ifHighQuantity,remark1,remark2',ture)->select();
        $body=array(
            'data'=>$data,
        );
        $this->assign($body);
        $this->display();
    }
    //检验记录详情
    public function testRecordDetail(){
        $centreno=I("id");
        $contract=D("contract");//实例化
        $where= "centreno='{$centreno}'";
        $data=$contract->where($where)->field('centreNo')->select();
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
        $data=$contract->where($where)->field('centreNo')->select();
        $body=array(
            'data'=>$data,
        );
        $this->assign($body);
        $this->display();
    }
	
	//特殊号段签发
	public function issueSepcialCode(){
		$list = D("special_centre_code")->select();
		
		$body = array(
			"special_list"=>$list,
		);
	    $this->assign($body);
		$this->display();
	}
	
}