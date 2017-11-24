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
    public function pdf(){
        $centreno = I('no');
        if($centreno){
            $report = D('test_report')->where("centreno='{$centreno}'")->find();
            $pdf_path=$report['pdf_path'];
            if(strpos($pdf_path,'http')===false){

                $pdf_path = getCurrentHost().$pdf_path;
            }
            $data=D("contract_flow")->where("centreno='{$centreno}'")->find();//查中心编号对应的状态
            $status=$data['status'];
            if($status==6){
                //计数
                $count = $report['find_count'] +1;
                D("test_report")->where("id=".$report['id'])->save(array("find_count"=>$count));
                //@header("Location:{$pdf_path}");
                
                $body = array(
                    'pdfUrl'=>urlencode($pdf_path),
                );
                
            }
            $this->assign($body);
        }
        $this->display();
    }
}