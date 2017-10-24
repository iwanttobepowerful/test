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
}