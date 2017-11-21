<?php
namespace Admin\Controller;
use Think\Controller;
class MainController extends Controller {
    //初始化方法
    public function _initialize(){
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active',strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active',strtolower(ACTION_NAME));
    }
    public function index(){
           //     mergeImage('./Public/attached/2017-11-21/page-1.jpg','./Public/qrcode/6027dff56bacb6a90c951d1520ad63ee.png','./Public/qrcode/222.png');



        //$files = convertPdf2Image('./Public/attached/2017-11-21/SJ-4-77_2017_01.pdf','./Public/attached/2017-11-21');
        //pr($files);
        
        $arr = array('./Public/attached/2017-11-21/page-1-tmp.jpg','./Public/attached/2017-11-21/page-1.jpg','./Public/attached/2017-11-21/page-2.jpg');
        $distpath = './Public/attached/2017-11-21/testcn_01.pdf';
        convertImageToPdf('./Public/attached/2017-11-21',$distpath,$arr);
       $this->display();
    }
}