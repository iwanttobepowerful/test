<?php
namespace Admin\Controller;
use Think\Controller;
class UploaderController extends Controller {
    //初始化方法
    public function _initialize(){
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }
    /**
     * [upload_img 图片上传]
     * @return [type] [description]
     */
    public function start($return=false){
        $upload = new \Think\Upload();
        $upload->maxSize   =     3145728 ;
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath  =     './Public/attached/'; // 设置附件上传根目录
        $upload->savePath  =     '';
        $upload->saveName = 'time'; 
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            if($return){
                return $upload->getError();
            }else{
                $this->error($upload->getError());
            }
        }else{// 上传成功 获取上传文件信息
            $saveUrl = './Public/attached/'.$info['file']['savepath'].$info['file']['savename'];
            $imgUrl = $this->cropImage($saveUrl,100,100);
            $array = array(
                'info'=>'succ',
                'url'=>substr($imgUrl, 1),
            );
            if($return){
                return $array;
            }else{
                echo json_encode($array);
            }
        }
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
}