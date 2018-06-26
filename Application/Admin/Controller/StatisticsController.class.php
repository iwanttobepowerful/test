<?php
namespace Admin\Controller;
use Think\Controller;
class StatisticsController extends Controller {
    public $user = array();
    //初始化方法
    public function _initialize(){
        load('@.functions');
        $this->user = D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }
    public function objActSheetSetCellValues($objActSheet)
    {
        $this->setValues($objActSheet);//填充数据

    }

    public function makeExcel1()
    {
        // 导出Exl
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel2007");
        $objPHPExcel = new \PHPExcel();

        for ($i = 1; $i <= 10; $i++) {
            if ($i > 1) {
                $objPHPExcel->createSheet();
            }
        }

        $begin_time = I("begin_time");
        $end_time = I("end_time");

        //A科室
        $this->fillDepartment($begin_time, $end_time, $objPHPExcel, 0);

        //B科室
        $this->fillDepartment($begin_time, $end_time, $objPHPExcel, 1);

        //C科室
        $this->fillDepartment($begin_time, $end_time, $objPHPExcel, 2);

        //D科室
        $this->fillDepartment($begin_time, $end_time, $objPHPExcel, 3);

        //E科室
        $this->fillDepartment($begin_time, $end_time, $objPHPExcel, 4);

        //F科室
        $this->fillDepartment($begin_time, $end_time, $objPHPExcel, 5);

        //G1科室
        $this->fillDepartmentG1($begin_time, $end_time, $objPHPExcel);

        //G2科室
        $this->fillDepartmentG2($begin_time, $end_time, $objPHPExcel);

        //H科室
        $this->fillDepartmentH($begin_time, $end_time, $objPHPExcel);


        //作废编号汇总
        $where = "1=1";
        $orderby = "b.collectdate desc";
        $begin_time && $where .= " and date_format(b.collectdate,'%Y-%m-%d') >='{$begin_time}'";
        $end_time && $where .= " and date_format(b.collectdate,'%Y-%m-%d') <='{$end_time}'";
        $objPHPExcel->setActiveSheetIndex(9);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $mytitle = '';
        $mytitle .= '作废编号';
        $objActSheet->setTitle($mytitle);
        $objActSheet->setCellValue("A1", "序号")
            ->setCellValue("B1", "中心编号")
            ->setCellValue("C1", "作废原因");
        $j = 2;
        $rs = D("contract_flow")->alias("c")
            ->field('c.status,c.centreno,r.reason')
            ->join('left join report_feedback as r on r.centreNo=c.centreNo')->where("c.status=-1 and r.status=1")->select();
        foreach ($rs as $key => $value) {
            $rs[$key] = $value;
            $objActSheet->setCellValue("A" . $j, $j - 1)->setCellValue("B" . $j, $value['centreno'])->setCellValue("C" . $j, $value['reason']);
            $j++;
        }


        $fileName = '流水登记表';
        $fileName .= "($begin_time,$end_time).xls";
        $fileName = iconv("utf-8", "gb2312", $fileName);

        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
    }

