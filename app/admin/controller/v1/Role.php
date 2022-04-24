<?php
declare (strict_types = 1);

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\Role as RoleModel;
use app\admin\validate\Role as RoleValidate;

/**
 * 角色组控制器
 */
class Role extends Base
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new RoleModel();
        $this->validate = new RoleValidate();
    }

    /**
     * 获取角色菜单
     */
    public function getRoleMenu():\think\response\Json
    {
        $result = $this->model->getMenuByRoleId($this->adminInfo['user_info']->role_id);
        if (!empty($result)) {
            foreach ($result as $key => $item) {
                if ($item == '') {
                    unset($result[$key]);
                }
            }
            //构建返回数据结构
            $result = array_values(array_unique($result));
            return $this->jr('获取成功',$result);
        }
        //构建返回数据结构
        return $this->jr('获取失败');
    }

}
