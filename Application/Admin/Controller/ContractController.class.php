<?php
/**
 * Created by PhpStorm.
 * User: Ail
 * Date: 2017/10/14
 * Time: 16:49
 */
namespace Admin\Controller;
use Think\Controller;
class ContractController extends Controller
{
    public $user = null;

    public function _initialize()
    {
        load('@.functions');
        D("account")->checkLogin();
        $this->assign('menu_active', strtolower(CONTROLLER_NAME));
        $this->assign('menu_secoud_active', strtolower(ACTION_NAME));
    }

    public function getTestReport(){
        $this->display();
    }
    public function issueTestReport(){
        $this->display();
    }
}