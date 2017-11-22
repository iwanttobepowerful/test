<?php
/**
 * Created by PhpStorm.
 * User: Ail
 * Date: 2017/10/14
 * Time: 16:18
 */
namespace Admin\Controller;
use Think\Controller;
include "__PUBLIC__/static/phpqrcode/phpqrcode.php";
class TestReportController extends Controller
{
    public $user = null;

    public function _initialize()
    {
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active', strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active', strtolower(ACTION_NAME));
    }

    //检验报告的生成
	public function generateReport(){
        $keyword = I("keyword");//获取参数
        $where= "contract_flow.centreno like '%{$keyword}%' and contract_flow.status=8";
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        if ($user==10 || $if_admin ==1) {//只有报告编制员，超级管理员才能操作
            $view="";
        }
        else {
            $view="hidden";
        }


        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $result=M('contract_flow')->where($where)
            ->join('contract ON contract_flow.centreNo = contract.centreNo')
            ->order('contract_flow.takelist_time desc,contract.id desc')
            ->limit("{$offset},{$pagesize}")->select();



        $count = D("contract_flow")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body=array(
            'lists'=>$result,
            'pagination'=>$pagination,
            'userid'=>$userid,
            'view'=>$view,
        );
        $this->assign($body);
        $this->display();
   }
    
    //选择模板
   	public function seleteTemp(){
        $mod = I("mod");
        $conNo=I("conNo");
        $where= "centreno='{$conNo}'";
        $data=D("contract")->where($where)->find();
        $data1=D("sampling_form")->where($where)->find();
        $tpl = D("tpl")->where("id=".$mod)->find();

        //$data['tplno'] = $mod;
        //$test_report->where($where)->save($data); // 根据条件保存修改的数据

        $test_content=M("contract");
        $sample_content=M("sampling_form");

        $status = M("contract_flow")->where($where)->field('status')->find();

        if ($status['status']==8) {
            $shengchengview="";
            $dayinview="hidden";
            $zhidu="";

        }
        else
        {
            $shengchengview="hidden";
            $dayinview="";
            $zhidu="readonly";

        }
        $final_content=$test_content->where($where)->find();
        $final_content_two=$sample_content->where($where)->find();
        $htmltable=M("test_report")->where($where)->field('htmltable')->select();

        $body = array(
            'one'=>$data,
            'con'=>$data1,
            'mod'=>$mod,
            'zhidu'=>$zhidu,
            'con_list'=>$final_content,
            'sam_list'=>$final_content_two,
            'htmltable'=>($htmltable[0]['htmltable']),
            'shengchengview'=>$shengchengview,
            'dayinview'=>$dayinview,
            'status'=>$status['status'],
            'qrimg'=>$this->qrcode($final_content['centreno'],getCurrentHost().'/admin/SeeReport/show?centreno='.$final_content['centreno']),
        );

        $this->assign($body);
        $tplfile = $tpl['filename'];
        $this->display($tplfile);
	}
	//生成报告word下载模板选择
	public function selectTemp(){
   	    $modid=I("id");
   	    $centreNo=I("contractno");
        $rs = array("msg"=>"","status"=>"fail");
   	    if(!empty($modid) and !empty($centreNo)){
            $tpl = D("tpl")->where("id=".$modid)->find();
            $contract = D("contract")->where("centreno='{$centreNo}'")->find();
			$reportNum = D("contract")->where("centreno like '%{$centreNo}%'")->count();
			if($reportNum == 1){
				$newCentreNo = $centreNo.'G1';
			}
			if($reportNum == 2){
				$newCentreNo = $centreNo.'G2';
			}
			if($reportNum == 3){
				$newCentreNo = $centreNo.'G3';
			}
            //dump($contract);
			
            $data = array(
                'centreNo'=>$newCentreNo,
                'sampleName'=>$contract['samplename'],
                'clientName'=>$contract['clientname'],
                'productionDate'=>$contract['productiondate'] ? $contract['productiondate']:"————",
                'productUnit'=>$contract['productunit'] ? $contract['productunit']:"————",
                'trademark'=>$contract['trademark'] ? $contract['trademark']:"————",
                'grade'=>$contract['grade'] ? $contract['grade']:"————",
                'specification'=>$contract['specification'] ? $contract['specification']:"————",
                'sampleStatus'=>$contract['samplestatus'] ? $contract['samplestatus']:"————",
                'testCriteria'=>$contract['testcriteria'],
                'testItem'=>$contract['testitem'],
                'collectDate'=>$contract['collectdate'] ? $contract['collectdate']:"————",
                'sampleCode'=>$contract['samplecode'] ? $contract['samplecode']:"————",
                'sampleQuantity'=>$contract['samplequantity'] ? $contract['samplequantity']:"————",
            );

           	$samplingForm = D("sampling_form")->where("centreno='{$centreNo}'")->find();
			if($samplingForm){
            	$data['samplePlace'] = $samplingForm['sampleplace'] ? $samplingForm['sampleplace'] : "————";
                $data['simplerSign'] = $samplingForm['simplersign'];
                $data['sampleDate'] = $samplingForm['sampledate'] ? $samplingForm['sampledate']:"————";
                $data['sampleQuantity'] = $samplingForm['samplequantity'] ? $samplingForm['samplequantity']:"————";
                $data['sampleBase'] = $samplingForm['samplebase'] ? $samplingForm['samplebase']:"————";
         	}
            
            $src = "./Public/{$tpl['filename']}";
            $dst = "./Public/attached/report/{$centreNo}.docx";
            if(file_exists($dst)){
                unlink($dst);
            }
            $qrcode = $this->qrcode($centreNo,getCurrentHost().'/admin/report/pdf?no='.$centreNo);
            convert2Word($data,$src,$dst,$qrcode);


            //setQrcode($dst,$qrcode,'./Public/qrcode/'.time().'.docx');die;
            $testReport = D("test_report")->where("centreno='{$centreNo}'")->find();

            $update = array(
                'tplno'=>$modid,
                'doc_path'=>$dst ? substr($dst,1):"",
                'qrcode_path'=>$qrcode ? substr($qrcode,1):"",
                'modify_time'=>date("Y-m-d H:i:s"),
            );
            pr($update);
            if($testReport){
                if(D("test_report")->where("centreno='{$centreNo}'")->save($update)){
                    $rs['msg']='succ';
                }
            }else{
                $update['centreNo']=$centreNo;
                if(D("test_report")->data($update)->add()){
                    $rs['msg'] = 'succ';

                }
            }

        }

        $this->ajaxReturn($rs);
    }
    //修改status
	public function doneCreate(){
        $conclusion= I("a_result");//填写的结论
        $mod=I("mod");
        $centreno=I("centreno");
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $where= "centreNo='{$centreno}'";
        $data=array(
            'status'=>1,
            'report_time'=>date("Y-m-d H:i:s"),
            'report_user_id'=>$userid,
        );
        $data1=array(
            'centreNo'=>$centreno,
            'tplno'=>$mod,
        );
        $data2['conclusion']=$conclusion;
        $exist=D("test_report")->where($where)->find();
        if($exist){
            $result=D("test_report")->where($where)->save($data1);
            if((D("contract_flow")->where($where)->save($data)) && ($result!== false)&&(D("contract")->where($where)->save($data2))){
                $rs['msg'] = 'succ';
            }
        }else{
            if((D("contract_flow")->where($where)->save($data)) && (D("test_report")->data($data1)->add())&&(D("contract")->where($where)->save($data2))){
                $rs['msg'] = 'succ';
            }
        }
        $this->ajaxReturn($rs);
    }
//生成二维码
    public function qrcode($centreno,$qr_data){
        $save_path = './Public/attached/qrcode/';  //图片存储的绝对路径
        $qr_level = 'L';
        $qr_size = '4';
        $save_prefix = '';
        if(file_exists($save_path.md5($centreno).'.png')){
            @unlink($save_path.md5($centreno).'.png');
        }
        if($filename = createQRcode($centreno,$save_path,$qr_data,$qr_level,$qr_size,$save_prefix)){
            $img = $save_path.$filename;
        }
        return $img;
        //return substr($img,1);
    }

    //选择编号
    public function seleteKey(){
	   $centreno = I("mod");
        $tpl=D("tpl")->select();
        $contract = D('contract')->where("centreno='{$centreno}'")->find();
        if($contract['testcategory']=='抽样检验'){
            $type = 2;
        }elseif($contract['testcategory']=='委托检验'){
            $type = 1;
        }elseif($contract['testcategory']=='型式检验'){
            $type = 2;
        }
        $body = array(
           'contactNo'=>$centreno,
            'tpl'=>$tpl,
            'type'=>$type,
       );
       $this->assign($body);
       $this->display(select);
	}
    
    
    public function reviseReport(){
        $this->display();
    }

}
?>