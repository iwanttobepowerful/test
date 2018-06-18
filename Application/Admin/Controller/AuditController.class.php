<?php
namespace Admin\Controller;
use Think\Controller;
class AuditController extends Controller {
    public $user = array();
    //初始化方法
    public function _initialize(){
        load('@.functions');
        $this->user = D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }

    /*public function add(){
        $keyword = I("keyword");
        if(!empty($keyword)){
            //查询合同编号
            $where="centreno='{$keyword}'";
            $rs=$contract =D("contract")->where($where)->field('centreNo')->select();
        }
        $this->assign("lists",$rs);
        $this->display();
    }

    //提交修改申请
    public function doAudit(){
        $rs = array("msg"=>"fail");
        $centreno=I("centreno");
        $reason=I("reason");
        $data=array(
            'contractno'=>$centreno,
            'reason'=>$reason,
            'create_time'=>date("Y-m-d H:i:s"),
        );
        if(D("audit_report")->data($data)->add()){
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }*/
    //申请列表
    public function reportList(){
        $de = I('de','A');
        $keyword = I("keyword");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $useraudit=$admin_auth['audit'];
        $if_admin = $admin_auth['super_admin'];
        if($if_admin==1 || $user==16 || $user==17) {
            $view="1";
        }else{
            $view="0";
        }
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;
        if($de =='A'){//内部修改申请
            if(!empty($keyword)){
                $where="r.centreno like '%{$keyword}% ' and r.if_outer=0 and r.if_report=0 and r.if_invalid=0";
            }else{
                $where="r.if_outer=0 and r.if_report=0  and r.if_invalid=0";
            }
            if($if_admin==0)
            $where.=" and r.role_id=".$user;
        }
        elseif($de =='B'){//报告修改申请
            if(!empty($keyword)){
                $where="r.centreno like '%{$keyword}% ' and r.if_report=1";
            }else{
                $where="r.if_report=1";
            }}
        elseif($de =='C'){//外部修改申请
            if(!empty($keyword)){
                $where="r.centreno like '%{$keyword}% ' and r.if_outer=1";
            }
            else{
                $where="r.if_outer=1";
            }}
        elseif($de =='D'){//合同作废申请
            if(!empty($keyword)){
                $where="r.centreno like '%{$keyword}% ' and r.if_invalid = 1";
            }
            else{
                $where="r.if_invalid=1";
            }}
        /*if(!empty($useraudit)){
            $data=explode(',',$useraudit);
            foreach($data as $v){
                $s .="'".$v."',";
            }
            $s=substr($s,0,-1);//利用字符串截取函数消除最后一个逗号
            $where .=" and SUBSTR(r.centreno,7,1) in({$s})";
        }*/
        $rs=D("report_feedback")->alias("r")
            ->field('if(r.status is null,-1,r.status) as sub_status,r.reason,r.create_time,r.centreno,r.id as reid,r.if_sample,a.clientname,a.samplename,a.testcriteria,a.testitem,c.*')
            ->join(' left join contract as a on r.centreNo=a.centreNo left join contract_flow as c on r.centreNo=c.centreNo')
            ->where($where)
            ->limit("{$offset},{$pagesize}")
            ->order('r.create_time desc')->select();
        if($de =='B'){
            $rs=D("report_feedback")->alias("r")
                ->field('if(r.status is null,-1,r.status) as sub_status,r.reason,r.create_time,r.centreno,r.id as reid,a.clientname,a.samplename,a.testcriteria,a.testitem,c.*,b.pdf_path,d.pdf_path as temp_pdf_path')
                ->join(' left join contract as a on r.centreNo=a.centreNo left join contract_flow as c on r.centreNo=c.centreNo')
                ->join('left join test_report as b on r.centreNo=b.centreNo left join test_report_temp as d on r.centreNo=d.centreNo')
                ->where($where)
                ->limit("{$offset},{$pagesize}")
                ->order('r.create_time desc')->select();
        }

        $count = D("report_feedback")->alias("r")->where($where)->count();
        $Page= new \Think\Page($count,$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出
        $body = array(
            'lists'=>$rs,
            'pagination'=>$pagination,
            'view'=>$view,
            'de'=>$de,
            'centreno'=>$keyword,
            'user'=>$user
        );
        $this->assign($body);
        $this->display();
    }
    //允许
    public function isAllow(){
        $id =I("id",0,'intval');
        $de=I("de");
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $contract_user_id = $admin_auth['id'];
        $user=$admin_auth['gid'];//判断是哪个角色
        $a=D("report_feedback")->where("id=".$id)->find();
        $centreno=$a['centreno'];
        $if_admin = $admin_auth['super_admin'];
        $data=array(
            'status'=>1,
        );

        if ($user==16||$if_admin==1) {//审核员和超级管理员的权限
            if ($de == 'A' or $de == "C") {
                if (D("report_feedback")->where("id=" . $id)->save($data)) {
                    if($d = D("test_report")->where("centreno='{$centreno}'")->find()){
                        $pdf_path='.'.$d['pdf_path'];
                        $pdf_sign = '.'.$d['pdf_sign_path'];
                        if(file_exists($pdf_path)){
                            @unlink($pdf_path);
                        }
                        if(file_exists($pdf_sign)){
                            @unlink($pdf_sign);
                        }
                    }
                    $rs['msg'] = 'succ';
                }
            } elseif($de == "D"){
                $data1 = array(
                    'status'=>-1
                );
                $data2 = array(
                    'centreNo'=>$centreno,
                    'status'=>-1,
                    'contract_user_id'=>$contract_user_id,
                    'contract_time'=>Date("Y-m-d H:i:s"),
                );
                $data_cost = array(
                    'Arecord'=>0,
                    'Brecord'=>0,
                    'Crecord'=>0,
                    'Drecord'=>0,
                    'Erecord'=>0,
                    'Frecord'=>0,
                    'Dcopy'=>0,
                    'Donline'=>0,
                    'Drevise'=>0,
                    'Dother'=>0,
                    'remark'=>'',
                    'RArecord'=>0,
                    'RBrecord'=>0,
                    'RCrecord'=>0,
                    'RDrecord'=>0,
                    'RErecord'=>0,
                    'RFrecord'=>0,
                    'RGrecord'=>0,
                    'RHrecord'=>0,
                    'idList'=>'',
                );

                $data_contract = array(
                    'testCost'=>0
                );
                M()->startTrans();
                if(D("contract_flow")->where("centreNo='".$centreno."'")->count()==0){
                    D("contract_flow")->add($data2);
                }else{
                    D("contract_flow")->where("centreNo='".$centreno."'")->save($data1);
                }
                if(D("report_feedback")->where("id=" . $id)->save($data)){
                    D("test_cost")->where("centreNo='".$centreno."'")->save($data_cost);
                    D("contract")->where("centreNo='".$centreno."'")->save($data_contract);
                    M()->commit();
                    $rs['msg'] = 'succ';
                }else{
                    M()->rollback();
                }

            }else {//$de =='B'
                $data1=array(
                    'status'=>1,//到提交审核那一步
                    'isaudit'=>0,
                    'internalpass'=>0,
                );
                M()->startTrans();
                if (D("contract_flow")->where("centreno='{$centreno}'")->save($data1) and D("report_feedback")->where("id=" . $id)->save($data)) {
                    $replace = D("test_report_temp")->where("centreno='{$centreno}'")->find();
                    $rp = array(
                        "tplno"=>$replace['tplno'],
                        "path"=>$replace['path'],
                        "doc_path"=>$replace['doc_path'],
                        "pdf_path"=>$replace['pdf_path'],
                        "modify_time"=>$replace['modify_time'],
                        "qrcode_path"=>$replace['qrcode_path'],
                        "pdf_sign_path"=>$replace['pdf_sign_path']
                    );
                    if(D('test_report')->where("centreno = '{$centreno}'")->save($rp)){
                        M()->commit();
                        $rs['msg'] = 'succ';
                    }else{
                        M()->rollback();
                    }

                }

            }
        }

        $this->ajaxReturn($rs);
    }

    //前台修改申请  允许并入库
    public function isAllowAndInput(){
        $id =I("id",0,'intval');
        $centreno=I("centreno");
        $rs = array("msg"=>"fail");
        $data=array(
            'status'=>3,
        );
        //合同临时表
        $contract = D("contract_temp")->where("id = (select max(id) from contract_temp where centreNo='".$centreno."')")->find();
        //费用临时表
        $contract_cost = D("test_cost_temp")->where("id=(select max(id) from test_cost_temp where centreNo='".$centreno."')")->find();

        $data_temp=Array();//合同入库
        $cost_temp=Array();//费用入库
        $data_info=Array();//通知单入库
        $data_sample=Array();//抽样单入库

        if($contract['clientname']!=null){
            $data_temp['clientName']=$contract['clientname'];
            $data_sample['productUnit']=$contract['clientname'];
        }
        if($contract['productunit']!=null){
            $data_temp['productUnit']=$contract['productunit'];
        }
        if($contract['samplename']!=null){
            $data_temp['sampleName']=$contract['samplename'];
            $data_info['sampleName']=$contract['samplename'];
            $data_sample['sampleName']=$contract['samplename'];
        }
        if($contract['samplecode']!=null){
            $data_temp['sampleCode']=$contract['samplecode'];
        }
        if($contract['grade']!=null){
            $data_temp['grade']=$contract['grade'];
        }
        if($contract['specification']!=null){
            $data_temp['specification']=$contract['specification'];
            $data_sample['specification']=$contract['specification'];
        }
        if($contract['trademark']!=null){
            $data_temp['trademark']=$contract['trademark'];
            $data_sample['trademark']=$contract['trademark'];
        }
        if($contract['productiondate']!=null){
            $data_temp['productionDate']=$contract['productiondate'];
        }
        if($contract['samplequantity']!=null){
            $data_temp['sampleQuantity']=$contract['samplequantity'];
            $data_info['sampleAuantity']=$contract['samplequantity'];
            $data_sample['sampleQuantity']=$contract['samplequantity'];
        }
        if($contract['samplestatus']!=null){
            $data_temp['sampleStatus']=$contract['samplestatus'];
            $data_info['sampleStatus']=$contract['samplestatus'];
        }
        if($contract['ration']!=null){
            $data_temp['ration']=$contract['ration'];
            $data_info['ration']=$contract['ration'];
        }
        if($contract['testcriteria']!=null){
            $data_temp['testCriteria']=$contract['testcriteria'];
            $data_info['testCreiteria']=$contract['testcriteria'];
            $data_sample['testCriteria']=$contract['testcriteria'];
        }
        if($contract['testitem']!=null){
            $data_temp['testItem']=$contract['testitem'];
            $data_info['testItem']=$contract['testitem'];
            $data_sample['testItem']=$contract['testitem'];
        }
        if($contract['postmethod']!=null){
            $data_temp['postMethod']=$contract['postmethod'];
        }
        if($contract['ifsubpackage']!=null){
            $data_temp['ifSubpackage']=$contract['ifsubpackage'];
            $data_sample['ifSubpackage']=$contract['ifsubpackage'];
        }
        if($contract['package_remark']!=null){
            $data_temp['package_remark']=$contract['package_remark'];
            $data_sample['package_remark']=$contract['package_remark'];
        }
        if($contract['clientsign']!=null){
            $data_temp['clientSign']=$contract['clientsign'];
        }
        if($contract['telephone']!=null){
            $data_temp['telephone']=$contract['telephone'];
        }
        if($contract['tax']!=null){
            $data_temp['tax']=$contract['tax'];
        }
        if($contract['postcode']!=null){
            $data_temp['postcode']=$contract['postcode'];
        }
        if($contract['email']!=null){
            $data_temp['email']=$contract['email'];
        }
        if($contract['address']!=null){
            $data_temp['address']=$contract['address'];
        }
        if($contract['remark']!=null){
            $data_temp['remark']=$contract['remark'];
            $data_info['otherComments']=$contract['remark'];
        }
        if($contract['samplestaquan']!=null){
            $data_temp['sampleStaQuan']=$contract['samplestaquan'];
        }
        if($contract['reportdate']!=null){
            $data_temp['reportDate']=$contract['reportdate'];
            $data_info['finishDate']=$contract['reportdate'];
        }
        if($contract['testcost']!=null){
            $data_temp['testCost']=$contract['testcost'];
        }

        //费用入库
        if($contract_cost['arecord']!=null){
            $cost_temp['Arecord']=$contract_cost['arecord'];
        }
        if($contract_cost['brecord']!=null){
            $cost_temp['Brecord']=$contract_cost['brecord'];
        }
        if($contract_cost['crecord']!=null){
            $cost_temp['Crecord']=$contract_cost['crecord'];
        }
        if($contract_cost['drecord']!=null){
            $cost_temp['Drecord']=$contract_cost['drecord'];
        }
        if($contract_cost['erecord']!=null){
            $cost_temp['Erecord']=$contract_cost['erecord'];
        }
        if($contract_cost['frecord']!=null){
            $cost_temp['Frecord']=$contract_cost['frecord'];
        }
        if($contract_cost['g1record']!=null){
            $cost_temp['G1record']=$contract_cost['g1record'];
        }
        if($contract_cost['g2record']!=null){
            $cost_temp['G2record']=$contract_cost['g2record'];
        }
        if($contract_cost['hrecord']!=null){
            $cost_temp['Hrecord']=$contract_cost['hrecord'];
        }
        if($contract_cost['rarecord']!=null){
            $cost_temp['RArecord']=$contract_cost['rarecord'];
        }
        if($contract_cost['rbrecord']!=null){
            $cost_temp['RBrecord']=$contract_cost['rbrecord'];
        }
        if($contract_cost['rcrecord']!=null){
            $cost_temp['RCrecord']=$contract_cost['rcrecord'];
        }
        if($contract_cost['rdrecord']!=null){
            $cost_temp['RDrecord']=$contract_cost['rdrecord'];
        }
        if($contract_cost['rerecord']!=null){
            $cost_temp['RErecord']=$contract_cost['rerecord'];
        }
        if($contract_cost['rfrecord']!=null){
            $cost_temp['RFrecord']=$contract_cost['rfrecord'];
        }
        if($contract_cost['rg1record']!=null){
            $cost_temp['RG1record']=$contract_cost['rg1record'];
        }
        if($contract_cost['rg2record']!=null){
            $cost_temp['RG2record']=$contract_cost['rg2record'];
        }
        if($contract_cost['rhrecord']!=null){
            $cost_temp['RHrecord']=$contract_cost['rhrecord'];
        }
        if($contract_cost['dcopy']!=null){
            $cost_temp['Dcopy']=$contract_cost['dcopy'];
        }
        if($contract_cost['drevise']!=null){
            $cost_temp['Drevise']=$contract_cost['drevise'];
        }
        if($contract_cost['dother']!=null){
            $cost_temp['Dother']=$contract_cost['dother'];
        }
        if($contract_cost['remark']!=null){
            $cost_temp['remark']=$contract_cost['remark'];
        }
        if($contract_cost['idlist']!=null){
            $cost_temp['idList']=$contract_cost['idlist'];
        }
        //是否为抽样检测
        $ifsample = substr($centreno,7,1);
        D("contract")->where("centreNo='".$centreno."'")->save($data_temp);
        D("test_cost")->where("centreNo='".$centreno."'")->save($cost_temp);
        D("work_inform_form")->where("centreNo='".$centreno."'")->save($data_info);
        if($ifsample=='C'){
            D("sampling_form")->where("centreNo='".$centreno."'")->save($data_sample);
        }
        D("report_feedback")->where("id=".$id)->save($data);
        $rs['msg']="succ";
        $this->ajaxReturn($rs);
    }

    //前台修改申请  允许并入库-抽样单
    public function isAllowAndInputSample(){
        $id =I("id",0,'intval');
        $centreno=I("centreno");
        $rs = array("msg"=>"fail");
        $data=array(
            'status'=>3,
        );
        //合同临时表
        $data_sample = D("sampling_form_temp")->where("id = (select max(id) from sampling_form_temp where centreNo='".$centreno."')")->find();

        $data_temp=Array();//抽样单入库

        if($data_sample['samplebase']!=null){
            $data_temp['sampleBase']=$data_sample['samplebase'];
        }
        if($data_sample['sampledate']!=null){
            $data_temp['sampleDate']=$data_sample['sampledate'];
        }
        if($data_sample['sampleplace']!=null){
            $data_temp['samplePlace']=$data_sample['sampleplace'];
        }
        if($data_sample['samplemethod']!=null){
            $data_temp['sampleMethod']=$data_sample['samplemethod'];
        }
        if($data_sample['productiondate']!=null){
            $data_temp['productionDate']=$data_sample['productiondate'];
        }
        if($data_sample['batchno']!=null){
            $data_temp['batchNo']=$data_sample['batchno'];
        }
        if($data_sample['simplersign']!=null){
            $data_temp['simplerSign']=$data_sample['simplersign'];
        }
        if($data_sample['simsigndate']!=null){
            $data_temp['simSignDate']=$data_sample['simsigndate'];
        }
        if($data_sample['sealersign']!=null){
            $data_temp['sealerSign']=$data_sample['sealersign'];
        }
        if($data_sample['seasingdate']!=null){
            $data_temp['seaSingDate']=$data_sample['seasingdate'];
        }
        if($data_sample['enterprisesign']!=null){
            $data_temp['enterpriseSign']=$data_sample['enterprisesign'];
        }
        if($data_sample['entsigndate']!=null){
            $data_temp['entSignDate']=$data_sample['entsigndate'];
        }
        if($data_sample['telephone']!=null){
            $data_temp['telephone']=$data_sample['telephone'];
        }
        if($data_sample['tax']!=null){
            $data_temp['tax']=$data_sample['tax'];
        }
        if($data_sample['address']!=null){
            $data_temp['address']=$data_sample['address'];
        }
        D("sampling_form")->where("centreNo='".$centreno."'")->save($data_temp);
        D("report_feedback")->where("id=".$id)->save($data);

        $rs['msg']="succ";
        $this->ajaxReturn($rs);
    }

    //拒绝
    public function notAllow(){
        $id =I("id");
        $de =I("de");
        $where= "id='{$id}'";
        $rs = array("msg"=>"fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];
        $rf=D("report_feedback")->where($where)->find();
        $arr=$rf['centreno'];
        $data=array(
            'status'=>2,
        );
        $data1=array(
            'if_edit'=>0,
        );
        if ($user==16||$if_admin==1||$user==17){//审核员和超级管理员的权限
            $rs['msg'] = 'succ';
            M()->startTrans();
            $result=D("report_feedback")->where($where)->save($data);
            $result1=D("inspection_report")->where("centreno='{$arr}'")->save($data1);
            if($result!==false and $result1!==false){
                M()->commit();
                $rs['msg'] = 'succ';

            }
            else{
                M()->rollback();
            }}
        $this->ajaxReturn($rs);
    }

    //修改审核的合同详情查询
    public function contractDetail(){
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $user=$admin_auth['gid'];//判断是哪个角色
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        $centreno=I("id");
        $contract=D("contract");//实例化
        $where= "centreno='{$centreno}'";
        $data=$contract->where($where)->field('ifHighQuantity,remark1,remark2',ture)->find();
        $cost=D("test_cost")->where('centreno="'.$centreno.'"')->find();
        //判断是否可以打印
        $ifedit=M('contract')->where($where)->find();
        $sub_status=M('report_feedback')->where('id = (SELECT max(id) from report_feedback WHERE centreNo="'.$centreno.'")')->find();
        if(empty($sub_status)){
            $sub_status['status']=-1;
        }
        $contract_temp = D("contract_temp")->where('id = (select max(id) from contract_temp where centreNo="'.$centreno.'")')->find();
        $cost = D("test_cost")->where('centreNo="'.$centreno.'"')->find();
        $cost_temp = D("test_cost_temp")->where('id = (select max(id) from test_cost_temp where centreNo="'.$centreno.'")')->find();
        $attr = unserialize($cost['idlist']);
        $sttr_temp = unserialize($cost_temp['idlist']);
        if(!empty($attr['a'])) {
            $where1['id'] = array('in', $attr['a']);
            $a = D("test_fee")->where($where1)->select();
        }
        if(!empty($attr['b'])) {
            $where2['id'] = array('in', $attr['b']);
            $b = D("test_fee")->where($where2)->select();
        }
        if(!empty($attr['c'])) {
            $where3['id'] = array('in', $attr['c']);
            $c = D("test_fee")->where($where3)->select();
        }
        if(!empty($attr['d'])) {
            $where4['id'] = array('in', $attr['d']);
            $d = D("test_fee")->where($where4)->select();
        }
        if(!empty($attr['e'])) {
            $where5['id'] = array('in', $attr['e']);
            $e = D("test_fee")->where($where5)->select();
        }
        if(!empty($attr['f'])) {
            $where6['id'] = array('in', $attr['f']);
            $f = D("test_fee")->where($where6)->select();
        }
        if(!empty($attr['g1'])) {
            $where4['id'] = array('in', $attr['g1']);
            $g1 = D("test_fee")->where($where4)->select();
        }
        if(!empty($attr['g2'])) {
            $where5['id'] = array('in', $attr['g2']);
            $g2 = D("test_fee")->where($where5)->select();
        }
        if(!empty($attr['h'])) {
            $where6['id'] = array('in', $attr['h']);
            $h = D("test_fee")->where($where6)->select();
        }

        //合同临时表的数据
        if(!empty($sttr_temp['a'])) {
            $where1['id'] = array('in', $sttr_temp['a']);
            $a1 = D("test_fee")->where($where1)->select();
        }else $a1=-1;
        if(!empty($sttr_temp['b'])) {
            $where2['id'] = array('in', $sttr_temp['b']);
            $b1 = D("test_fee")->where($where2)->select();
        }else $b1=-1;
        if(!empty($sttr_temp['c'])) {
            $where3['id'] = array('in', $sttr_temp['c']);
            $c1 = D("test_fee")->where($where3)->select();
        }else $c1=-1;
        if(!empty($sttr_temp['d'])) {
            $where4['id'] = array('in', $sttr_temp['d']);
            $d1 = D("test_fee")->where($where4)->select();
        }else $d1=-1;
        if(!empty($sttr_temp['e'])) {
            $where5['id'] = array('in', $sttr_temp['e']);
            $e1 = D("test_fee")->where($where5)->select();
        }else $e1=-1;
        if(!empty($sttr_temp['f'])) {
            $where6['id'] = array('in', $sttr_temp['f']);
            $f1 = D("test_fee")->where($where6)->select();
        }else $f1=-1;
        if(!empty($sttr_temp['g1'])) {
            $where4['id'] = array('in', $sttr_temp['g1']);
            $g11 = D("test_fee")->where($where4)->select();
        }else $g11=-1;
        if(!empty($sttr_temp['g2'])) {
            $where5['id'] = array('in', $sttr_temp['g2']);
            $g21 = D("test_fee")->where($where5)->select();
        }else $g21=-1;
        if(!empty($sttr_temp['h'])) {
            $where6['id'] = array('in', $sttr_temp['h']);
            $h1 = D("test_fee")->where($where6)->select();
        }else $h1=-1;

        $body=array(
            'one'=>$data,
            'cost'=>$cost,
            'one_temp'=>$contract_temp,
            'cost_temp'=>$cost_temp,
            'ifedit'=>$ifedit,
            'sub_status'=>$sub_status,
            'user'=>$user,
            'if_admin'=>$if_admin,
            'a'=>$a,
            'b'=>$b,
            'c'=>$c,
            'd'=>$d,
            'e'=>$e,
            'f'=>$f,
            'g1'=>$g1,
            'g2'=>$g2,
            'h'=>$h,
            'a1'=>$a1,
            'b1'=>$b1,
            'c1'=>$c1,
            'd1'=>$d1,
            'e1'=>$e1,
            'f1'=>$f1,
            'g11'=>$g11,
            'g21'=>$g21,
            'h1'=>$h1
        );
        $this->assign($body);
        $this->display();
    }

    //抽样单显示
    public function sampleShow(){
        $keyword = I("id");//获取参数
        $where= "centreno='{$keyword}'";
        $result=M('sampling_form')->where($where)->find();
        $result_temp=M('sampling_form_temp')->where('id = (select max(id) from sampling_form_temp where centreNo="'.$keyword.'")')->find();

        $simsigndateyear = $result['simsigndate'] ? date("Y",strtotime($result['simsigndate'])):"";
        $simsigndatemonth =  $result['simsigndate'] ? date("m",strtotime($result['simsigndate'])):"";
        $simsigndateday =  $result['simsigndate'] ? date("d",strtotime($result['simsigndate'])):"";
        array_push($result,$simsigndateyear);
        array_push($result,$simsigndatemonth);
        array_push($result,$simsigndateday);

        $seasingdateyear =  $result['seasingdate'] ? date("Y",strtotime($result['seasingdate'])):"";
        $seasingdatemonth =  $result['seasingdate'] ? date("m",strtotime($result['seasingdate'])):"";
        $seasingdateday =  $result['seasingdate'] ? date("d",strtotime($result['seasingdate'])):"";
        array_push($result,$seasingdateyear);
        array_push($result,$seasingdatemonth);
        array_push($result,$seasingdateday);

        $entsigndateyear =  $result['entsigndate'] ? date("Y",strtotime($result['entsigndate'])):"";
        $entsigndatemonth =  $result['entsigndate'] ? date("m",strtotime($result['entsigndate'])):"";
        $entsigndateday =  $result['entsigndate'] ? date("d",strtotime($result['entsigndate'])):"";
        array_push($result,$entsigndateyear);
        array_push($result,$entsigndatemonth);
        array_push($result,$entsigndateday);


        $sub_status=M('report_feedback')->where('id = (SELECT max(id) from report_feedback WHERE centreNo="'.$keyword.'")')->find();
        if(empty($sub_status)){
            $sub_status['status']=-1;
        }

        $body=array(
            'one'=>$result,
            'one_temp'=>$result_temp,
            'sub_status'=>$sub_status,
        );
        $this->assign($body);
        $this->display();
    }
}

