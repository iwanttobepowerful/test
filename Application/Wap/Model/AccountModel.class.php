<?php
namespace Wap\Model;
use Think\Model;
class AccountModel extends Model{
    protected $tableName = 'common_system_user';
    public function checkLogin(){
        $admin_auth = session("admin_auth");
        if($admin_auth){
            return $admin_auth;
        }
        $loginUrl = U('/account/login');
        @header("Location:{$loginUrl}");
    }
    public function isLogin(){
        $admin_auth = session("admin_auth");
        if($admin_auth){
            return $admin_auth;
        }else{
            return false;
        }
    }
    public function verifyPermission($action="",$func=""){
        $admin_auth = session("admin_auth");
        if($admin_auth['super_admin']){
            return true;
        }else{
             $action = strtolower($action);
            $func = strtolower($func);
            if($admin_auth['perm'] && $action && $func){
                if($admin_auth['perm'][$action] && in_array($func, $admin_auth['perm'][$action])){
                    return true;
                }
            }
        }
        return false;
    }
}
?>
