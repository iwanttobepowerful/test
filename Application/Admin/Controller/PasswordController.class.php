<?php
namespace Admin\Controller;
use Think\Controller;
class PasswordController extends Controller {
	public $user = array();
    //初始化方法
    public function _initialize(){
        load('@.functions');
        $this->user = D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }
	
	public function password(){
		 $stamp = D('common_system_user')->find();
        $body = array(
            'password' => $password,
        );
        $this->assign($body);
       $this->display();
    }
    public function doUpdatePassword(){
		//$this->user=d("account")->checkLogin();//这个会返回当前登录的信息，
		//session('user',$this->user);
		//$username = session('user.username');
		//$where=array('id'=>$this->user['id']);
        $oldpassword = I("oldpassword");
        $newpassword = I("newpassword");
		$newpasswordagain = I("newpasswordagain");

		if(trim($newpassword) != trim($newpasswordagain)){
            $result['msg'] = "两次输入的新密码不一致，请重新输入！";
            $this->ajaxReturn($result);
        }
		
		$newpassword = SHA256Hex($newpassword);
		$oldpassword = SHA256Hex($oldpassword);
		
				
		$admin = D("common_system_user")->where("id=".$this->user['id'])->find();
		//pr($admin);打印admin所有的信息
						
		if($admin['passwd'] != $oldpassword){
			$result['msg'] = "输入的旧密码错误，请重新输入";
            $this->ajaxReturn($result);
		}
		
   		$updatepassword = D("common_system_user")->where("id=".$this->user['id'])->setField('passwd',$newpassword);
  		if($updatepassword){
            $result['msg'] = 'succ';
        }else{
            $result['msg'] = '修改密码失败！';
			
        }
		$this->ajaxReturn($result);
    }
}