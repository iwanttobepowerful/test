<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends Controller {
    //初始化方法
    public function _initialize(){
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }
    public function index(){
        /*
        $orders = M('orders');
        $auth_group_access = M('auth_group_access');
        $admin_count = $auth_group_access->where('group_id = 1')->count();
        $user_count = $auth_group_access->where('group_id = 2')->count();
        $orders_count = $orders->count();
        $this->assign('admin_count',$admin_count);
        $this->assign('user_count',$user_count);
        $this->assign('orders_count',$orders_count);
        $this->display();
        */
       $this->display("Common/main");
    }
    /**
     * 后台图片上传处理接口
     * @Author   3246899071@qq.com
     * @DateTime 2017-06-26T20:09:32+0800
     * @return   [type]                   [description]
     */
    public function upload_json(){
    	$ret = upLoadOne('imgFile');
    	if($ret['status'] == 'ok'){
    		$data['error'] = 0;
    		$data['url'] = $ret['img_url'];
    	}else{
    		$data['error'] = 1;
    		$data['message'] = $ret['msg'];
    	}
    	$this->ajaxReturn($data);
    }

}