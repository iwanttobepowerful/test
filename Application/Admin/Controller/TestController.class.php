<?php

namespace Admin\Controller;
use Think\Controller;
class TestController extends Controller{
    public $user = null;

    public function _initialize(){//系统Action类提供了一个初始化方法_initialize接口，可以用于扩展需要，_initialize方法会在所有操作方法调用之前首先执行
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }

    //工作通知单查询
    public function infoList(){
        $keyword = I("keyword");//获取参数
        $where= "centreno='{$keyword}'";
        $result=M('work_inform_form')->where($where)->field("id,centreno,samplename")->select();
        $body=array(
            'lists'=>$result,

        );
        $this->assign($body);
        $this->display();
    }

    //工作通知单显示
    public function infoShow(){
        $keyword = I("id");//获取参数
        $where= "centreNo='{$keyword}'";

        $work_inform_form=M('work_inform_form');
        $result=$work_inform_form->where($where)->select();
        $body=array(
            'lists'=>$result,
        );
        $this->assign($body);
        $this->display();
    }



    //抽样单查询
    public function sampleList(){
        $keyword = I("keyword");//获取参数
        $where= "centreno='{$keyword}'";
        $result=M('sampling_form')->where($where)->field("id,centreno,clientname,productunit")->select();

        $body=array(
            'lists'=>$result,

        );
        $this->assign($body);
        $this->display();
    }

    //抽样单显示
    public function sampleShow(){
        $keyword = I("id");//获取参数
        $where= "centreno='{$keyword}'";
        $result=M('sampling_form')->where($where)->select();

        $simsigndateyear = $result[0]['simsigndate'] ? date("Y",strtotime($result[0]['simsigndate'])):"";
        $simsigndatemonth =  $result[0]['simsigndate'] ? date("m",strtotime($result[0]['simsigndate'])):"";
        $simsigndateday =  $result[0]['simsigndate'] ? date("d",strtotime($result[0]['simsigndate'])):"";
        array_push($result[0],$simsigndateyear);
        array_push($result[0],$simsigndatemonth);
        array_push($result[0],$simsigndateday);

        $seasingdateyear =  $result[0]['seasingdate'] ? date("Y",strtotime($result[0]['seasingdate'])):"";
        $seasingdatemonth =  $result[0]['seasingdate'] ? date("m",strtotime($result[0]['seasingdate'])):"";
        $seasingdateday =  $result[0]['seasingdate'] ? date("d",strtotime($result[0]['seasingdate'])):"";
        array_push($result[0],$seasingdateyear);
        array_push($result[0],$seasingdatemonth);
        array_push($result[0],$seasingdateday);

        $entsigndateyear =  $result[0]['entsigndate'] ? date("Y",strtotime($result[0]['entsigndate'])):"";
        $entsigndatemonth =  $result[0]['entsigndate'] ? date("m",strtotime($result[0]['entsigndate'])):"";
        $entsigndateday =  $result[0]['entsigndate'] ? date("d",strtotime($result[0]['entsigndate'])):"";
        array_push($result[0],$entsigndateyear);
        array_push($result[0],$entsigndatemonth);
        array_push($result[0],$entsigndateday);

        $body=array(
            'lists'=>$result,

        );
        $this->assign($body);
        $this->display();
    }


    //检测记录上传
    public function recordUpload(){
        $this->display();
    }

    public function doUpd(){
        $keyword = I("keyword");//获取参数
        $recordname = I("recordname");//获取参数
        $str =I("str");
        $data = array("centreNo"=>$keyword,"recordName"=>$recordname,"remark"=>$str);
        if(D("test_record")->add($data)){
            $rs = array("msg"=>"succ");
        }
        $this->ajaxReturn($rs);
    }


    public function recordUp(){

        $this->display();
    }


    public function recordPicture(){
        $keyword = I("keyword");//获取参数
        $where= "centreno='{$keyword}'";
        $result=M('test_record')->where($where)->field("centreno")->find();
        $body=array(
            'lists'=>$result,

        );
        $this->assign($body);
        $this->display();
    }

    public function recordPictureUp(){
        $page = I("p",'int');
        $pagesize = 20;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $orderby = "create_time desc";

        $keyword = I("id");//获取参数
        $where= "centreno='{$keyword}'";
        $result=D('test_record')->limit("{$offset},{$pagesize}")->where($where)->select();

        $count = D("test_record")->count();
        $Page       = new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination       = $Page->show();// 分页显示输出

        $body=array(
            'lists'=>$result,
            'pagination'=>$pagination,
        );
        $this->assign($body);
        $this->display();
    }

    public function picUp(){
        $id =I("id",0,'intval');
        if($id){
            $pic = D('test_record')->where("id=".$id)->find();
        }
       // dump($pic);
        $body = array(
            'pic' => $pic,
        );
        $this->assign($body);
        $this->display();
    }
    public function doUploadPic(){
        $id = I("id",0,'intval');
        $imgurl = I("imgurl");
        $remark = I("remark");
        $result = array("msg"=>"fail");
        if(empty($imgurl)){
            $result['msg'] = "无效的提交！";
            $this->ajaxReturn($result);
        }
        $data = array("path"=>$imgurl,"remark"=>$remark);
        $pic = D("test_record")->where("id=".$id)->find();
        if($pic){
            if(D("test_record")->where("id=".$pic['id'])->save($data)){
                $result['msg'] = 'succ';
            }
        }else{
            if(D("test_record")->data($data)->add()){
                $result['msg'] = 'succ';
            }
        }
        $this->ajaxReturn($result);
    }

    public function doDeletePic(){
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        if(D("test_record")->where("id=".$id)->delete()){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }
}