<?php
namespace Admin\Controller;
use Think\Controller;
class AuthController extends Controller {
	public $user = null;
	private $name = '';
	private $url = '';
	private $pid = '';
	private $disorder = '';
	private $status = '' ;
	private $id = '' ;
	private $url_type = '';
	private $collapsed = 0 ; 
	private $top = array() ;

	public function _initialize(){ 
		load('@.functions');
		D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
	}
	public function navlist(){
		if(!D("account")->verifyPermission(CONTROLLER_NAME,ACTION_NAME)){
			$this->display("Common/warning");
			exit();
		}

		$result = D("common_admin_nav")->field("id,name,pid as parentid,url,status,addtime,disorder,path")->order("pid asc,disorder asc")->select();

		$list = array();
		if($result){
			foreach($result as $k=>$v){
				$list[$v['id']]  = $v ;
			}
		}
		$list = $this->genTree($list,'id','parentid','childs');
		$body = array(
			'list' => $list,
		);
		$this->assign($body);
        $this->display();
	}
	public function getData(){		
		$id = I("id",0,"intval");
		$rs = array('msg'=>'fail');
		if($id){
			$info = D("common_admin_nav")->where("id=".$id)->find();
			if(empty($info)){
				$rs['msg'] = "请传递正确的参数";
				$this->ajaxReturn($rs);
			}
			$list = D("common_admin_nav")->field("id,name,pid as parentid,url")->order("pid asc,disorder asc")->select();
			$result = array();
			if($list){
				foreach($list as $k=>$v){
					$result[$v['id']]  = $v ;
				}
			}
			$result = $this->genTree($result,'id','parentid','childs');
			$options =  $this->getChildren($result);
			$data = array(
				'info'=>(array) $info,
				'options'=>(array) $options,
			);
			$rs['msg'] = 'succ';
			$rs['list'] = $data;
		}else{
			$list = D("common_admin_nav")->field("id,name,pid as parentid,url")->order("pid asc,disorder asc")->select();
			$result = array();
			if($list){
				foreach($list as $k=>$v){
					$result[$v['id']]  = $v ;
				}
			}
			$result = $this->genTree($result,'id','parentid','childs');
			$options =  $this->getChildren($result);	
			$data['options'] = (array) $options ;
			$rs['msg'] = 'succ';
			$rs['list'] = $data;
		}
		$this->ajaxReturn($rs);
	}
	public function doAdd(){
		$this->pid = I("pid",0,"intval"); //pid
		$this->id = I("modid",0,"intval"); //id
		$this->name = I("name");//name
		$this->url = I("url");//url地址
		$this->disorder = I("disorder",0,'intval'); //排序
		$this->status = I("status",0,'intval'); //状态	
		$icon = I("icon");
		//$this->url_type = I("url_type",0,'intval'); //url类型	
		//$this->collapsed = I("collapsed",0,'intval'); //是否收缩
		if($this->id){
			$one = D("common_admin_nav")->where("id=".$this->id)->field("pid,path")->find(); 
			//递归查询所有的上级ID	
			$this->queryPid($one['pid']);
			$path_array = array_reverse($this->top) ;
			$path = implode("-" , $path_array);
			$newpath = '' ; 
			if($path == '0' ){
				$newpath = 0 ; 
			}else{
				array_pop($path_array);
				array_push($path_array , $this->pid) ;
				$newpath = implode("-" , $path_array);
			}
			$update = array(
				'pid'=>$this->pid,
				'name'=>$this->name,
				'url'=>$this->url,
				'disorder'=>$this->disorder,
				'status'=>$this->status,
				'path'=>$newpath,
				'icon'=>$icon,
			);
			$rs = array('msg'=>'fail');
			M()->startTrans();
			if(D("common_admin_nav")->where("id=".$this->id)->save($update)){
				//$restatus = $this->status ? 0:1;
				//$where = "path like '{$one['path']}-{$this->id}%' AND status=".$restatus;
				//$res = D("common_admin_nav")->where($where)->save(array("status"=>$this->status));
				$rs['msg'] = 'succ';
				M()->commit();
			}else{
				M()->rollback();
			}
			
			$this->ajaxReturn($rs);
		}else{
			$nav = D("common_admin_nav")->where("id=".$this->pid)->field("pid,path")->find();
			//递归查询所有的上级ID	
			$this->queryPid(isset($nav['pid']) ? $nav['pid']:0);

			$path_array = array_reverse($this->top) ;
			$path = implode("-" , $path_array);
			$newpath = '' ; 
			if($path == '0' ){
				$newpath = 0 ; 
			}else{
				array_pop($path_array);
				array_push($path_array , $this->pid) ;
				$newpath = implode("-" , $path_array);
			}
			
			$data = array(
				'pid'=>$this->pid,
				'name'=>$this->name,
				'status'=>$this->status,
				'addtime'=>date("Y-m-d H:i:s",time()),
				'url'=>$this->url,
				'disorder'=>$this->disorder,
				'path'=>$newpath
			);
			$rs = array('msg'=>'fail');
			M()->startTrans();
			if($insert_id = D("common_admin_nav")->data($data)->add()){

				$this->queryPid($data['pid']);
				$path_array = array_reverse($this->top) ;
				$path = implode("-" , $path_array);
				if(D("common_admin_nav")->where("id=".$insert_id)->save(array("path"=>$path))){
					$rs['msg'] = 'succ';
					M()->commit();
					$this->ajaxReturn($rs);
				}
			}
			M()->rollback();
			$this->ajaxReturn($rs);
		}
		
	}
	public function doDelete(){
		$id = I("id",0,'intval');
		$rs = array('msg'=>'fail');
		if($id){
			$info = D("common_admin_nav")->where("id=".$id)->find();
			if(empty($info)){
				$rs['msg'] = "请传递正确的参数";
				$this->ajaxReturn($rs);
			}
			if($info['status']){
				$rs['msg'] = "开启状态不能删除";
				$this->ajaxReturn($rs);
			}
			//子集有开启状态
			$where = "path like '{$info['path']}-{$id}%' AND status=1";
			$count = D("common_admin_nav")->where($where)->count();
			if($count){
				$rs['msg'] = "子菜单有开启状态不能删除";
				$this->ajaxReturn($rs);
			}
			M()->startTrans();
			if(D("common_admin_nav")->where("id=".$id)->delete()){
				D("common_admin_nav")->where("path like '{$info['path']}-{$id}%' AND status=0")->delete();
				$rs['msg'] = 'succ';
				M()->commit();
			}else{
				M()->rollback();
			}
		}
		$this->ajaxReturn($rs);
	}
	private function genTree($items,$id = 'id' ,$pid = 'pid' ,$child = 'children' ,$nodes=array(),$all = true) {

	    $tree = array(); //格式化好的树
	    $tmptree = array();
	    foreach ($items as $item){
	        $item['checked'] = 0;
	        if($nodes && in_array($item['id'],$nodes)){
		    	$item['checked'] = 1;
		    }
	        if($item[$pid]){
	        	
	           if($tree[$item[$pid]]){
	           		//二级菜单
	           		$tree[$item[$pid]][$child][$item['id']] = $item;
	           		if(!$all && !$tree[$item[$pid]][$child][$item['id']]['checked']){
	           			unset($tree[$item[$pid]][$child][$item['id']]);
	           		}
	           }else{
	           		$path = $item['path'];
	           		$patharr = explode('-', $path);

	           		if($patharr[1] && $tree[$patharr[1]]){
	           			if(!$all){
	           				if($item['checked']){
	           					$tree[$patharr[1]][$child][$patharr[2]][$child][] = $item;
	           				}
	           			}else{
	           				$tree[$patharr[1]][$child][$patharr[2]][$child][] = $item;
	           			}
	           		}
	           }
	           
	        }else{
	        	//一级菜单
	        	$tree[$item['id']] = $item;
	        	if(!$all && !$tree[$item['id']]['checked']){
	        		unset($tree[$item['id']]);
	        	}
	   		}
	   	}
	   	if($tree){
   			foreach ($tree as &$value) {
	   			if($value[$child]){
	   				$tmp  = array();
	   				foreach ($value[$child] as $one) {
	   					$tmp[] = $one;
	   				}
	   				$value[$child] = $tmp;
	   			}
	   		}
	   		sort($tree);	   		
	   	}
	   	
	    return $tree;
	}
	private function queryPid($id){		
		$data = D("common_admin_nav")->where("id=".$id)->field("pid ,id")->find();
		$this->top[] = isset($data['id'])?$data['id']:0;
		if(isset($data) && $data  ){		
			$this->queryPid(isset($data['pid'])?$data['pid']:0) ; 
		}
	}
	private function getChildren($parent,$deep=0) {
		foreach($parent as $row) {
			$data[] = array("id"=>$row['id'], "name"=>$row['name'],"pid"=>$row['parentid'],'deep'=>$deep,'url'=>$row['url']);
			if (isset($row['childs']) && !empty($row['childs'])) {
				$data = array_merge($data, $this->getChildren($row['childs'], $deep+1));
			}
		}
		return $data;
	}
	/**
	 * [group 权限角色分组]
	 * @return [type] [description]
	 */
	public function group(){
		if(!D("account")->verifyPermission(CONTROLLER_NAME,ACTION_NAME)){
			$this->display("Common/warning");
			exit();
		}
		$id = I("id",0,'intval');
		if($id){
			$this->editRole($id);
		}else{
			$list = D("common_role")->select();
			$body = array(
				'list'=>$list,
			);
			$this->assign($body);
	        $this->display();
		}
		
	}
	public function doAddGroup(){
		$id = I("modid",0,'intval');
		$rolename = I("rolename");
		$status = I("status",0,'intval');
		$add = array(
			"rolename"=>$rolename,
			"status"=>$status,
			"addtime"=>date("Y-m-d H:i:s"),
		);
		$rs = array("msg"=>"fail");
		if(empty($add['rolename'])) $this->ajaxReturn($rs);
		if($id){
			if(D("common_role")->where("id=".$id)->save($add)){
				$rs['msg'] = 'succ';
			}
			$this->ajaxReturn($rs);
		}else{
			if(D("common_role")->data($add)->add()){
				$rs['msg'] = 'succ';
			}
		}
		
		$this->ajaxReturn($rs);
	}
	/**
	 * [getGroup 获取角色信息]
	 * @return [type] [description]
	 */
	public function getGroup(){
		$id = I("id",0,'intval');
		$rs = array("msg"=>'fail');
		if(empty($id)){
			$this->ajaxReturn($rs);
		}
		$role = D("common_role")->where("id=".$id)->find();
		$rs['msg'] = 'succ';
		$rs['info'] = $role;
		$this->ajaxReturn($rs);
	}
	/**
	 * [getNodes 生成菜单节点树]
	 * @return [type] [description]
	 */
	public function getNodes(){
		$id = I("id",0,'intval');
		$result = D("common_admin_nav")->where("status=1")->field("id,name,pid as parentid,url,status,addtime,disorder,path")->order("pid asc,disorder asc")->select();
		$id && $role = D("common_role")->where("id=".$id)->find();
		
		$list = array();
		if($result){
			foreach($result as $k=>$v){
				$list[$v['id']]  = $v ;
			}
		}
		$list = $this->genTree($list,'id','parentid','childs',$role['power'] ? unserialize($role['power']):array());
		//pr($list);
		$rs = array('msg'=>'succ','list'=>$list);
		$this->ajaxReturn($rs);
	}
	/**
	 * [editRole 修改角色,生成菜单节点树]
	 * @return [type] [description]
	 */
	private function editRole($id){
		$body = array(
			'roleid'=>$id,
		);
		$this->assign($body);
        $this->display("Auth/editrole");
	}
	/**
	 * [doEditNodes 修改角色权限]
	 * @return [type] [description]
	 */
	public function doEditRole(){
		$nodes = I("nodes");
		$id = I("id",0,'intval');
		$power = array();
		$rs = array('msg'=>'fail');
		if(!$id) $this->ajaxReturn($rs);
		if($nodes){
			$newnodes = implode(',', $nodes);
			if($newnodes){
				//重新格式化
				$power = explode(',', $newnodes);
			}
		}
		if(D("common_role")->where("id=".$id)->save(array("power"=>serialize($power)))){
			$rs['msg'] = 'succ';
		}
		$this->ajaxReturn($rs);
	}
	/**
	 * admins 管理员列表
	 */
	public function admins(){
		if(!D("account")->verifyPermission(CONTROLLER_NAME,ACTION_NAME)){
			$this->display("Common/warning");
			exit();
		}
		$page = I("p",'int');
		$pagesize = 20;
		if($page<=0) $page = 1;
		$offset = ( $page-1 ) * $pagesize;
		$orderby = "create_time desc";
		$result = D("common_system_user")->limit("{$offset},{$pagesize}")->select();
		$count = D("common_system_user")->count();
		$Page       = new \Think\Page($count,$pagesize);
		$Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
		$pagination       = $Page->show();// 分页显示输出
		//角色组
		$roles = D("common_role")->where("status=1")->field("id,rolename")->select();
		if($result && $roles){
			$rls = assColumn($roles);
			foreach ($result as &$value) {
				$value['rolename'] = $value['gid'] ? $rls[$value['gid']]['rolename']:"";
			}
		}
		$body = array(
			'lists'=>$result,
			'pagination'=>$pagination,
			'roles'=>$roles,
		);
		$this->assign($body);
		$this->display();
	}
	/**
	 * [doAddAdmin 添加系统管理员]
	 * @return [type] [description]
	 */
	public function doAddAdmin(){
		$username = I("username");
		$password = I("password");
		$name = I("name");
		$department = I("department");
		$gid = I("gid",0,'intval');
		$super_admin = I("super_admin",0,'intval');
		$status = I("status",0,'intval');
		$id = I("modid",0,'intval');
		$rs = array("msg"=>'fail');
		if(empty($username)){
			$rs['msg'] = '信息填写不完整!';
			$this->ajaxReturn($rs);
		}
		$data = array(
			'username'=>$username,
			'gid'=>$gid,
			'name'=>$name,
			'department'=>$department,
			'status'=>$status,
			'super_admin'=>$super_admin,
		);
		if($data['super_admin']==1){
			$data['gid'] = 0;//超管无角色组
		}
		if($id){
			//修改登陆信息
			$admin = D("common_system_user")->where("id=".$id)->find();
			if(empty($admin)){
				$rs['msg'] = "error";
				$this->ajaxReturn($rs);
			}
			if($password){
				$data['passwd'] = SHA256Hex($password);
			}
			if(D("common_system_user")->where("id=".$id)->save($data)){
				$rs['msg'] = 'succ';
			}
		}else{
			$data['addtime'] = date("Y-m-d H:i:s");
			if(empty($password)){
				$rs['msg'] = '信息填写不完整!';
				$this->ajaxReturn($rs);
			}
			$data['passwd'] = SHA256Hex($password);
			if(D("common_system_user")->data($data)->add()){
				$rs['msg'] = 'succ';
			}
		}
		$this->ajaxReturn($rs);
	}
	/**
	 * 获取单个管理员信息
	 */
	public function getAdminUser(){
		$id = I("id",0,'intval');
		$rs = array("msg"=>'fail');
		$admin = D("common_system_user")->where("id=".$id)->find();
		if(empty($admin)){
			$rs['msg'] = "error";
			$this->ajaxReturn($rs);
		}
		$rs['msg'] = 'succ';
		$rs['info'] = $admin;
		$this->ajaxReturn($rs);
	}
	/**
	 * [menu 获取]
	 * @return [type] [description]
	 */
	public function menu(){
		$adminAuth = session("admin_auth");
		if($adminAuth){
			$result = D("common_admin_nav")->where("status=1")->field("id,name,pid as parentid,url,status,addtime,disorder,path,icon")->order("pid asc,disorder asc")->select();
			$list = array();
			if($result){
				foreach($result as $k=>$v){
					if(strpos($v['url'],'/')>-1){
						$v['url'] = str_replace( strrchr($v['url'].'/') , '' , $v['url']);
						$urlaction = explode('/', $v['url']);
						$v['menu_active'] = strtolower($urlaction[count($urlaction)-2]);
						$v['menu_secoud_active'] = strtolower($urlaction[count($urlaction)-1]);
					}
					$list[$v['id']]  = $v ;
				}
			}
			$menus = array();
			if($adminAuth['super_admin']){
				$menus = $this->genTree($list,'id','parentid','childs');
			}else{
				$role = D("common_role")->where("id=".$adminAuth['gid'])->find();
				//$power && $power = unserialize($power['power']);
				if($role){
					$menus = $this->genTree($list,'id','parentid','childs',$role['power'] ? unserialize($role['power']):array(),false);				
				}
			}
			$rs = array('msg'=>'succ','list'=>$menus);
			$this->ajaxReturn($rs);
		}
		
	}
}
//file end