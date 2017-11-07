<?php
namespace Admin\Controller;
use Think\Controller;
class SeeReportController extends Controller
{
    public $user = null;

    public function _initialize()
    {
        load('@.functions');
    }

    public function show(){
        $centreno = I("centreno");//获取参数

        $where= "centreno='{$centreno}'";
        $tplno = M("test_report")->where($where)->field('tplno')->select();
        $test_content=M("contract");
        $sample_content=M("sampling_form");

        $final_content=$test_content->where($where)->find();
        $final_content_two=$sample_content->where($where)->find();

        $body=array(
            'con_list'=>$final_content,
            'sam_list'=>$final_content_two,

        );
        $this->assign($body);
        $this->display();
//        switch($tplno){
//            case 1:
//                $this->display(testReportA);
//                break;
//            case 2:
//                $this->display(testReportB);
//                break;
//            case 3:
//                $this->display(testReportC);
//                break;
//            case 4:
//                $this->display(testReportD);
//                break;
//            case 5:
//                $this->display(testReportE);
//                break;
//            case 6:
//                $this->display(testReportF);
//                break;
//            case 7:
//                $this->display(testReportG);
//                break;
//        }

    }

}