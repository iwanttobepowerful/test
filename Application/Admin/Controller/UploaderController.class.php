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
     * [upload_word word上传]
     * @return [type] [description]
     */
    public function word($return=false){
        $upload = new \Think\Upload();
        $upload->maxSize   =    0;//不限制上传大小
        $upload->exts      =     array('doc','docx','docm','dotm','txt','dot','pdf');
        $upload->rootPath  =     './Public/attached/'; // 设置附件上传根目录
        $upload->savePath  =     '';
        $upload->saveName = 'time';
        $info   =   $upload->upload();

        $array = array("info"=>"fail");
        if(!$info) {// 上传错误提示错误信息
            $array['info'] = $upload->getError();
        }else{// 上传成功 获取上传文件信息
            $saveUrl = './Public/attached/'.$info['file']['savepath'].$info['file']['savename'];
             $base = pathinfo($saveUrl);
            $thumb = $base['dirname'] .'/'. $base['filename'].'.'.$base['extension'];
            $array = array(
                'info'=>'succ',
                'url'=>substr($thumb, 1),
            );
            echo json_encode($array);
            return true;
        }
        echo json_encode($array);

    }
    //临时申请修改上传报告
    public function word1($return=false){
        $upload = new \Think\Upload();
        $upload->maxSize   =    0;//不限制上传大小
        $upload->exts      =     array('doc','docx','docm','dotm','txt','dot','pdf');
        $upload->rootPath  =     './Public/attached/temp/'; // 设置附件上传根目录
        $upload->savePath  =     '';
        $upload->saveName = 'time';
        $info   =   $upload->upload();
        $array = array("info"=>"fail");
        if(!$info) {// 上传错误提示错误信息
            $array['info'] = $upload->getError();
        }else{// 上传成功 获取上传文件信息
            $saveUrl = './Public/attached/temp/'.$info['file']['savepath'].$info['file']['savename'];
            $base = pathinfo($saveUrl);
            $thumb = $base['dirname'] .'/'. $base['filename'].'.'.$base['extension'];
            $array = array(
                'info'=>'succ',
                'url'=>substr($thumb, 1),
            );
            echo json_encode($array);
            return true;
        }
        echo json_encode($array);

    }
    /**
     * [upload_img 图片上传]
     * @return [type] [description]
     */
    public function start($return=false){
        $upload = new \Think\Upload();
        $upload->maxSize   =     0 ;
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath  =     './Public/attached/'; // 设置附件上传根目录
        $upload->savePath  =     '';
        $upload->saveName = 'time'; 
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $array['info'] = $upload->getError();
        }else{// 上传成功 获取上传文件信息
            $saveUrl = './Public/attached/'.$info['file']['savepath'].$info['file']['savename'];
            $picAddr = $saveUrl;
            $exif = exif_read_data($picAddr);
            $image = imagecreatefromjpeg($picAddr);
            if($exif['Orientation'] == 3) {
                $result = imagerotate($image, 180, 0);
                imagejpeg($result, $picAddr, 100);
            } elseif($exif['Orientation'] == 6) {
                $result = imagerotate($image, -90, 0);
                imagejpeg($result, $picAddr, 100);
            } elseif($exif['Orientation'] == 8) {
                $result = imagerotate($image, 90, 0);
                imagejpeg($result, $picAddr, 100);
            }
            isset($result) && imagedestroy($result);
            imagedestroy($image);
            $imgUrl = $this->cropImage($picAddr,100,100);
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

//多图上传
    public function webup(){
        $config = array(
            'mimes'         =>  array(), //允许上传的文件MiMe类型
            'maxSize'       =>  0, //上传的文件大小限制 (0-不做限制)
            'exts'          =>  array('jpg', 'gif', 'png', 'jpeg'), //允许上传的文件后缀
            'autoSub'       =>  true, //自动子目录保存文件
            'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath'      =>  './Public/attached/', //保存根路径
            'savePath'      =>  '',//保存路径
            'savename'      => 'time'
        );
        $upload = new \Think\Upload($config);// 实例化上传类


        $info   =   $upload->upload();

        if(!$info) {

            $this->error($upload->getError());// 上传错误提示错误信息

        }else {// 上传成功
            //dump($info);

            foreach ($info as $va) {
                $saveUrl = './Public/attached/'.$va['savepath'].$va['savename'];
                //防止图片旋转
                $picAddr = $saveUrl;
                $exif = exif_read_data($picAddr);
                $image = imagecreatefromjpeg($picAddr);
                if($exif['Orientation'] == 3) {
                    $result = imagerotate($image, 180, 0);
                    imagejpeg($result, $picAddr, 100);
                } elseif($exif['Orientation'] == 6) {
                    $result = imagerotate($image, -90, 0);
                    imagejpeg($result, $picAddr, 100);
                } elseif($exif['Orientation'] == 8) {
                    $result = imagerotate($image, 90, 0);
                    imagejpeg($result, $picAddr, 100);
                }
                isset($result) && imagedestroy($result);
                imagedestroy($image);
                $imgUrl = $this->cropImage($picAddr,100,100);
                $this->ajaxReturn($imgUrl);
            }

        }
    }
}