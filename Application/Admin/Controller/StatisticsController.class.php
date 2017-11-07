<?php
namespace Admin\Controller;
use Think\Controller;
class StatisticsController extends Controller {
    public $user = array();
    //初始化方法
    public function _initialize(){
        load('@.functions');
        $this->user = D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }

    public function base(){
        $keyword = I("keyword");
        $where = " status=6";
        if(!empty($keyword)){
            //查询合同编号
            $where .=" and centreno='{$keyword}'";
        }
        $list = D("contract_flow")->where($where)->order("external_sign_time desc")->select();
        if($list){
        	$centrenoIds = array();
        	foreach ($list as $value) {
        		$centrenoIds[] = "'".$value['centreno']."'";
        	}
        	$cost = D("test_cost")->where("centreno in(".implode(",", $centrenoIds).")")->select();
        	$contract = D("contract")->where("centreno in(".implode(",", $centrenoIds).")")->select();
        	$cost && $cost = assColumn($cost,'centreno');
        	$contract && $contract = assColumn($contract,'centreno');

        	foreach ($list as $key => $value) {
        		$value['test_cost'] = $cost[$value['centreno']] ? $cost[$value['centreno']]:array();
        		$value['contract'] = $contract[$value['centreno']] ? $contract[$value['centreno']]:array();
        		$list[$key] = $value;
        	}
        }
        $count = D("contract_flow")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出

        $body = array(
        	 'pagination'=>$pagination,
        	'lists'=>$list,
        );
        $this->assign($body);
        $this->display();
    }
}