<?php
namespace Wap\Controller;
use Think\Controller;
class UserController extends Controller {
    public $user = array();
    //初始化方法  -- 
    public function _initialize(){
        load('@.functions');
        $user = D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }
    //修改密码页面
    public function updatePassword(){
       // $admin_info = M('common_system_user')->find($admin_id);
        $body = array(
            "pagetitle"=>"修改密码",
            'backed'=>true,
        );
        $this->assign($body);
        $this->display();
    }
    //保存修改信息
    public function save(){
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $admin_id= $admin_auth['id'];
        $oldpassword  = I('oldpassword');
        $newpassword  =I('newpassword');
        $newpasswordagain =I('newpasswordagain');
        //是否一致
        if($newpassword !== $newpasswordagain){
           $rs['msg']='两次密码输入不一致!';
            $this->ajaxReturn($rs);
        }
        $newpassword = SHA256Hex($newpassword);
        $oldpassword = SHA256Hex($oldpassword);


        $admin = D("common_system_user")->where("id=".$admin_id)->find();
        if($admin['passwd'] != $oldpassword){
            $rs['msg'] = "输入的旧密码错误，请重新输入";
            $this->ajaxReturn($rs);
        }
        //dump($admin_id);die;
        $updatepassword = D("common_system_user")->where("id=".$admin_id)->setField('passwd',$newpassword);
        if($updatepassword){
            $rs['msg'] = 'succ';
        }else{
            $rs['msg'] = '修改密码失败！';

        }
        $this->ajaxReturn($rs);


    }
}
