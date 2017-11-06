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
        $admin_id          = I('id');
        $admin_name          = I('username', '');
        $old_admin_password  = md5(I('old_admin_password', ''));
        $admin_password      =md5( I('admin_password', ''));
        $re_admin_repassword = md5(I('re_admin_repassword', ''));

        //是否一致
        if($admin_password !== $re_admin_repassword){
            $this->error('两次密码输入不一致');
        }

        //原始用户名和密码是否正确
        $filter = array(
            'username' => $admin_name,
            'password' => $old_admin_password
        );

        $admin_info = M('common_system_user')->where($filter)->find();
        if(!$admin_info){
            $this->error('原始用户名或密码错误');
        }else{
            //更新管理员信息
            $admin_info = array(
                'password' => $re_admin_repassword
            );
            $result = M('common_system_user')->where(array('id' => $admin_id))->save($admin_info);
            if($result){
                $this->ajaxReturn(array('status' => 'ok', 'info' => '管理员信息修改成功'));
            }else{
                $this->ajaxReturn(array('status' => 'error', 'info' => '管理员信息修改失败'));
            }
        }

    }
}
