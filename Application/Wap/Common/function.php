<?php
/** 
 * 截取处理UTF-8编码字符串 
 * 规则：中文2个字符，数字、英文1个字符；截取末尾不足一个汉字的则舍弃。 
 * @param string $str 
 * @param int    $len 截取出的字符长度 
 * @author flyer0126 
 * @since 2012/05/03 
 */  
if( ! function_exists("substr_utf8_cn")){
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
}
/**
* 多图上传方法
* @param Array $_FILES 上传的图片信息
* @return Array
*/
if( ! function_exists("upLoads")){
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
}
//单图上传方法
if( ! function_exists("upLoadOne")){
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
}
/**
 * 图片压缩处理
 * @Author   slz@yujia.com
 * @DateTime 2017-06-29T13:39:32+0800
 * @param    string                   $img_url [图片地址]
 * @param    array                   $size  [压缩配置]
 * @return   string                            [返回压缩后的图片地址]
 */
if( ! function_exists("thumb_image")){
    function thumb_image($img_url,$size){
        $image = new \Think\Image(); 
        $sub = substr($img_url,0,-4).$size[0].'*'.$size[1].'.jpg';
        $image->open('.'.$img_url)->thumb($size[0],$size[1])->save('.'.$sub);          
        return $sub;
    }
}
/**
 * 删除原图
 * @Author   slz@yujia.com
 * @DateTime 2017-06-29T17:45:28+0800
 * @param    [type]                   $img_url [description]
 * @return   [type]                            [description]
 */
if( ! function_exists("del_image")){

    function del_image($img_url){
        $image = new \Think\Image();
        $sub = substr($img_url,0,-4).'w.jpg';
        $image->open('.'.$img_url)->save('.'.$sub); 
        if($sub){
            unlink('.'.$img_url);
        }
        return $sub;
    }
}
//数组元素删除函数
if( ! function_exists("remove_array")){
    function remove_array($ys,$arr){
    	$k = array_search($ys,$arr);
    	if ($k !== false){
    		array_splice($arr,$k,1);
    	}
    	return $arr;
    }
}
/**
 * 检查当前用户是否登录
 */
if( !function_exists("checkLogin") ){
    function checkLogin(){
        if(session("accessUser")){
            return true;
        }
        return false;
    }
}
/**
 * 获取当前访问的域名
 */
if(!function_exists("getCurrentHost")){
    function getCurrentHost(){
        $httpProtocol = "";
        if($_SERVER["REQUEST_SCHEME"]){
            $httpProtocol = $_SERVER["REQUEST_SCHEME"];
        }else{
            if(stripos($_SERVER['SERVER_PROTOCOL'], 'http')>-1){
                $httpProtocol = "http";
            }elseif(stripos($_SERVER['SERVER_PROTOCOL'], 'https')>-1){
                $httpProtocol = "http";
            }
        }
        $host = $httpProtocol.'://'.$_SERVER["SERVER_NAME"];
        return $host;
    }
}

/**
 * 获取当前访问的url
 */
if(!function_exists("getCurrentUrl")){
    function getCurrentUrl(){
        $httpProtocol = "";
        if($_SERVER["REQUEST_SCHEME"]){
            $httpProtocol = $_SERVER["REQUEST_SCHEME"];
        }else{
            if(stripos($_SERVER['SERVER_PROTOCOL'], 'http')>-1){
                $httpProtocol = "http";
            }elseif(stripos($_SERVER['SERVER_PROTOCOL'], 'https')>-1){
                $httpProtocol = "http";
            }
        }
        $url = $httpProtocol.'://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        return $url;
    }
}
/** 
 * 打印
 */
if(!function_exists("pr")){
    function pr($string){
        @header("Content-type:text/html;charset=utf-8");
        echo '<pre>';
        print_r($string);
        echo '</pre>';
    }
}
/**
 * 获取用户的邀请码
 */
if(!function_exists("encInviteCode")){
    function encInviteCode($user){
        @import("Home.Lib.Crypt");
        $crypt = new \Crypt();
        $inviteCode = $crypt->enc(C("enckey").$user['id']);
        return $inviteCode;
    }
}
/**
 * 邀请码解密
 */
