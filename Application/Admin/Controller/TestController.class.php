<?php

namespace Admin\Controller;

use Think\Controller;

class TestController extends Controller
{
    public $user = null;

    public function _initialize()
    {//系统Action类提供了一个初始化方法_initialize接口，可以用于扩展需要，_initialize方法会在所有操作方法调用之前首先执行
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active', strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active', strtolower(ACTION_NAME));
    }

    //工作通知单查询
    public function infoList()
    {
        $keyword = I("keyword");//获取参数
        $where = "centreno='{$keyword}'";
        $result = M('work_inform_form')->where($where)->field("id,centreno,samplename")->select();
        $body = array(
            'lists' => $result,
        );
        $this->assign($body);
        $this->display();
    }

    //工作通知单显示
    public function infoShow()
    {
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        $user = $admin_auth['gid'];//判断是哪个角色
        if ($user == 9 || $if_admin == 1) {
            $gid = 1;
        } else {
            $gid = 0;
        }
        $keyword = I("id");//获取参数
        $where = "centreNo='{$keyword}'";
        $work_inform_form = M('work_inform_form');
        $result = $work_inform_form->where($where)->find();
        $contract_flow = D("contract_flow")->where($where)->field('status')->find();
        $status = $contract_flow['status'];
        //判断是否可以打印
        $ifedit = M('contract')->where($where)->find();
        $sub_status = M('report_feedback')->where('id = (SELECT max(id) from report_feedback WHERE centreNo="' . $keyword . '")')->find();
        if (empty($sub_status)) {
            $sub_status['status'] = -1;
        }
        $body = array(
            'one' => $result,
            'ifedit' => $ifedit,
            'sub_status' => $sub_status,
            'status' => $status,
            'gid' => $gid,
            'user' => $user,
            'if_admin' => $if_admin
        );
        $this->assign($body);
        $this->display();
    }

    //抽样单查询
    public function sampleList()
    {
        $keyword = I("keyword");//获取参数
        $where = "centreno='{$keyword}'";
        $result = M('sampling_form')->where($where)->field("id,centreno,clientname,productunit")->select();
        $body = array(
            'lists' => $result,
        );
        $this->assign($body);
        $this->display();
    }

    //抽样单显示
    public function sampleShow()
    {
        $keyword = I("id");//获取参数
        $where = "centreno='{$keyword}'";
        $result = M('sampling_form')->where($where)->find();

        $admin_auth = session("admin_auth");
        $if_admin = $admin_auth['super_admin'];
        $roleid = $admin_auth['gid'];

        $role = D('common_role')->where('id=' . $roleid)->find();
        if ($role['rolename'] == "领导" || $if_admin == 1 || $role['rolename'] == "前台人员") {
            $if_edit = 1;
        } else {
            $if_edit = 0;
        }
        $simsigndateyear = $result['simsigndate'] ? date("Y", strtotime($result['simsigndate'])) : "";
        $simsigndatemonth = $result['simsigndate'] ? date("m", strtotime($result['simsigndate'])) : "";
        $simsigndateday = $result['simsigndate'] ? date("d", strtotime($result['simsigndate'])) : "";
        array_push($result, $simsigndateyear);
        array_push($result, $simsigndatemonth);
        array_push($result, $simsigndateday);
        $seasingdateyear = $result['seasingdate'] ? date("Y", strtotime($result['seasingdate'])) : "";
        $seasingdatemonth = $result['seasingdate'] ? date("m", strtotime($result['seasingdate'])) : "";
        $seasingdateday = $result['seasingdate'] ? date("d", strtotime($result['seasingdate'])) : "";
        array_push($result, $seasingdateyear);
        array_push($result, $seasingdatemonth);
        array_push($result, $seasingdateday);
        $entsigndateyear = $result['entsigndate'] ? date("Y", strtotime($result['entsigndate'])) : "";
        $entsigndatemonth = $result['entsigndate'] ? date("m", strtotime($result['entsigndate'])) : "";
        $entsigndateday = $result['entsigndate'] ? date("d", strtotime($result['entsigndate'])) : "";
        array_push($result, $entsigndateyear);
        array_push($result, $entsigndatemonth);
        array_push($result, $entsigndateday);
        //后面跟着现场上传图片
        $rs = D("sample_picture")->where($where)->field("picture_name")->select();//取出地址
        //换成字符串后再替换
        foreach ($rs as $v) {
            $v = join(",", $v); //可以用implode将一维数组转换为用逗号连接的字符串，join是别名
            $temp[] = $v;
        }
        foreach ($temp as $v) {
            $s .= $v . ",";
        }
        $s = substr($s, 0, -1);//利用字符串截取函数消除最后一个逗号
        $list = str_replace("_thumb", "", $s);
        $path = explode(',', $list);

        //判断是否可以打印
        $ifedit = M('contract')->where($where)->find();
        $sub_status = M('report_feedback')->where('id = (SELECT max(id) from report_feedback WHERE centreNo="' . $keyword . '")')->find();
        if (empty($sub_status)) {
            $sub_status['status'] = -1;
        }

        $body = array(
            'one' => $result,
            'if_edit' => $if_edit,
            'list' => $path,
            'sub_status' => $sub_status,
            'ifedit' => $ifedit,
            'if_admin' => $if_admin,
            'user' => $roleid
        );
        $this->assign($body);
        $this->display();
    }

