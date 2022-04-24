<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\Admin as AdminModel;
use app\admin\validate\Admin as AdminValidate;

/**
 * 后台管理员类
 */
class Admin extends Base
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new AdminModel();
        $this->validate = new AdminValidate();
    }

    /**
     * 新增用户拉取对应数据给前台
     */
    public function create():\think\Response\Json
    {
        // 获取菜单数据
        $menuList = $this->model->getMenuList($this->adminInfo);
        // 获取角色列表数据
        $roleList = $this->model->getRoleList();
        // 获取角色权限
        $roleAuth = $this->model->getRoleAuth($this->adminInfo);
        // 检查角色id
        return $this->jr('获取成功',[$menuList,$roleList,$roleAuth,1,[]]);
    }

    /**
     * 获取用户详情
     */
    public function userInfo():\think\Response\Json
    {
        $inputData = $this->request->param();
        if ($this->commonValidate(__FUNCTION__,$inputData)) {
            return $this->message(true);
        }
        $field = ['id','username','real_name','phone','email','avatar'];
        if (isset($this->adminInfo['admin_id']) && !empty($this->adminInfo['admin_id'])) {
            $result = $this->model->getUserInfo(['id' => $this->adminInfo['admin_id']],$field);
            if (!empty($result)) {
                $result['roles'] = [$this->adminInfo['role_key']];
                $result['realName'] = $result['real_name'];
                unset($result['real_name']);
            }
            return $this->jr(['获取成功','获取失败'],$result);
        }
        return $this->jr('用户信息已经过期，请重新登录');
    }
}