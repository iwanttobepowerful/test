<?php
/**
 * Created by PhpStorm.
 * User: Ail
 * Date: 2017/10/18
 * Time: 13:45
 */
namespace Admin\Controller;
use Think\Controller;
class ReportController extends Controller
{
    public $user = null;

    public function _initialize()
    {
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active', strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active', strtolower(ACTION_NAME));
    }
    //报告审核
    public function auditReport(){
        $this->display();
    }
    //报告审批
    public function authorizeReport(){
        $this->display();
    }
    //内部签发
    public function internalIssue(){
        $page = I("p",'int');
        $pagesize = 20;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $contract_flow=M("contract_flow");//实例化对象
        $where['status']=5;
        //$where['ifinnerissue']=0;
        $rs=$contract_flow->where($where)->field('id,centreNo')->order('id')->limit("{$offset},{$pagesize}")->select();//查找条件为已经批准并且内部尚未签发的报告
        $count = D("contract_flow")->where($where)->count();
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
    //签发按钮功能实现

    public function doUpd(){
// 要修改的数据对象属性赋值
        $data['status'] = 6;
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        if(D("")->where("id=".$id)->save($data)){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }

    //外部签发
    public function externalIssue(){
        $this->display();
    }
    //报告模板
    public function templateReport()
        {
            $page = I("p", 'int');
            $pagesize = 20;
            if ($page <= 0) $page = 1;
            $offset = ($page - 1) * $pagesize;
            $orderby = "create_time desc";
            $result = D("tpl")->limit("{$offset},{$pagesize}")->select();
            $count = D("tpl")->count();
            $Page = new \Think\Page($count, $pagesize);
            $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
            $pagination = $Page->show();// 分页显示输出

            $body = array(
                'lists' => $result,
                'pagination' => $pagination,
            );
            $this->assign($body);
            $this->display();
        }

        public function doUploadReport()
        {
            $id = I("id", 0, 'intval');
            $imgurl = I("imgurl");
            $filename = I("filename");
            $result = array("msg" => "fail");
            if (empty($imgurl)) {
                $result['msg'] = "无效的提交！";
                $this->ajaxReturn($result);
            }
            $data = array("path" => $imgurl, "filename" => $filename);
            $report = D("tpl")->where("id=" . $id)->find();
            if ($report) {
                if (D("tpl")->where("id=" . $report['id'])->save($data)) {
                    $result['msg'] = 'succ';
                }
            } else {
                if (D("tpl")->data($data)->add()) {
                    $result['msg'] = 'succ';
                }
            }
            $this->ajaxReturn($result);
        }

        public function updateReport()
        {

            $id = I("id", 0, 'intval');
            if ($id) {
                $report = D('tpl')->where("id=" . $id)->find();
            }

            $body = array(
                'report' => $report,
            );
            $this->assign($body);
            $this->display();
        }

        public function doDeleteReport()
        {
            $id = I("id", 0, 'intval');
            $rs = array("msg" => "fail");
            if (D("tpl")->where("id=" . $id)->delete()) {
                $rs['msg'] = 'succ';
            }
            $this->ajaxReturn($rs);
        }

    }
