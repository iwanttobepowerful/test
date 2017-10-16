<?php
namespace Admin\Controller;
use Think\Controller;
class OtherController extends Controller {
    //初始化方法
    public function _initialize(){
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }
    public function stamp(){
        $id =I("id",0,'intval');
        if($id){
            $stamp = D('offcial_seal')->where("id=".$id)->find();
        }
        
        $body = array(
            'stamp' => $stamp,
        );
        $this->assign($body);
       $this->display();
    }
    public function doUploadStamp(){
        $id = I("id",0,'intval');
        $imgurl = I("imgurl");
        $remark = I("remark");
        $result = array("msg"=>"fail");
        if(empty($imgurl)){
            $result['msg'] = "无效的提交！";
            $this->ajaxReturn($result);
        }
        $data = array("offcial_seal"=>$imgurl,"remark"=>$remark);
        $stamp = D("offcial_seal")->where("id=".$id)->find();
        if($stamp){
            if(D("offcial_seal")->where("id=".$stamp['id'])->save($data)){
                $result['msg'] = 'succ';
            }
        }else{
            if(D("offcial_seal")->data($data)->add()){
                $result['msg'] = 'succ';
            }
        }
        $this->ajaxReturn($result);
    }
    public function stampList(){
        $page = I("p",'int');
        $pagesize = 20;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $orderby = "create_time desc";
        $result = D("offcial_seal")->limit("{$offset},{$pagesize}")->select();
        $count = D("offcial_seal")->count();
        $Page       = new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination       = $Page->show();// 分页显示输出

        $body = array(
            'lists'=>$result,
            'pagination'=>$pagination,
        );
        $this->assign($body);
        $this->display();
    }
}