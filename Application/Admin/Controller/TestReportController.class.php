<?php
/**
 * Created by PhpStorm.
 * User: Ail
 * Date: 2017/10/14
 * Time: 16:18
 */
namespace Admin\Controller;
use Think\Controller;
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
       $page = I("p",0,'int');
       $pagesize = 10;
       if($page<=0) $page = 1;
       $offset = ( $page-1 ) * $pagesize;

       $test_reprot=M("contract");//实例化对象

       $rs=$test_reprot->field('centreNo,id')->order('id')->limit("{$offset},{$pagesize}")->select();
       $count = M("contract")->count();

       $Page= new \Think\Page($count,$pagesize);
       $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</ a></ul>");
       $pagination= $Page->show();// 分页显示输出
       $body = array(
           'lists'=>$rs,
           'pagination'=>$pagination,
       );
       $this->assign($body);
       $this->display();
   }
    
    //选择模板
   	public function seleteTemp(){
	   $mod = I("mod");
       $conNo=I("conNo");
       //var_dump($conNo);
       
       $test_content=M("contract");
       $sample_content=M("sampling_form");
       //var_dump($test_content);
       //echo '<br /><br /><br /><br /><br /><br />';
       //var_dump($sample_content);
       $map['centreNo']=array('eq',$conNo);
       $final_content=$test_content->where($map)->find();
       $final_content_two=$sample_content->where($map)->find();
       //var_dump($final_content);
       //var_dump($final_content_two);
       $body = array(
           'con_list'=>$final_content,
           '<br /><br /><br /><br /><br />',
           'sam_list'=>$final_content_two,
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
       //echo $template;
      // display($template);
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