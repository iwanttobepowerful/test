<?php
namespace Admin\Controller;
use Think\Controller;
class AlbumController extends Controller {
	public $user = null;
	public function _initialize(){ 
		load('@.functions');
		D("account")->checkLogin();
	}
	
	/**
	 * [upload_img 图片上传]
	 * @return [type] [description]
	 */
	public function upload(){
		$group_id = $_REQUEST['group_id'];
	    $upload = new \Think\Upload();
	    $upload->maxSize   =     3145728 ;
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
	    $upload->rootPath  =     './Public/attached/image/'; // 设置附件上传根目录
	    $upload->savePath  =     '';
	    $upload->saveName = 'time'; 
	    $info   =   $upload->upload();
		if(!$info) {// 上传错误提示错误信息
			$this->error($upload->getError());
			
		}else{// 上传成功 获取上传文件信息
			$saveUrl = $upload->rootPath.$info['file']['savepath'].$info['file']['savename'];
			$imgUrl = getCurrentHost().'/'.$this->cropImage($saveUrl,350,210);
			$data = array(
				'image'=>$saveUrl,
				'thumb'=>$imgUrl,
				'group_id'=>$group_id ? $group_id:1,
			);
			if($id = $this->saveThumb($data)){
				$data['id'] = $id;
				$data['info'] = 'succ';
				
			}else{
				$data['info'] = 'fail';
			}
			echo json_encode($data);		
		}
	}
	/**
	 * [saveThumb 存储]
	 * @return [type] [description]
	 */
	private function saveThumb($data){
		$insert = array(
			'group_id'=>intval($data['group_id']),
			'image'=>$data['image'],
			'thumb'=>$data['thumb'],
		);
		if($mid = D("media_album")->data($insert)->add()){
			return $mid;
		}
		return false;
	}
	/**
	 * 图片裁剪
	 */
	private function cropImage($img,$width=0,$height=0){
		$image = new \Think\Image(); 
		$image->open($img);
		$base = pathinfo($img);
		$thumb = $base['dirname'] .'/'. $base['filename'].'_thumb.'.$base['extension'];
  		$image->thumb($width, $height,\Think\Image::IMAGE_THUMB_CENTER)->save($thumb);
  		return $thumb;
	}
	/**
	 * [getList 图片列表]
	 * @return [type] [description]
	 */
	public function getList(){
		$page = I("p",'int');
		$group_id = I("group_id",1,'int');
		$pagesize = 49;
		if($page<=0) $page = 1;
		$offset = ( $page-1 ) * $pagesize;
		$where = "";
		if($group_id) $where = " group_id=".$group_id;
		$list = D("media_album")->where($where)->field("id,thumb")->order("id desc")->limit("{$offset},{$pagesize}")->select();
		$countRs = D("media_album")->where($where)->field("count(1) as total")->select();
		$Page       = new \Think\PageAjax($countRs[0]['total'],$pagesize);
		$Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
		$pagination       = $Page->show();// 分页显示输出
		$body = array(
			'lists'=>(array)$list,
			'pagination'=>$pagination,
		);
		$this->ajaxReturn($body);
	}
	/**
	 * [delete 删除图片]
	 * @return [type] [description]
	 */
	public function delete(){
		$result = array("msg"=>'fail');
		$ids = I("ids");
		if($ids){
			$ids = array_unique($ids);
			if(D("media_album")->where("id in(".implode(',', $ids).")")->delete()){
				$result['msg']='succ';
			}
		}
		$this->ajaxReturn($result);
	}
	/**
	 * 
	 */
	public function group(){
		$groups = D("media_group")->field("id,name,0 as number")->order("id asc")->select();

		//图片分类总计
		$res = D("media_album")->field("group_id,count(1) as total")->group("group_id")->select();
		if($res) $res = assColumn($res,"group_id");
		if($groups && $res){
			foreach ($groups as $key => $value) {
				$res[$value['id']] ? $groups[$key]['number'] = $res[$value['id']]['total']:0;
			}
		}
		$result = array("msg"=>'succ',"list"=>(array)$groups);
		$this->ajaxReturn($result);
	}
	/**
	 * [addGroup 添加组]
	 */
	public function addGroup(){
		$result = array("msg"=>'fail');
		$name = I("name","");
		$insert = array(
			'name'=>$name,
		);
		if($name && D("media_group")->data($insert)->add()){
			$result['msg'] = 'succ';
		}
		$this->ajaxReturn($result);
	}
	/**
	 * [addGroup 删除组]
	 */
	public function deleteGroup(){
		$result = array("msg"=>'fail');
		$group_id = I("group_id",0,"int");

		if(D("media_group")->where("id=".$group_id)->delete()){
			$result['msg'] = 'succ';
		}
		$this->ajaxReturn($result);
	}
	public function renameGroup(){
		$result = array("msg"=>'fail');
		$group_id = I("group_id",0,"int");
		$name = I("name","");
		if($group_id && $name){
			if(D("media_group")->where("id=".$group_id)->data(array("name"=>$name))->save()){
				$result['msg'] = 'succ';
			}
		}
		$this->ajaxReturn($result);
	}
}
//file end