if(!function_exists("decInviteCode")){
    function decInviteCode($code){
        @import("Home.Lib.Crypt");
        $crypt = new \Crypt();
        $inviteCode = $crypt->dec($code);
        $inviteUid = str_replace(C("enckey"),'',$inviteCode);
        return $inviteUid;
    }
}
if(!function_exists("getColumn")){
    function getColumn($a=array(), $column='id', $null=true, $column2=null){
        $ret = array();
        @list($column, $anc) = preg_split('/[\s\-]/',$column,2,PREG_SPLIT_NO_EMPTY);
        foreach( $a AS $one )
        {
            if ( $null || @$one[ $column ] )
                $one[ $column ] && $ret[] = @$one[ $column ].($anc?'-'.@$one[$anc]:'');
        }
        return $ret;
    }
}
if(!function_exists("assColumn")){
    function assColumn($a=array(), $column='id'){
        $two_level = func_num_args() > 2 ? true : false;
        if ( $two_level ) $scolumn = func_get_arg(2);

        $ret = array(); settype($a, 'array');
        if ( false == $two_level )
        {
            foreach( $a AS $one )
            {
                if ( is_array($one) )
                    $ret[ @$one[$column] ] = $one;
                else
                    $ret[ @$one->$column ] = $one;
            }
        }
        else
        {
            foreach( $a AS $one )
            {
                if (is_array($one)) {
                    if ( false==isset( $ret[ @$one[$column] ] ) ) {
                        $ret[ @$one[$column] ] = array();
                    }
                    $ret[ @$one[$column] ][ @$one[$scolumn] ] = $one;
                } else {
                    if ( false==isset( $ret[ @$one->$column ] ) )
                        $ret[ @$one->$column ] = array();

                    $ret[ @$one->$column ][ @$one->$scolumn ] = $one;
                }
            }
        }
        return $ret;
    }
}
if(!function_exists("error")){
    function error($string){
        die($string);
    }
}
//获取客户端的IP地址
if( ! function_exists("get_client_ip")){
    function get_client_ip(){
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")){
            $ip = getenv("HTTP_CLIENT_IP");
        }else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")){
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        }else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "unknown";
        #log_message("LOG",var_export($_SERVER,true),'log');
        if($ip!='unknown'){
            $ipArr = explode(',',$ip);
            $ip = $ipArr[count($ipArr)-1];
        }
        return($ip);
    }
}
/**
     * $operation 加密ENCODE或解密DECODE
     * $key 密钥
     * $expiry 密钥有效期 ， 默认是一直有效
*/
if(!function_exists("auth_code")){
    function auth_code($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    /*
        动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
        取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
        当此值为 0 时，则不产生随机密钥
    */
    $ckey_length = 4;
    $key = md5($key != '' ? $key : "fdsfdf43535svxfsdfdsfs"); // 此处的key可以自己进行定义，写到配置文件也可以
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}
}
if(!function_exists("isEmail")){
    function isEmail($email){
        return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-]+(\.\w+)+$/",$email);
    }
}
if(!function_exists("isMobile")){
    function isMobile($str){
        return preg_match("/^1[3|5|7|8]\d{9}$/", $str);
    }
}
if(!function_exists("isName")){
    function isName($str){
        $pattern_url = "/^((?!@).)*$/is"; 
        $go = mb_strlen($str,'UTF8');
        //如果输入的在2-8个字符之间 就进行判断
        if($go<=12 && $go>=2){
            // 如果里面没有含有@就返回真
            if (preg_match($pattern_url, $str)){ 
                return true;
            }else{ 
                return false;
            }
        }else{
            return false;
        }
    }
}
if(!function_exists("isQq")){
    function isQq($qq){
        $pattern_url = "/^[1-9]\d{4,12}$/is"; 
        if (preg_match($pattern_url, $qq)){ 
            return true;
        }
        return false;
    }
}
if(!function_exists("humanTime")){

    function humanTime($time=null, $forceDate=false){
        $now = time();
        $time = is_numeric($time) ? $time : strtotime($time);
        $interval = $now - $time;
        if ( $forceDate || $interval > 3*86400 ){
            //return strftime("%Y-%m-%d 周%a %H:%M",$time);
            //return date("Y-m-d 周D H:M",$time);
            $w = array('Sun'=>'日','一','Tue'=>'二','Wed'=>'三','Thu'=>'四','Fri'=>'五','Sat'=>'六');
            ///return  date("Y-m-d ",$time).'周'.$w[ date('D',$time)].date(" H:i",$time);
            return  date("Y-m-d ",$time).date(" H:i",$time);
        }else if ( $interval > 86400 ){
            $number = intval($interval/86400);
            return "${number}天前";
        }else if ( $interval > 3600 ){ // > 1 hour
            $number = intval($interval/3600);
            return "${number}小时前";
        }else if ( $interval >= 60 ){ // > 1 min
            $number = intval($interval/60);
            return "${number}分钟前";
        }else if ( 5 >= $interval){// < 5 second
            return "就在刚才";
        }else{ // < 1 min
            return "${interval}秒前";
        }
    }
}
/**
     * 密码加密
     */
if(!function_exists("SHA256Hex")){
    function SHA256Hex($str){
        $re=hash('sha256', $str, true);
        return md5(bin2hex($re));
    }
}
