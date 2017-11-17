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
        $centreno = I("centreno");
        $begin_time = I("begin_time");
        $end_time = I("end_time");
        $sortby = I("sortby");
        $where = " a.status in(5,6)";

        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;

        if(!empty($centreno)){
            //查询合同编号
            $where .=" and a.centreno like '%{$centreno}%'";
        }
        if($sortby==1){
            //盖章日期
            $orderby = "a.inner_sign_time desc";
            $begin_time && $where .=" and date_format(a.inner_sign_time,'%Y-%m-%d') >='{$begin_time}'";
            $end_time && $where .=" and date_format(a.inner_sign_time,'%Y-%m-%d') <='{$end_time}'";

        }elseif($sortby==2){
            //来样日期
            $orderby = "b.collectdate desc";
            $begin_time && $where .=" and date_format(b.collectdate,'%Y-%m-%d') >='{$begin_time}'";
            $end_time && $where .=" and date_format(b.collectdate,'%Y-%m-%d') <='{$end_time}'";
        }
        else{
            $orderby = "a.external_sign_time desc";//默认排序
        }
        //小计
        $sumlist = D("contract_flow")->alias("a")->join(C("DB_PREFIX")."contract b on a.centreno=b.centreno","LEFT")->join(C("DB_PREFIX")."test_cost c on a.centreno=c.centreno","LEFT")->where($where)->field("sum(b.testcost) as testcost,sum(c.arecord) as arecord,sum(c.brecord) as brecord,sum(c.crecord) as crecord,sum(c.drecord) as drecord,sum(c.erecord) as erecord,sum(c.frecord) as frecord,sum(c.dcopy) as dcopy,sum(c.drevise) as drevise,sum(c.dother) as dother,sum(c.donline) as donline")->find();
        $list = D("contract_flow")->alias("a")->join(C("DB_PREFIX")."contract b on a.centreno=b.centreno","LEFT")->join(C("DB_PREFIX")."test_cost c on a.centreno=c.centreno","LEFT")->where($where)->order($orderby)
            ->field("a.id,a.status,a.external_sign_time,a.inner_sign_time,a.centreno,a.takelist_user_id,b.clientname,b.productunit,b.samplename,b.samplecode,b.testitem,b.testcost,b.remark,b.testcriteria,b.collectdate,b.samplequantity,b.collector,c.arecord,c.brecord,c.crecord,c.drecord,c.erecord,c.frecord,c.dcopy,c.donline,c.drevise,c.dother")->limit("{$offset},{$pagesize}")->select();
        
        if($list){            
        	$centrenoIds = array();
            $userIds = array();
        	foreach ($list as $value) {
        		$centrenoIds[] = "'".$value['centreno']."'";
                $value['takelist_user_id'] && $userIds[] = $value['takelist_user_id'];
        	}
        	$userIds && $user = D("common_system_user")->where("id in(".implode(',', $userIds).")")->field("id,name")->select();
            $user && $user = assColumn($user);
        	foreach ($list as $key => $value) {
                $value['takelist_user'] = $value['takelist_user_id'] ? $user[$value['takelist_user_id']]['name']:"";
                $list[$key] = $value;
            }
        }
        $count = D("contract_flow")->alias("a")->join(C("DB_PREFIX")."contract b on a.centreno=b.centreno","LEFT")->join(C("DB_PREFIX")."test_cost c on a.centreno=c.centreno","LEFT")->where($where)->field("count(*) as total")->select();
        $Page= new \Think\Page(intval($count[0]['total']),$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出

        $body = array(
        	 'pagination'=>$pagination,
        	'lists'=>$list,
            'sum'=>$sumlist,
            'centreno'=>$centreno,
            'begin_time'=>$begin_time,
            'end_time'=>$end_time,
            'sortby'=>$sortby,
        );
        $this->assign($body);
        $this->display();
    }
}