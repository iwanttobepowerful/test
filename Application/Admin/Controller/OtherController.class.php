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
        $stamp = D('offcial_seal')->find();
        $body = array(
            'stamp' => $stamp,
        );
        $this->assign($body);
       $this->display();
    }
    public function doUploadStamp(){
        $imgurl = I("imgurl");
        $remark = I("remark");
        $result = array("msg"=>"fail");
        if(empty($imgurl)){
            $result['msg'] = "无效的提交！";
            $this->ajaxReturn($result);
        }
        $data = array("offcial_seal"=>$imgurl,"remark"=>$remark);
        $stamp = D("offcial_seal")->find();
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
}