<?php
/** 
 * 截取处理UTF-8编码字符串 
 * 规则：中文2个字符，数字、英文1个字符；截取末尾不足一个汉字的则舍弃。 
 * @param string $str 
 * @param int    $len 截取出的字符长度 
 * @author flyer0126 
 * @since 2012/05/03 
 */  
function substr_utf8_cn($str, $len)  
{  
    $length  = strlen($str);  
    if ($length <=  $len)  
    {  
        return $str;  
    }  
  
    $result_str = '';  
    for($i=0;$i<$len;$i++)  
    {  
        $temp_str=substr($str,0,1);  
        if(ord($temp_str) > 127)  
        {  
            if($i+1<$len)  
            {  
                $result_str .= substr($str,0,3);  
                $str = substr($str,3);  
            }
            $i++;
        }
        else  
        {  
            $result_str .= substr($str,0,1);  
            $str=substr($str,1);
        }  
    }  
  
    return $result_str;  
}  

/**
* 多图上传方法
* @param Array $_FILES 上传的图片信息
* @return Array
*/
function upLoads(){
    $upload = new \Think\Upload();// 实例化上传类
    // 开启子目录保存 并以日期（格式为Ymd）为子目录
    $upload->autoSub = true;
    $upload->subName = date("Ymd");
    $upload->maxSize = 3145728 ;// 设置附件上传大小
    $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    $upload->rootPath = $_SERVER['DOCUMENT_ROOT'].'/Public/attached/image/'; // 设置附件上传根目录
    $upload->saveName = date("YmdHis").'_'.rand(111111,999999);
    // 上传文件 
    $info = $upload->upload();
    if(!$info) {// 上传错误提示错误信息
        $da = false;
    }else{// 上传成功 获取上传文件信息
        foreach($info as $file){
            $da[] = '/Public/attached/image/'.$file['savepath'].$file['savename'];
        }
    }
    return $da;
}

//单图上传方法
function upLoadOne($files){
	$upload = new \Think\Upload();// 实例化上传类
    // 开启子目录保存 并以日期（格式为Ymd）为子目录
	$upload->autoSub = true;
	$upload->subName = date("Ymd");
	$upload->maxSize = 3145728 ;// 设置附件上传大小
	$upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	$upload->rootPath = './Public/attached/image/'; // 设置附件上传根目录
	$upload->saveName = date("YmdHis").'_'.rand(111111,999999);
    // 上传单个文件 
    $info   =   $upload->uploadOne($_FILES[$files]);
    if(!$info) {// 上传错误提示错误信息
        $da['msg'] = $upload->getError();
        $da['status'] = 'error';
    }else{// 上传成功 获取上传文件信息
        $da['img_url'] = '/Public/attached/image/'.$info['savepath'].$info['savename'];
        $da['status'] = 'ok';
    }
    return $da;
}

/**
 * 图片压缩处理
 * @Author   slz@yujia.com
 * @DateTime 2017-06-29T13:39:32+0800
 * @param    string                   $img_url [图片地址]
 * @param    array                   $size  [压缩配置]
 * @return   string                            [返回压缩后的图片地址]
 */
function thumb_image($img_url,$size){
    $image = new \Think\Image(); 
    $sub = substr($img_url,0,-4).$size[0].'*'.$size[1].'.jpg';
    $image->open('.'.$img_url)->thumb($size[0],$size[1])->save('.'.$sub);          
    return $sub;
}

/**
 * 删除原图
 * @Author   slz@yujia.com
 * @DateTime 2017-06-29T17:45:28+0800
 * @param    [type]                   $img_url [description]
 * @return   [type]                            [description]
 */
function del_image($img_url){
    $image = new \Think\Image();
    $sub = substr($img_url,0,-4).'w.jpg';
    $image->open('.'.$img_url)->save('.'.$sub); 
    if($sub){
        unlink('.'.$img_url);
    }
    return $sub;
}

//数组元素删除函数
function remove_array($ys,$arr){
	$k = array_search($ys,$arr);
	if ($k !== false){
		array_splice($arr,$k,1);
	}
	return $arr;
}

//添加sku数组详情
function add_sku_item($datas,$goods_id){
    $goods_sku = M('goods_sku');
    foreach (json_decode($datas,true)  as $key => $value) {
        $reqs_skuall[$key]['goods_id'] = $goods_id;
        $reqs_skuall[$key]['skus_id'] = $value['path'];
        $reqs_skuall[$key]['price'] = $value['price'];
        $reqs_skuall[$key]['repertory'] = $value['num'];
    }
    $ret = $goods_sku->addAll($reqs_skuall);
    return $ret ? true : false;
}

//添加系统消息
function send_msg($receiver_id,$type,$content,$tips){
    $user_msg = M('user_msg');
    if ($receiver_id && $type && $content) {
        $data['receiver_id'] = $receiver_id;
        $data['type'] = $type;
        $data['content'] = $content;
        $data['tips'] = $tips;
        $data['creat_time'] = date('Y-m-d H:i:s');
        if ($user_msg->add($data)) {
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}