//检测记录
    public function recordPicture()
    {
        $de = I("de", 'A');
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        $userid = $admin_auth['id'];
        $user = $admin_auth['gid'];//判断是哪个角色
        $department = $admin_auth['department'];//判断是哪个部门的
        $keyword = I("keyword");//获取参数
        $keyword = trim($keyword);
        $begin_time = I("begin_time");
        $end_time = I("end_time");
        $sortby = I("sortby");
        $where = "1=1";
        if ($de == 'A') {
            $where .= " and contract_flow.status in('0','7') ";
        } elseif ($de == 'B') {
            $where .= " and contract_flow.status  not in('0','7','-1') ";
        }
        if ($sortby == 1) {
            $begin_time && $where .= " and date_format(contract_flow.contract_time,'%Y-%m-%d') >='{$begin_time}'";
            $end_time && $where .= " and date_format(contract_flow.contract_time,'%Y-%m-%d') <='{$end_time}'";
        } elseif ($sortby == 2) {
            $begin_time && $where .= " and date_format(contract_flow.takelist_time,'%Y-%m-%d') >='{$begin_time}'";
            $end_time && $where .= " and date_format(contract_flow.takelist_time,'%Y-%m-%d') <='{$end_time}'";
        }
        if ($user == 8 || $user == 15 || $if_admin == 1) {
            if (!empty($keyword)) {
                $where .= " and contract_flow.centreno like '%{$keyword}%'";
            }
        } else {
            //判断G1/G2的特殊化
            if ($department == 'G1') {
                $where .= " and SUBSTR(contract_flow.centreno,7,1) = 'G' and SUBSTR(contract_flow.centreno,9,3) <='500'";
            } elseif ($department == 'G2') {
                $where .= " and SUBSTR(contract_flow.centreno,7,1) = 'G' and SUBSTR(contract_flow.centreno,9,3) >'500'";
            } else {
                $where .= " and SUBSTR(contract_flow.centreNo,7,1) = '{$department}'";
            }
            if (!empty($keyword)) {
                $where .= "and contract_flow.centreno like '%{$keyword}%'";
            }
        }
        if ($user == 9 || $if_admin == 1) {//只有报告编制员，超级管理员才能操作
            $view = "visible";
        } else {
            $view = "hidden";
        }
        $page = I("p", 'int');
        $pagesize = 10;
        if ($page <= 0) $page = 1;
        $offset = ($page - 1) * $pagesize;
        $list = M('contract_flow')->where($where)
            ->join('work_inform_form ON contract_flow.centreNo = work_inform_form.centreNo')//从工作通知单取数据
            ->join('contract as a on contract_flow.centreno=a.centreno')
            ->field('contract_flow.back_time,contract_flow.gz_back,contract_flow.sh_back,contract_flow.bz_back,contract_flow.ifback,contract_flow.takelist_user_id,contract_flow.status,work_inform_form.workDate,work_inform_form.centreNo,work_inform_form.sampleName,work_inform_form.testCreiteria,a.centreno1,a.centreno2,a.centreno3')
            ->order('contract_flow.back_time desc,work_inform_form.workDate desc')
            ->limit("{$offset},{$pagesize}")->select();//从合同表!!!!里取出对应中心编号的信息
        if ($list) {
            $con_list = array();//反馈
            foreach ($list as $contract) {
                array_push($con_list, "'" . $contract['centreno'] . "'");
            }
            $centreno_str = implode(',', $con_list);
            $no_feed_list = D('report_feedback')->where(' id in (select max(id) from report_feedback where if_report=0 and centreNo in (' . $centreno_str . ') group by centreNo)')->group('centreNo')->select();
            $con_list = array();
            if ($no_feed_list) {
                foreach ($no_feed_list as $no_feed) {
                    $con_list[$no_feed['centreno']] = $no_feed;
                }
            }
            foreach ($list as $key => $val) {
                if ($con_list[$val['centreno']]) {
                    $val['sub_status'] = $con_list[$val['centreno']]['status'];
                    $val['if_outer'] = $con_list[$val['centreno']]['if_outer'];
                } else {
                    $val['sub_status'] = -1;
                    $val['if_outer'] = -1;
                }
                $list[$key] = $val;
            }
        }
        //dump($list);die;
        $count = D("contract_flow")->where($where)->count();
        $Page = new \Think\Page($count, $pagesize);
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination = $Page->show();// 分页显示输出
        $body = array(
            'lists' => $list,
            'pagination' => $pagination,
            'userid' => $userid,
            'view' => $view,
            'de' => $de,
            'begin_time' => $begin_time,
            'end_time' => $end_time,
            'sortby' => $sortby,
        );
        $this->assign($body);
        $this->display();
    }

    //接单操作
    public function doneTakeList()
    {
        $centreno = I("centreno");
        $rs = array("msg" => "fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid = $admin_auth['id'];
        $user = $admin_auth['gid'];
        $where = "centreno='{$centreno}'";
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        //$role = D('common_role')->where('id='.$user)->find();
        if ($user == 9 || $if_admin == 1) {
            $data = array(
                'status' => 7,
                'takelist_time' => date("Y-m-d H:i:s"),
                'takelist_user_id' => $userid,
            );
            if (D("contract_flow")->where($where)->save($data)) {
                $rs['msg'] = 'succ';
            }
        }
        $this->ajaxReturn($rs);
    }

//检测记录上传完毕按钮
    public function doAllSave()
    {
        $centreno = I("centreno");
        $where = "centreno='{$centreno}'";
        $record = D('test_record')->where($where)->select();
        if (empty($record)) {
            $rs['msg'] = 'error';
            $this->ajaxReturn($rs);
        }
        $rs = array("msg" => "fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid = $admin_auth['id'];
        $data = array(
            'status' => 8,
            'takelist_all_time' => date("Y-m-d H:i:s"),
        );
        if (D("contract_flow")->where($where)->save($data)) {
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }

    //有退回的检测记录上传完毕按钮
    public function doAllSave1()
    {
        $centreno = I("centreno");
        $sortby = I('sortby');
        $where = "centreno='{$centreno}'";
        $record = D('test_record')->where($where)->select();
        if (empty($record)) {
            $rs['msg'] = 'error';
            $this->ajaxReturn($rs);
        }
        $rs = array("msg" => "fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid = $admin_auth['id'];
        $data = array(
            'status' => $sortby,
            'ifback' => 0,
            'takelist_all_time' => date("Y-m-d H:i:s"),
        );
        if (D("contract_flow")->where($where)->save($data)) {
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }

    //检测记录上传图片
    public function recordPictureUp()
    {
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        $user = $admin_auth['gid'];//判断是哪个角色
        if ($user == 9 || $if_admin == 1) {
            $gid = 1;
        } else {
            $gid = 0;
        }
        $id = I("idnum", 0, 'intval');
        $centreno = I("id");//中心编号
        $page = I("p", 'int');
        $pagesize = 10;
        if ($page <= 0) $page = 1;
        $offset = ($page - 1) * $pagesize;
        $where = "centreno='{$centreno}'";
        if ($id) {
            $pic = D('test_record')->where("id=" . $id)->find();
        }
        $result = D('test_record')
            ->limit("{$offset},{$pagesize}")->where($where)->order('name,id')->select();
        $status = D("contract_flow")->where($where)->find();
        $view = $status['status'];
        $count = D("test_record")->where($where)->count();//!!!!!!!!!!!!!!
        $Page = new \Think\Page($count, $pagesize);
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination = $Page->show();// 分页显示输出
        $body = array(
            'pic' => $pic,
            'lists' => $result,
            'pagination' => $pagination,
            'centreno' => $centreno,//!!!!!!!!!!!!!!
            'view' => $view,
            'gid' => $gid
        );
        $this->assign($body);
        $this->display();
    }

    /*public function picUp(){
        $id =I("id",0,'intval');
        $centreno=I("centreno");//!!!!!!!!!!!!!!!!!!!!!!!
        if($id){
            $pic = D('test_record')->where("id=".$id)->find();
        }
        $body = array(
            'pic' => $pic,
            'centreno'=>$centreno,//!!!!!!!!!!!!!!!!!!!!!!!
        );
        $this->assign($body);
        $this->display();
    }*/
    public function doUploadPic()
    {
        $id = I("id", 0, 'intval');
        $centreno = I("centreno");//!!!!!!!!!!!!!!!!
        $imgurl = I("imgurl");
        $filename = I('filename');
        //截掉后缀名
        $name = strpos($filename, ".");
        $filename = substr($filename, 0, $name);
        $result = array("msg" => "fail");
        if (empty($imgurl)) {
            $result['msg'] = "无效的提交！";
            $this->ajaxReturn($result);
        }
        $a = substr($imgurl, 0, 1);
        if ($a == '.') {
            $imgurl = substr($imgurl, 1);
        }
        $data = array("name" => $filename, "centreno" => $centreno, "path" => $imgurl, 'lastmodifytime' => date("Y-m-d H:i:s"));//!!!!!!!!!"centreno"=>$centreno,  'lastmodifytime'=>date("Y-m-d H:i:s")
        $pic = D("test_record")->where("id=" . $id)->find();
        if ($pic) {
            if (D("test_record")->where("id=" . $pic['id'])->save($data)) {
                $result['msg'] = 'succ';
            }
        } else {
            if (D("test_record")->data($data)->add()) {
                $result['msg'] = 'succ';
            }
        }
        $this->ajaxReturn($result);
    }

    public function doDeletePic()
    {
        $id = I("id", 0, 'intval');
        $rs = array("msg" => "fail");
        if (D("test_record")->where("id=" . $id)->delete()) {
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }

    //批量打印
    public function doPrint()
    {
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        $user = $admin_auth['gid'];//判断是哪个角色
        $id = I("id");//获取勾选的id值
        $type = I("type");//获取type
        $data = explode(',', $id);
        $where['id'] = array('in', $data);
        $where1 = "centreno='{$id}'";
        if ($type == 1) {
            $rs = D("sample_picture")->where($where)->field('picture_name')->select();
        } elseif ($type == 2) {
            $rs = D("test_record")->where($where1)->field('path')->select();
        } else {
            $rs = D("test_record")->where($where)->field('path')->select();
        }
        //换成字符串后再替换
        foreach ($rs as $v) {
            $v = join(",", $v); //可以用implode将一维数组转换为用逗号连接的字符串，join是别名
            $temp[] = $v;
        }
        foreach ($temp as $v) {
            $s .= $v . ",";
        }
        $s = substr($s, 0, -1);//利用字符串截取函数消除最后一个逗号
        $list = str_replace("_thumb", "", $s);
        $path = explode(',', $list);
        $body = array(
            'list' => $path,
            'user' => $user,
            'if_admin' => $if_admin
        );
        $this->assign($body);
        $this->display();
    }

//上传检测报告显示页面
    public function record()
    {
        //phpinfo();die;
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        $user = $admin_auth['gid'];//判断是哪个角色
        $department = $admin_auth['department'];
        $page = I("p", 'int');
        $pagesize = 20;
        if ($page <= 0) $page = 1;
        $offset = ($page - 1) * $pagesize;
        $where = "contract_flow .status=1";
        if ($user == 8 || $user == 15 || $user == 13 || $if_admin == 1) {
            //
        } else {
            //判断G1/G2的特殊化
            if ($department == 'G1') {
                $where .= " and SUBSTR(contract_flow.centreno,7,1) = 'G' and SUBSTR(contract_flow.centreno,9,3) <='500'";
            } elseif ($department == 'G2') {
                $where .= " and SUBSTR(contract_flow.centreno,7,1) = 'G' and SUBSTR(contract_flow.centreno,9,3) >'500'";
            } else {
                $where .= " and SUBSTR(contract_flow.centreno,7,1) = '{$department}'";
            }
        }
        $list = D("contract_flow ")->where($where)
            ->join('left join contract as a on contract_flow .centreno=a.centreno left join test_report as t on contract_flow .centreno=t.centreno')
            ->field('contract_flow .*,a.*,t.path,t.doc_path,t.pdf_path')
            ->order('contract_flow.back_time desc,contract_flow .report_time desc,a.id desc')->limit("{$offset},{$pagesize}")->select();
        //当已经生成报告，状态为1的时候，才能上传检测报告
        if ($list) {
            $con_list = array();//反馈
            foreach ($list as $contract) {
                array_push($con_list, "'" . $contract['centreno'] . "'");
            }
            $centreno_str = implode(',', $con_list);
            $no_feed_list = D('report_feedback')->where(' id in (select max(id) from report_feedback where centreNo in (' . $centreno_str . ') group by centreNo)')->group('centreNo')->select();
            $con_list = array();
            if ($no_feed_list) {
                foreach ($no_feed_list as $no_feed) {
                    $con_list[$no_feed['centreno']] = $no_feed;
                }
            }
            foreach ($list as $key => $val) {
                if ($con_list[$val['centreno']]) {
                    $val['sub_status'] = $con_list[$val['centreno']]['status'];
                    $val['if_report'] = $con_list[$val['centreno']]['if_report'];
                } else {
                    $val['sub_status'] = -1;
                    $val['if_report'] = -1;
                }
                $list[$key] = $val;
            }
        }
        $count = D("contract_flow")->where($where)->count();//!!!!!!!!!!!!!!
        $Page = new \Think\Page($count, $pagesize);
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination = $Page->show();// 分页显示输出
        $body = array(
            'pagination' => $pagination,
            'lists' => $list,
        );
        $this->assign($body);
        $this->display();
    }

//上传检测报告显示列表
    public function recordList()
    {
        $page = I("p", 'int');
        $pagesize = 10;
        if ($page <= 0) $page = 1;
        $offset = ($page - 1) * $pagesize;
        $centreno = I("id");//获取中心编号
        $where = "centreno='{$centreno}'";
        $result = M('test_report')->limit("{$offset},{$pagesize}")->where($where)->select();
        $count = M("test_report")->where($where)->count();//!!!!!!!!!!!!!!
        $Page = new \Think\Page($count, $pagesize);
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination = $Page->show();// 分页显示输出
        $body = array(
            'lists' => $result,
            'pagination' => $pagination,
            'centreno' => $centreno,//!!!!!!!!!!!!!!
        );
        $this->assign($body);
        $this->display();
    }

//上传检测报告页面
    public function recordUpload()
    {
        //$id =I("id",0,'intval');
        $centreno = I("centreno");
        $where = "centreno='{$centreno}'";
        //if($id){
        $report = D('test_report')->where($where)->find();
        //}
        $body = array(
            'report' => $report,
            'centreno' => $centreno,
        );
        $this->assign($body);
        $this->display();
    }

//上传检测报告提交word
    public function doUp()
    {
        $id = I("id", 0, 'intval');//test_report的id
        $centreno = I("centreno");
        $where = "centreno='{$centreno}'";
        $fileurl = I("fileurl");
        $result = array("msg" => "fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid = $admin_auth['id'];
        if (empty($fileurl)) {
            $result['msg'] = "无效的提交！";
            $this->ajaxReturn($result);
        }
        $report = D("test_report")->where("centreno='{$centreno}'")->find();
        $report['tplno'] && $tpl = D("tpl")->where("id=" . $report['tplno'])->find();
        //pdf转换
        $data = array(
            //"centreNo"=>$centreno,
            "path" => $fileurl,
            'modify_time' => date("Y-m-d H:i:s"),
        );
        //$pdf = convert2Pdf(ROOT_PATH,$data['path'],$centreno);
        $imgFiles = array();//delete image
        //$distfile = "/Public/attached/2017-11-21/SJ-4-77_2017_01.pdf";
        //转image,在测试服务器上测试，本地需要配置环境
        //demo
        $imageFiles = convertPdf2Image(ROOT_PATH, $data['path'], $centreno);
        if ($imageFiles) {
            //转换成功,合并二维码
            /*
            //第一页
            if(file_exists($imageFiles[0]) && file_exists(ROOT_PATH . $report['qrcode_path'])){
                $baseinfo = pathinfo($imageFiles[0]);
                $saveFile = $baseinfo['dirname'] . '/'.$baseinfo['filename'].'-tmp.'.$baseinfo['extension'];
                waterMark($imageFiles[0],ROOT_PATH . $report['qrcode_path'],$saveFile,array(2048,3160));
                @rename($saveFile,$imageFiles[0]);
            }
            //最后一页
            if(count($imageFiles) >1 && file_exists($imageFiles[count($imageFiles)-1]) && file_exists(ROOT_PATH . $report['qrcode_path'])){
                $baseinfo = pathinfo($imageFiles[count($imageFiles)-1]);
                $saveFile = $baseinfo['dirname'] . '/'.$baseinfo['filename'].'-tmp.'.$baseinfo['extension'];
                waterMark($imageFiles[count($imageFiles)-1],ROOT_PATH . $report['qrcode_path'],$saveFile,array(1948,2870));
                @rename($saveFile,$imageFiles[count($imageFiles)-1]);
            }
            */
            //再转换成pdf
            //$pdf = './Public/attached/report/'.$centreno.'.pdf';
            //convertImageToPdf(ROOT_PATH,substr($pdf,1),$imageFiles);
            $imgFiles = $imageFiles;
            //对外签加公章
            if (file_exists($imageFiles[0]) && file_exists($imageFiles[1])) {
                $baseinfo = pathinfo($imageFiles[0]);
                if ($tpl['subtype'] == 2) {
                    //小中心
                    $tmpSavefile = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-mark.' . $baseinfo['extension'];
                    waterMark($imageFiles[0], './Public/static/images/sealB.png', $tmpSavefile, array(1050, 2650));
                    //左上角章
                    $tmpSavefile2 = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-sign.' . $baseinfo['extension'];
                    waterMark($tmpSavefile, './Public/static/images/sign.png', $tmpSavefile2, array(350, 60));
                    //带mark的pdf
                    @rename($tmpSavefile2, $imageFiles[0]);
                    $imgFiles[] = $tmpSavefile;
                    //图二带章
                    $baseinfo = pathinfo($imageFiles[1]);
                    $tmpSavefile = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-mark2.' . $baseinfo['extension'];
                    waterMark($imageFiles[1], './Public/static/images/sealB.png', $tmpSavefile, array(1600, 2380));
                } else {
                    //大中心
                    $tmpSavefile = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-mark.' . $baseinfo['extension'];
                    waterMark($imageFiles[0], './Public/static/images/sealB.png', $tmpSavefile, array(700, 2700));
                    //第二个公章
                    $tmpSavefile2 = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-mark2.' . $baseinfo['extension'];
                    waterMark($tmpSavefile, './Public/static/images/sealA.png', $tmpSavefile2, array(1300, 2700));
                    //左上角章
                    $tmpSavefile3 = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-sign.' . $baseinfo['extension'];
                    waterMark($tmpSavefile2, './Public/static/images/sign.png', $tmpSavefile3, array(350, 60));
                    //带mark的pdf
                    @rename($tmpSavefile3, $imageFiles[0]);
                    $imgFiles[] = $tmpSavefile2;
                    //图二带章
                    $baseinfo = pathinfo($imageFiles[1]);
                    $tmpSavefile = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-mark2.' . $baseinfo['extension'];
                    waterMark($imageFiles[1], './Public/static/images/sealA.png', $tmpSavefile, array(1600, 2400));
                }

                //左上角章
                $tmpSavefile2 = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-sign2.' . $baseinfo['extension'];
                waterMark($tmpSavefile, './Public/static/images/sign.png', $tmpSavefile2, array(350, 0));
                @rename($tmpSavefile2, $imageFiles[1]);
//加水印
                //再转换成pdf
                $signPdf = './Public/attached/report/' . $centreno . '-sign.pdf';
                convertImageToPdf(ROOT_PATH, substr($signPdf, 1), $imageFiles, 1024, './Public/static/images/wmfull.png');
                //获取临时转换图片地址
                $temp= implode("^",$imgFiles);
                $temp1 = str_replace(".jpg","-thumb.jpg",$temp);
                $temp2 = str_replace(".jpg","-wmthumb.jpg",$temp);
                $arr1 =explode("^",$temp1);
                $arr2 =explode("^",$temp2);
                foreach ($arr1 as $v1){
                    $imgFiles[] = $v1;
                }
                foreach ($arr2 as $v2){
                    $imgFiles[] = $v2;
                }
                $imgFiles[] = $tmpSavefile;
                $imgFiles[] = $tmpSavefile2;
            }
            $data['pdf_path'] = $data['path'];
            $data['pdf_sign_path'] = substr($signPdf, 1);
            if (D("test_report")->where("centreno='{$centreno}'")->save($data)) {
                if ($imgFiles) {
                    foreach ($imgFiles as $value) {
                        if($value){
                            @unlink($value);
                        }
                    }
                }
                $result['msg'] = "succ";
            }
         else {
            $result['msg'] = "转换pdf失败";
        }
        /*
        $res = json_decode($res,true);
        if($res['retMsg']=='success'){
            $outputURLs = $res['outputURLs'];
            $pdfUrl = $outputURLs[0];
            $data['pdf_path'] = $pdfUrl;
        }else{
            $result['msg'] = "转换pdf失败";
            $this->ajaxReturn($result);
        }
        */
        $this->ajaxReturn($result);
    }
    }

    //申请修改临时生成的报告
    public function doUp1()
    {
        $centreno = I("centreno");
        $fileurl = I("fileurl");
        $result = array("msg" => "fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid = $admin_auth['id'];
        if (empty($fileurl)) {
            $result['msg'] = "无效的提交！";
            $this->ajaxReturn($result);
        }
        $report = D("test_report_temp")->where("centreno='{$centreno}'")->find();
        $report['tplno'] && $tpl = D("tpl")->where("id=" . $report['tplno'])->find();
        //pdf转换
        $data = array(
            //"centreNo"=>$centreno,
            "path" => $fileurl,
            'modify_time' => date("Y-m-d H:i:s"),
        );
        //$pdf = convert2Pdf(ROOT_PATH,$data['path'],$centreno);
        $imgFiles = array();//delete image
        //$distfile = "/Public/attached/2017-11-21/SJ-4-77_2017_01.pdf";
        //转image,在测试服务器上测试，本地需要配置环境
        //demo
        $imageFiles = convertPdf2Image(ROOT_PATH, $data['path'], $centreno);
        if ($imageFiles) {
            //转换成功,合并二维码
            /*
            //第一页
            if(file_exists($imageFiles[0]) && file_exists(ROOT_PATH . $report['qrcode_path'])){
                $baseinfo = pathinfo($imageFiles[0]);
                $saveFile = $baseinfo['dirname'] . '/'.$baseinfo['filename'].'-tmp.'.$baseinfo['extension'];
                waterMark($imageFiles[0],ROOT_PATH . $report['qrcode_path'],$saveFile,array(2048,3160));
                @rename($saveFile,$imageFiles[0]);
            }
            //最后一页
            if(count($imageFiles) >1 && file_exists($imageFiles[count($imageFiles)-1]) && file_exists(ROOT_PATH . $report['qrcode_path'])){
                $baseinfo = pathinfo($imageFiles[count($imageFiles)-1]);
                $saveFile = $baseinfo['dirname'] . '/'.$baseinfo['filename'].'-tmp.'.$baseinfo['extension'];
                waterMark($imageFiles[count($imageFiles)-1],ROOT_PATH . $report['qrcode_path'],$saveFile,array(1948,2870));
                @rename($saveFile,$imageFiles[count($imageFiles)-1]);
            }
            */
            //再转换成pdf
            //$pdf = './Public/attached/report/'.$centreno.'.pdf';
            //convertImageToPdf(ROOT_PATH,substr($pdf,1),$imageFiles);
            $imgFiles = $imageFiles;
            //对外签加公章
            if (file_exists($imageFiles[0]) && file_exists($imageFiles[1])) {
                $baseinfo = pathinfo($imageFiles[0]);
                if ($tpl['subtype'] == 2) {
                    //小中心
                    $tmpSavefile = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-mark.' . $baseinfo['extension'];
                    waterMark($imageFiles[0], './Public/static/images/sealB.png', $tmpSavefile, array(1050, 2650));
                    //左上角章
                    $tmpSavefile2 = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-sign.' . $baseinfo['extension'];
                    waterMark($tmpSavefile, './Public/static/images/sign.png', $tmpSavefile2, array(350, 60));
                    //带mark的pdf
                    @rename($tmpSavefile2, $imageFiles[0]);
                    $imgFiles[] = $tmpSavefile;
                    //图二带章
                    $baseinfo = pathinfo($imageFiles[1]);
                    $tmpSavefile = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-mark2.' . $baseinfo['extension'];
                    waterMark($imageFiles[1], './Public/static/images/sealB.png', $tmpSavefile, array(1600, 2380));
                } else {
                    //大中心
                    $tmpSavefile = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-mark.' . $baseinfo['extension'];
                    waterMark($imageFiles[0], './Public/static/images/sealB.png', $tmpSavefile, array(700, 2700));
                    //第二个公章
                    $tmpSavefile2 = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-mark2.' . $baseinfo['extension'];
                    waterMark($tmpSavefile, './Public/static/images/sealA.png', $tmpSavefile2, array(1300, 2700));
                    //左上角章
                    $tmpSavefile3 = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-sign.' . $baseinfo['extension'];
                    waterMark($tmpSavefile2, './Public/static/images/sign.png', $tmpSavefile3, array(350, 60));
                    //带mark的pdf
                    @rename($tmpSavefile3, $imageFiles[0]);
                    $imgFiles[] = $tmpSavefile2;
                    //图二带章
                    $baseinfo = pathinfo($imageFiles[1]);
                    $tmpSavefile = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-mark2.' . $baseinfo['extension'];
                    waterMark($imageFiles[1], './Public/static/images/sealA.png', $tmpSavefile, array(1600, 2400));
                }
                //左上角章
                $tmpSavefile2 = $baseinfo['dirname'] . '/' . $baseinfo['filename'] . '-sign2.' . $baseinfo['extension'];
                waterMark($tmpSavefile, './Public/static/images/sign.png', $tmpSavefile2, array(350, 0));
                @rename($tmpSavefile2, $imageFiles[1]);
//加水印
                //再转换成pdf
                $signPdf = './Public/attached/report_temp/' . $centreno . '-sign.pdf';
                convertImageToPdf(ROOT_PATH, substr($signPdf, 1), $imageFiles, 1024, './Public/static/images/wmfull.png');
                //获取临时转换图片地址
                $temp= implode("^",$imgFiles);
                $temp1 = str_replace(".jpg","-thumb.jpg",$temp);
                $temp2 = str_replace(".jpg","-wmthumb.jpg",$temp);
                $arr1 =explode("^",$temp1);
                $arr2 =explode("^",$temp2);
                foreach ($arr1 as $v1){
                    $imgFiles[] = $v1;
                }
                foreach ($arr2 as $v2){
                    $imgFiles[] = $v2;
                }
                $imgFiles[] = $tmpSavefile;
                $imgFiles[] = $tmpSavefile2;
            }
            $data['pdf_path'] = $data['path'];
            $data['pdf_sign_path'] = substr($signPdf, 1);
            if (D("test_report_temp")->where("centreno='{$centreno}'")->save($data)) {
                if ($imgFiles) {
                    foreach ($imgFiles as $value) {
                        if (file_exists($value)) {
                            @unlink($value);
                        }
                    }
                }
                $result['msg'] = "succ";

            }
        } else {
            $result['msg'] = "转换pdf失败";
        }
        /*
        $res = json_decode($res,true);
        if($res['retMsg']=='success'){
            $outputURLs = $res['outputURLs'];
            $pdfUrl = $outputURLs[0];
            $data['pdf_path'] = $pdfUrl;
        }else{
            $result['msg'] = "转换pdf失败";
            $this->ajaxReturn($result);
        }
        */
        $this->ajaxReturn($result);
    }

    //提交审核按钮
    public function doUpd()
    {
        $centreno = I("centreno");
        $rs = array("msg" => "fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid = $admin_auth['id'];
        $where1 = "centreno = '{$centreno}' and type = 3";
        $data1 = array(
            'status' => 2,
            'bz_back' => 0,
            'back_time' => null,
            'uploadreport_user_id' => $userid,
            'uploadreport_time' => date("Y-m-d H:i:s"),
        );
        M()->startTrans();
        if (D("contract_flow")->where("centreno='{$centreno}'")->save($data1)) {
            //检查退回原因框是否有编制退回的记录，有的话就删除
            D('back_report')->where($where1)->delete();
            $rs['msg'] = "succ";
            M()->commit();
        } else {
            M()->rollback();
        }
        $this->ajaxReturn($rs);
    }

    //退回后修改完毕提交
    public function doUpd1()
    {
        $centreno = I("centreno");
        $sortby = I('sortby');
        $rs = array("msg" => "fail");
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $userid = $admin_auth['id'];
        $data1 = array(
            'status' => $sortby,
            'ifback' => 0,
            'uploadreport_user_id' => $userid,
            'uploadreport_time' => date("Y-m-d H:i:s"),
        );
        M()->startTrans();
        if (D("contract_flow")->where("centreno='{$centreno}'")->save($data1)) {
            $rs['msg'] = "操作成功！";
            M()->commit();
        } else {
            M()->rollback();
        }
        $this->ajaxReturn($rs);
    }

    //删除（不用改）
    public function doDeleteReport()
    {
        $id = I("id", 0, 'intval');
        $rs = array("msg" => "fail");
        if (M("test_report")->where("id=" . $id)->delete()) {
            $rs['msg'] = 'succ';
        }
        $this->ajaxReturn($rs);
    }

//申请修改下载模板、二维码、上传报告临时页面
    public function reportTemp()
    {
        $centreno = I('id');
        $data = D('test_report_temp')->where("centreNo = '$centreno'")->find();
        $this->assign($data);
        $this->display();
    }

    public function checkNotify()
    {
        //-1合同作废，0合同录入完毕，未接单状态,1生成报告完毕,待上传检测报告制表，
        //2已生成检测报告,待提交审核，3盖章退回，-3审核未通过，4已批准，待内部签发，
        //-4批准未通过,5内部已经签发，待外部签发,6外部签发7已接单8检测完毕,待生成报告
        $admin_auth = session("admin_auth");//获取当前登录用户信息
        $if_admin = $admin_auth['super_admin'];//是否是超级管理员
        $user = $admin_auth['gid'];//判断是哪个角色
        $department = $admin_auth['department'];

        if ($if_admin) {
            $where_bgwbqf = "status='5'";//报告外部签发
        } else {
            $where_bgwbqf = "status='5'";//报告外部签发
            if ($department == 'G1') {
                $where_bgwbqf .= " and SUBSTR(contract_flow.centreno,7,1) = 'G' and SUBSTR(contract_flow.centreno,9,3) <='500'";
            } elseif ($department == 'G2') {
                $where_bgwbqf .= " and SUBSTR(contract_flow.centreno,7,1) = 'G' and SUBSTR(contract_flow.centreno,9,3) >'500'";
            } else {
                $where_bgwbqf .= " and SUBSTR(contract_flow.centreNo,7,1) = '{$department}'";
            }
        }
        if ($user == 7 || $if_admin) {
            $num_bgwbqf = D("contract_flow")->where($where_bgwbqf)->count();
            $flag = 1;
        }
        else {
            $num_bgwbqf = 0;
        }


        //盖章审核
        if ($if_admin || $user == 15) {
            $where_zwqf = "status='4'";//暂未签发
            $num_zwqf = D("contract_flow")->where($where_zwqf)->count();

            $where_nbqf = "status='5'";//内部签发
            $num_nbqf = D("contract_flow")->where($where_nbqf)->count();

        } else {
            $num_zwqf = 0;
            $num_nbqf = 0;
        }

        //检测记录
        if ($if_admin) {
            $where_jcjl = "(status=0 or (status=7 and bz_back=1) or (status=7 and sh_back=1) or (status=7 and gz_back=1))";//一：0合同录入完毕，未接单状态,要通知实验员接单  二：(status='7' and bz_back=1)状态7已接单并且是编制退回的情况下也要通知实验员来处理,还有报告审核退回，盖章审核退回
        } else {
            $where_jcjl = "(status=0 or (status=7 and bz_back=1) or (status=7 and sh_back=1) or (status=7 and gz_back=1))";
            if ($department == 'G1') {
                $where_jcjl .= " and SUBSTR(contract_flow.centreno,7,1) = 'G' and SUBSTR(contract_flow.centreno,9,3) <='500'";
            } elseif ($department == 'G2') {
                $where_jcjl .= " and SUBSTR(contract_flow.centreno,7,1) = 'G' and SUBSTR(contract_flow.centreno,9,3) >'500'";
            } else {
                $where_jcjl .= " and SUBSTR(contract_flow.centreNo,7,1) = '{$department}'";
            }
        }
        if ($user == 9 || $if_admin) {
            $num_jcjl = D("contract_flow")->where($where_jcjl)->count();
        } else {
            $num_jcjl = 0;
        }

        //报告审核
        if ($if_admin || $user == 13) {
            $useraudit= $admin_auth['audit'];
            $if_G1 = 0;
            $if_G2 = 0;//能审核的部门是否包含G1\G2，默认没有

            $where = "status='2'";
            if(!empty($useraudit)){
                //先检查是否有G1\G2
                if(strstr($useraudit,'G1')){
                    $if_G1 = 1;
                    $useraudit = str_replace('1','',$useraudit);
                }
                if(strstr($useraudit,'G2')){
                    $useraudit = str_replace('2','',$useraudit);
                    $if_G2 = 1;
                }
                $data=explode(',',$useraudit);
                foreach($data as $v){
                    $s .="'".$v."',";
                }
                $s=substr($s,0,-1);//利用字符串截取函数消除最后一个逗号
                if ($if_G1 == 0 and $if_G2 == 1){//只选了G2
                    $where .= " and ((SUBSTR(contract_flow.centreno,7,1) in({$s}) and SUBSTR(contract_flow.centreno,7,1) !='G') or (SUBSTR(contract_flow.centreno,7,1) ='G' and SUBSTR(contract_flow.centreno,9,3) > 500))";
                }
                elseif ($if_G1 == 1 and $if_G2 == 0){
                    $where .=" and ((SUBSTR(contract_flow.centreno,7,1) in({$s}) and SUBSTR(contract_flow.centreno,7,1) !='G') or (SUBSTR(contract_flow.centreno,7,1) ='G' and SUBSTR(contract_flow.centreno,9,3) <= 500))";
                }
                else{
                    $where .=" and SUBSTR(contract_flow.centreno,7,1) in({$s})";
                }

            }

            $num_bgsh = D("contract_flow")->where($where)->count();
        } else {
            $num_bgsh = 0;
        }


        //前台内部修改申请
        if ($if_admin || $user == 17) {//单项修改的
            $where_qtnbxgsq_one = "status=0 and if_outer=0 and if_report=0  and if_invalid=0 and role_id=17";
            $num_qtnbxgsq_one = D("report_feedback")->where($where_qtnbxgsq_one)->count();
        } else {
            $num_qtnbxgsq_one = 0;
        }

        if ($if_admin || $user == 16) {//多项修改的
            $where_qtnbxgsq_more = "status=0 and if_outer=0 and if_report=0  and if_invalid=0 and role_id=16";
            $num_qtnbxgsq_more = D("report_feedback")->where($where_qtnbxgsq_more)->count();
        } else {
            $num_qtnbxgsq_more = 0;
        }


        //报告修改申请
        if ($user == 16 || $if_admin || $user == 17) {
            $where_bgxgsq = "status=0 and if_report=1";
            $num_bgxgsq = D("report_feedback")->where($where_bgxgsq)->count();
        } else {
            $num_bgxgsq = 0;
        }

        //外部修改申请
        if ($user == 16 || $if_admin) {
            $where_wbxgsq = "status=0 and if_outer=1";
            $num_wbxgsq = D("report_feedback")->where($where_wbxgsq)->count();
        } else {
            $num_wbxgsq = 0;
        }

        //编号作废申请
        if ($user == 16 || $if_admin) {
            $where_bhzfsq = "status=0 and if_invalid=1";
            $num_bhzfsq = D("report_feedback")->where($where_bhzfsq)->count();
        } else {
            $num_bhzfsq = 0;
        }

        if ($user == 16) {
            $num_qtnbxgsq = $num_qtnbxgsq_more;
            $num_xggl = $num_qtnbxgsq + $num_bgxgsq + $num_wbxgsq + $num_bhzfsq;
        } elseif ($user == 17) {
            $num_qtnbxgsq = $num_qtnbxgsq_one;
            $num_xggl = $num_qtnbxgsq + $num_bgxgsq + $num_wbxgsq + $num_bhzfsq;
        }

        $test = array(
            "content" => "true",
            "number_jcjl" => $num_jcjl,
            "name_jcjl" => "检测记录",
            "name_jygl" => "检验管理",
            "id_jygl" => "menu_id_105",
            "id_jcjl" => "son_id_132",
            "num_xggl" => $num_xggl,
            "name_xggl" => "修改管理",
            "name_bgxgsq" => "报告修改申请",
            "id_xggl" => "menu_id_111",
            "id_bgxgsq" => "son_id_142",
            "num_bgsh" => $num_bgsh,
            "name_bgsh" => "报告审核",

            "id_bgsh" => "son_id_136",
            "name_qtgl" => "前台管理",
            "id_qtgl" => "menu_id_101",
            "num_bgwbqf" => $num_bgwbqf,
            "name_bgwbqf" => "报告外部签发",
            "flag"=>$flag,

            "id_bgwbqf" => "son_id_139",

            'num_zwqf' => $num_zwqf,
            'num_nbqf' => $num_nbqf,
            'num_qtnbxgsq_one' => $num_qtnbxgsq_one,
            'num_qtnbxgsq_more' => $num_qtnbxgsq_more,
            'num_qtnbxgsq' => $num_qtnbxgsq,
            'num_bgxgsq' => $num_bgxgsq,
            'num_wbxgsq' => $num_wbxgsq,
            'num_bhzfsq' => $num_bhzfsq,

        );
        $this->ajaxReturn($test);
    }
}