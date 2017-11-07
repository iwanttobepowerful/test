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
        $tpl = D("tpl")->where("id=".$mod)->find();

        //$data['tplno'] = $mod;
        //$test_report->where($where)->save($data); // 根据条件保存修改的数据

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
            'qrimg'=>$this->qrcode($final_content['centreno'],"getCurrentHost().'/admin/SeeReport/show?centreno='{$final_content['centreno']}"),
        );

        $this->assign($body);
        $tplfile = $tpl['filename'];
        $this->display($tplfile);
	}
    //修改status
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
    }
//生成二维码
    public function qrcode($centreno,$qr_data){
        $save_path = './Public/qrcode/';  //图片存储的绝对路径
        $web_path = '/Admin/qrcode/';        //图片在网页上显示的路径
        $qr_level = 'L';
        $qr_size = '4';
        $save_prefix = '';
        if(file_exists($save_path.md5($centreno).'.png')){
            $img = $save_path.md5($centreno).'.png';
            //unlink($img);
        }elseif($filename = createQRcode($centreno,$save_path,$qr_data,$qr_level,$qr_size,$save_prefix)){
            $img = $save_path.$filename;
        }
        return substr($img,1);
        //echo "<img src='".$pic."'>";
    }

    //选择编号
    public function seleteKey(){
	   $mod = I("mod");
        $tpl=D("tpl")->select();

        $body = array(
           'contactNo'=>$mod,
            'tpl'=>$tpl,
       );
       $this->assign($body);
       $this->display(select);
	}
    
    
    public function reviseReport(){
        $this->display();
    }

}
?>