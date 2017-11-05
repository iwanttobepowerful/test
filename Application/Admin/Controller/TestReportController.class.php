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
    //内部签发检验报告
    public function issueTestPort(){
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        $test_reprot=M("test_reprot");//实例化对象
        $where['authorizer']=1;
        $where['ifinnerissue']=0;
        $rs=$test_reprot->where($where)->field('id,centreNo')->order('id')->limit("{$offset},{$pagesize}")->select();//查找条件为已经批准并且内部尚未签发的报告
        $count = D("test_reprot")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            'rs'=>$rs,
            'pagination'=>$pagination,
        );
        $this->assign($body);
        $this->display();
    }

//签发按钮功能实现

    public function doUpd(){
// 要修改的数据对象属性赋值
        $data['ifinnerissue'] = 1;
        $id =I("id",0,'intval');
        $rs = array("msg"=>"fail");
        if(D("test_reprot")->where("id=".$id)->save($data)){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
        }
		
		
		
    //检验报告的生成
	public function generateReport(){
        $keyword = I("keyword");//获取参数
        $where= "contract_flow.centreno like '%{$keyword}%' and contract_flow.status=1";
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
        $test_report=M("test_report");
        $data['tplno'] = $mod;
        $test_report->where($where)->save($data); // 根据条件保存修改的数据

        $test_content=M("contract");
        $sample_content=M("sampling_form");

        $status = M("contract_flow")->where($where)->field('status')->find();

        if ($status['status']==1) {
            $shengchengview="";
            $dayinview="hidden";

        }
        if($status['status']==2)
        {
            $shengchengview="hidden";
            $dayinview="";

        }
        $final_content=$test_content->where($where)->find();
        $final_content_two=$sample_content->where($where)->find();
        $htmltable=M("test_report")->where($where)->field('htmltable')->select();

        $body = array(
            'con_list'=>$final_content,
            'sam_list'=>$final_content_two,
            'htmltable'=>($htmltable[0]['htmltable']),
            'shengchengview'=>$shengchengview,
            'dayinview'=>$dayinview,
            'status'=>$status['status'],
        );

        $this->assign($body);
       
       switch($mod){
        case 1:
            $this->display(testReportA);
            break;
        case 2:
            $this->display(testReportB);
            break;
        case 3:
            $this->display(testReportC);
            break;
        case 4:
            $this->display(testReportD);
            break;
        case 5:
            $this->display(testReportE);
            break;
        case 6:
            $this->display(testReportF);
            break;
        case 7:
            $this->display(testReportG);
            break;           
       }

	}
    //修改status,并且生成二维码
	public function doCreate(){
        $centreno=I("centreno");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid=$admin_auth['id'];
        $where= "centreno='{$centreno}'";
        $data=array(
            'status'=>2,
            'report_time'=>date("Y-m-d H:i:s"),
            'report_user_id'=>$userid,
        );
        if(D("contract_flow")->where($where)->save($data)){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
 /*
        $value = 'http://www.baidu.com'; //二维码内容
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 6;//生成图片大小
        //生成二维码图片
        QRcode::png($value, 'qrcode.png', $errorCorrectionLevel, $matrixPointSize, 2);
        $logo = '__Public__/imgs/logo.png';//准备好的logo图片
        $QR = 'qrcode.png';//已经生成的原始二维码图

        if ($logo !== FALSE) {
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR);//二维码图片宽度
            $QR_height = imagesy($QR);//二维码图片高度
            $logo_width = imagesx($logo);//logo图片宽度
            $logo_height = imagesy($logo);//logo图片高度
            $logo_qr_width = $QR_width / 3;
            $scale = $logo_width / $logo_qr_width;
            $logo_qr_height = $logo_height / $scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            //重新组合图片并调整大小
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
                $logo_qr_height, $logo_width, $logo_height);
        }
        //输出图片
        imagepng($QR, './Public/erweima/haha.png');
 */
    }


    //选择编号
    public function seleteKey(){
	   $mod = I("mod");

       $body = array(
           'contactNo'=>$mod,
       );
       $this->assign($body);
       $this->display(select);
	}
    
    
    public function reviseReport(){
        $this->display();
    }

}
?>