    public function base(){
        $centreno = I("centreno");
        $centreno = trim($centreno);//去空格查询
        $begin_time = I("begin_time");
        $end_time = I("end_time");
        $de = I("de",'A');
        $searchby=I("searchby");
        $page = I("p",'int');
        $pagesize = 10;
        if($page<=0) $page = 1;
        $offset = ( $page-1 ) * $pagesize;

        if($de=='B'){
            $where = " a.status in(5,6)";
            $orderby = "a.inner_sign_time desc";
            $begin_time && $where .=" and date_format(a.inner_sign_time,'%Y-%m-%d') >='{$begin_time}'";
            $end_time && $where .=" and date_format(a.inner_sign_time,'%Y-%m-%d') <='{$end_time}'";
            if(!empty($centreno)){
                //查询合同编号
                $where .=" and a.centreno like '%{$centreno}%'";
            }

            if($searchby==1)
            {
                $where .=" and SUBSTR(a.centreNo,7,1) = 'A'";
            }
            elseif ($searchby==2){
                $where .=" and SUBSTR(a.centreNo,7,1) = 'B'";
            }
            elseif ($searchby==3){
                $where .=" and SUBSTR(a.centreNo,7,1) = 'C'";
            }
            elseif ($searchby==4){
                $where .=" and SUBSTR(a.centreNo,7,1) = 'D'";
            }
            elseif ($searchby==5){
                $where .=" and SUBSTR(a.centreNo,7,1) = 'E'";
            }
            elseif ($searchby==6){
                $where .=" and SUBSTR(a.centreNo,7,1) = 'F'";
            }
        $sumlist = D("contract_flow")->alias("a")->join(C("DB_PREFIX")."contract b on a.centreno=b.centreno","LEFT")->join(C("DB_PREFIX")."test_cost c on a.centreno=c.centreno","LEFT")->where($where)->field("sum(b.testcost) as testcost,sum(c.rarecord) as arecord,sum(c.rbrecord) as brecord,sum(c.rcrecord) as crecord,sum(c.rdrecord) as drecord,sum(c.rerecord) as erecord,sum(c.rfrecord) as frecord,sum(c.rg1record) as g1record,sum(c.rg2record) as g2record,sum(c.rhrecord) as hrecord,sum(c.dcopy) as dcopy,sum(c.drevise) as drevise,sum(c.dother) as dother,sum(c.donline) as donline")->find();
        $list = D("contract_flow")->alias("a")->join(C("DB_PREFIX")."contract b on a.centreno=b.centreno","LEFT")->join(C("DB_PREFIX")."test_cost c on a.centreno=c.centreno","LEFT")->where($where)->order($orderby)
            ->field("a.id,a.status,a.external_sign_time,a.inner_sign_time,a.centreno,a.takelist_user_id,b.clientname,b.productunit,b.samplename,b.testcriteria,b.testitem,b.testcost,b.remark,b.testcriteria,b.collectdate,b.samplequantity,b.collector,b.centreno1,b.centreno2,b.centreno3,c.rarecord,c.rbrecord,c.rcrecord,c.rdrecord,c.rerecord,c.rfrecord,c.rg1record,c.rg2record,c.rhrecord,c.dcopy,c.donline,c.drevise,c.dother")->limit("{$offset},{$pagesize}")->select();
        //dump($where);
        if($list){
        	$centrenoIds = array();
            $userIds = array();
        	foreach ($list as $value) {
        		$centrenoIds[] = "'".$value['centreno']."'";
                $value['takelist_user_id'] && $userIds[] = $value['takelist_user_id'];
        	}
        	$userIds && $user = D("common_system_user")->where("id in(".implode(',', $userIds).")")->field("id,name")->select();
            $user && $user = assColumn($user);
        	foreach ($list as $key => $value) {
                $value['takelist_user'] = $value['takelist_user_id'] ? $user[$value['takelist_user_id']]['name']:"";
                $list[$key] = $value;
            }
        }
        $count = D("contract_flow")->alias("a")->join(C("DB_PREFIX")."contract b on a.centreno=b.centreno","LEFT")->join(C("DB_PREFIX")."test_cost c on a.centreno=c.centreno","LEFT")->where($where)->field("count(*) as total")->select();
        }
        elseif ($de=='A'){
            //来样日期
            $where="1=1";
            $orderby = "b.collectdate desc";
            $begin_time && $where .=" and date_format(b.collectdate,'%Y-%m-%d') >='{$begin_time}'";
            $end_time && $where .=" and date_format(b.collectdate,'%Y-%m-%d') <='{$end_time}'";
            if(!empty($centreno)){
                //查询合同编号
                $where .=" and b.centreno like '%{$centreno}%'";
            }
            if($searchby==1)
            {
                $where .=" and SUBSTR(b.centreNo,7,1) = 'A'";
            }
            elseif ($searchby==2){
                $where .=" and SUBSTR(b.centreNo,7,1) = 'B'";
            }
            elseif ($searchby==3){
                $where .=" and SUBSTR(b.centreNo,7,1) = 'C'";
            }
            elseif ($searchby==4){
                $where .=" and SUBSTR(b.centreNo,7,1) = 'D'";
            }
            elseif ($searchby==5){
                $where .=" and SUBSTR(b.centreNo,7,1) = 'E'";
            }
            elseif ($searchby==6){
                $where .=" and SUBSTR(b.centreNo,7,1) = 'F'";
            }
            $sumlist = D("contract")->alias("b")->join(C("DB_PREFIX")."test_cost c on b.centreno=c.centreno","LEFT")->where($where)->field("sum(b.testcost) as testcost,sum(c.rarecord) as arecord,sum(c.rbrecord) as brecord,sum(c.rcrecord) as crecord,sum(c.rdrecord) as drecord,sum(c.rerecord) as erecord,sum(c.rfrecord) as frecord,sum(c.rg1record) as g1record,sum(c.rg2record) as g2record,sum(c.rhrecord) as hrecord,sum(c.dcopy) as dcopy,sum(c.drevise) as drevise,sum(c.dother) as dother,sum(c.donline) as donline")->find();
            $list = D("contract")->alias("b")->join(C("DB_PREFIX")."contract_flow a on b.centreno=a.centreno","LEFT")->join(C("DB_PREFIX")."test_cost c on b.centreno=c.centreno","LEFT")->where($where)->order($orderby)
                ->field("a.id,a.status,a.external_sign_time,a.inner_sign_time,a.takelist_user_id,b.centreno,b.clientname,b.productunit,b.samplename,b.testcriteria,b.testitem,b.testcost,b.remark,b.testcriteria,b.collectdate,b.samplequantity,b.collector,b.centreno1,b.centreno2,b.centreno3,c.rarecord,c.rbrecord,c.rcrecord,c.rdrecord,c.rerecord,c.rfrecord,c.rg1record,c.rg2record,c.rhrecord,c.dcopy,c.donline,c.drevise,c.dother")->limit("{$offset},{$pagesize}")->select();
            //dump($where);
            if($list){
                $centrenoIds = array();
                $userIds = array();
                foreach ($list as $value) {
                    $centrenoIds[] = "'".$value['centreno']."'";
                    $value['takelist_user_id'] && $userIds[] = $value['takelist_user_id'];
                }
                $userIds && $user = D("common_system_user")->where("id in(".implode(',', $userIds).")")->field("id,name")->select();
                $user && $user = assColumn($user);
                foreach ($list as $key => $value) {
                    $value['takelist_user'] = $value['takelist_user_id'] ? $user[$value['takelist_user_id']]['name']:"";
                    $list[$key] = $value;
                }
            }
            $count = D("contract")->alias("b")->join(C("DB_PREFIX")."contract_flow a on b.centreno=a.centreno","LEFT")->join(C("DB_PREFIX")."test_cost c on b.centreno=c.centreno","LEFT")->where($where)->field("count(*) as total")->select();
        }
        $Page= new \Think\Page(intval($count[0]['total']),$pagesize);
        $Page->setConfig('theme',"<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $pagination= $Page->show();// 分页显示输出

        $body = array(
        	 'pagination'=>$pagination,
        	'lists'=>$list,
            'sum'=>$sumlist,
            'centreno'=>$centreno,
            'begin_time'=>$begin_time,
            'end_time'=>$end_time,
            'de'=>$de,
            'searchby'=>$searchby,
        );
        $this->assign($body);
        $this->display();
    }
    /**
     * @param $objActSheet
     */
    public function setValues($objActSheet)
    {
        $objActSheet->setCellValue("A1", "盖章日期")->setCellValue("B1", "中心编号")->setCellValue("C1", "委托单位")
            ->setCellValue("D1", "生产单位")->setCellValue("E1", "样品名称")->setCellValue("F1", "规格型号/等级")
            ->setCellValue("G1", "检验内容及要求（含燃烧级别）")->setCellValue("H1", "实收金额")
            ->setCellValue("I1", "A记录")->setCellValue("J1", "B记录")
            ->setCellValue("K1", "C记录")->setCellValue("L1", "D记录")
            ->setCellValue("M1", "E记录")->setCellValue("N1", "F记录")
            ->setCellValue("O1", "G1记录")->setCellValue("P1", "G2记录")
            ->setCellValue("Q1", "H记录")->setCellValue("R1", "副本")
            ->setCellValue("S1", "基础费")->setCellValue("T1", "检验依据")
            ->setCellValue("U1", "来样日期")->setCellValue("V1", "报告日期")
            ->setCellValue("W1", "样品数量")->setCellValue("X1", "实验员")
            ->setCellValue("Y1", "收样人")->setCellValue("Z1", "客户姓名")
            ->setCellValue("AA1", "客户联系电话")->setCellValue("AB1", "客户地址")
            ->setCellValue("AC1", "邮编")->setCellValue("AD1", "传真")->setCellValue("AE1", "E-mail");//填充数据
    }

    /**
     * @param $objActSheet
     * @param $j
     * @param $value
     */
    private function setCellValues($objActSheet, $j, $value)
    {
        $objActSheet->setCellValue("A" . $j, $value['inner_sign_time'])->setCellValue("B" . $j, $value['centreno'])->setCellValue("C" . $j, $value['clientname'])->setCellValue("D" . $j, $value['productunit'])
            ->setCellValue("E" . $j, $value['samplename'])->setCellValue("F" . $j, $value['specification'])->setCellValue("G" . $j, $value['testitem'])->setCellValue("H" . $j, $value['testcost'])
            ->setCellValue("I" . $j, $value['rarecord'])->setCellValue("J" . $j, $value['rbrecord'])->setCellValue("K" . $j, $value['rcrecord'])->setCellValue("L" . $j, $value['rdrecord'])
            ->setCellValue("M" . $j, $value['rerecord'])->setCellValue("N" . $j, $value['rfrecord'])->setCellValue("O" . $j, $value['rg1record'])->setCellValue("P" . $j, $value['rg2record'])
            ->setCellValue("Q" . $j, $value['rhrecord'])->setCellValue("R" . $j, $value['dcopy'])->setCellValue("S" . $j, $value['donline'])->setCellValue("T" . $j, $value['testcriteria'])
            ->setCellValue("U" . $j, $value['collectdate'])->setCellValue("V" . $j, $value['reportdate'])->setCellValue("W" . $j, $value['samplequantity'])->setCellValue("X" . $j, $value['takelist_user'])//实验员也就是接单人
            ->setCellValue("Y" . $j, $value['collector'])->setCellValue("Z" . $j, $value['clientsign'])->setCellValue("AA" . $j, $value['telephone'])
            ->setCellValue("AB" . $j, $value['address'])->setCellValue("AC" . $j, $value['postcode'])->setCellValue("AD" . $j, $value['tax'])->setCellValue("AE" . $j, $value['email']);
    }

    /**
     * @param $lists
     * @param $objActSheet
     * @param $j
     * @return array
     */
    private function fillData($lists, $objActSheet, $j)
    {
        if ($lists) {
            $centrenoIds = array();
            $userIds = array();
            foreach ($lists as $value) {
                $centrenoIds[] = "'" . $value['centreno'] . "'";
                $value['takelist_user_id'] && $userIds[] = $value['takelist_user_id'];
            }
            $userIds && $user = D("common_system_user")->where("id in(" . implode(',', $userIds) . ")")->field("id,name")->select();
            $user && $user = assColumn($user);
            foreach ($lists as $key => $value) {
                $value['takelist_user'] = $value['takelist_user_id'] ? $user[$value['takelist_user_id']]['name'] : "";
                $lists[$key] = $value;
                $this->setCellValues($objActSheet, $j, $value);
                $j++;
            }

        }

    }


    private function fillDepartment($begin_time, $end_time, $objPHPExcel, $ch)   //$ch是数字
    {
        $where = " a.status in(5,6)";
        $orderby = "a.inner_sign_time desc";
        $begin_time && $where .= " and date_format(a.inner_sign_time,'%Y-%m-%d') >='{$begin_time}'";
        $end_time && $where .= " and date_format(a.inner_sign_time,'%Y-%m-%d') <='{$end_time}'";

        $objPHPExcel->setActiveSheetIndex($ch);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $mytitle = '';

        $ch = chr($ch + 65);//转换到字母
        $mytitle .= $ch . '科室';
        $objActSheet->setTitle($mytitle);
        $where .= " and SUBSTR(a.centreNo,7,1) = '$ch'";
        //$sumlistA = D("contract_flow")->alias("a")->join(C("DB_PREFIX") . "contract b on a.centreno=b.centreno", "LEFT")->join(C("DB_PREFIX") . "test_cost c on a.centreno=c.centreno", "LEFT")->where($where)->field("sum(b.testcost) as testcost,sum(c.rarecord) as arecord,sum(c.rbrecord) as brecord,sum(c.rcrecord) as crecord,sum(c.rdrecord) as drecord,sum(c.rerecord) as erecord,sum(c.rfrecord) as frecord,sum(c.dcopy) as dcopy,sum(c.drevise) as drevise,sum(c.dother) as dother,sum(c.donline) as donline")->find();

        $listA = D("contract_flow")->alias("a")->join(C("DB_PREFIX") . "contract b on a.centreno=b.centreno", "LEFT")->join(C("DB_PREFIX") . "test_cost c on a.centreno=c.centreno", "LEFT")->where($where)->order($orderby)->select();
        $this->setValues($objActSheet);
        $j = 2;
        $this->fillData($listA, $objActSheet, $j);
        //return array($where, $orderby, $objActSheet, $j);
    }

    private function fillDepartmentG1($begin_time, $end_time, $objPHPExcel)
    {
        $where = "1=1";
        $orderby = "b.collectdate desc";
        $begin_time && $where .= " and date_format(b.collectdate,'%Y-%m-%d') >='{$begin_time}'";
        $end_time && $where .= " and date_format(b.collectdate,'%Y-%m-%d') <='{$end_time}'";
        $objPHPExcel->setActiveSheetIndex(6);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $mytitle = '';

        $mytitle .= 'G1科室';
        $objActSheet->setTitle($mytitle);
        $where .= " and SUBSTR(a.centreno,7,1) = 'G' and SUBSTR(a.centreno,9,11) <='500'";
        //$sumlistA = D("contract_flow")->alias("a")->join(C("DB_PREFIX") . "contract b on a.centreno=b.centreno", "LEFT")->join(C("DB_PREFIX") . "test_cost c on a.centreno=c.centreno", "LEFT")->where($where)->field("sum(b.testcost) as testcost,sum(c.rarecord) as arecord,sum(c.rbrecord) as brecord,sum(c.rcrecord) as crecord,sum(c.rdrecord) as drecord,sum(c.rerecord) as erecord,sum(c.rfrecord) as frecord,sum(c.rgrecord) as g1record,sum(c.dcopy) as dcopy,sum(c.drevise) as drevise,sum(c.dother) as dother,sum(c.donline) as donline")->find();

        $listA = D("contract_flow")->alias("a")->join(C("DB_PREFIX") . "contract b on a.centreno=b.centreno", "LEFT")->join(C("DB_PREFIX") . "test_cost c on a.centreno=c.centreno", "LEFT")->where($where)->order($orderby)->select();
        $this->setValues($objActSheet);
        $j = 2;
        $this->fillData($listA, $objActSheet, $j);
        //return array($where, $orderby, $objActSheet, $j);
    }

    private function fillDepartmentG2($begin_time, $end_time, $objPHPExcel)
    {
        $where = "1=1";
        $orderby = "b.collectdate desc";
        $begin_time && $where .= " and date_format(b.collectdate,'%Y-%m-%d') >='{$begin_time}'";
        $end_time && $where .= " and date_format(b.collectdate,'%Y-%m-%d') <='{$end_time}'";
        $objPHPExcel->setActiveSheetIndex(7);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $mytitle = '';

        $mytitle .= 'G2科室';
        $objActSheet->setTitle($mytitle);
        $where .= " and SUBSTR(a.centreno,7,1) = 'G' and SUBSTR(a.centreno,9,11) >'500'";
        //$sumlistA = D("contract_flow")->alias("a")->join(C("DB_PREFIX") . "contract b on a.centreno=b.centreno", "LEFT")->join(C("DB_PREFIX") . "test_cost c on a.centreno=c.centreno", "LEFT")->where($where)->field("sum(b.testcost) as testcost,sum(c.rarecord) as arecord,sum(c.rbrecord) as brecord,sum(c.rcrecord) as crecord,sum(c.rdrecord) as drecord,sum(c.rerecord) as erecord,sum(c.rfrecord) as frecord,sum(c.dcopy) as dcopy,sum(c.drevise) as drevise,sum(c.dother) as dother,sum(c.donline) as donline")->find();

        $listA = D("contract_flow")->alias("a")->join(C("DB_PREFIX") . "contract b on a.centreno=b.centreno", "LEFT")->join(C("DB_PREFIX") . "test_cost c on a.centreno=c.centreno", "LEFT")->where($where)->order($orderby)->select();
        $this->setValues($objActSheet);
        $j = 2;
        $this->fillData($listA, $objActSheet, $j);
        //return array($where, $orderby, $objActSheet, $j);
    }

    private function fillDepartmentH($begin_time, $end_time, $objPHPExcel)
    {
        $where = "1=1";
        $orderby = "b.collectdate desc";
        $begin_time && $where .= " and date_format(b.collectdate,'%Y-%m-%d') >='{$begin_time}'";
        $end_time && $where .= " and date_format(b.collectdate,'%Y-%m-%d') <='{$end_time}'";
        $objPHPExcel->setActiveSheetIndex(8);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $mytitle = '';

        $mytitle .= 'H科室';
        $objActSheet->setTitle($mytitle);
        $where .= " and SUBSTR(a.centreNo,7,1) = 'H'";//！！！！！！！！！！！！！！！！！！！
        //$sumlistA = D("contract_flow")->alias("a")->join(C("DB_PREFIX") . "contract b on a.centreno=b.centreno", "LEFT")->join(C("DB_PREFIX") . "test_cost c on a.centreno=c.centreno", "LEFT")->where($where)->field("sum(b.testcost) as testcost,sum(c.rarecord) as arecord,sum(c.rbrecord) as brecord,sum(c.rcrecord) as crecord,sum(c.rdrecord) as drecord,sum(c.rerecord) as erecord,sum(c.rfrecord) as frecord,sum(c.dcopy) as dcopy,sum(c.drevise) as drevise,sum(c.dother) as dother,sum(c.donline) as donline")->find();

        $listA = D("contract_flow")->alias("a")->join(C("DB_PREFIX") . "contract b on a.centreno=b.centreno", "LEFT")->join(C("DB_PREFIX") . "test_cost c on a.centreno=c.centreno", "LEFT")->where($where)->order($orderby)->select();
        $this->setValues($objActSheet);
        $j = 2;
        $this->fillData($listA, $objActSheet, $j);
        //return array($where, $orderby, $objActSheet, $j);
    }
}