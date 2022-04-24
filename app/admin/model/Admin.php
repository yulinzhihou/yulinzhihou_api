<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\facade\Log;

/**
 * 后台管理员模型
 */
class Admin extends Base
{
    /**
     * 获取用户
     * @param array $field
     * @param array $data
     * @return array
     */
    public function getUserInfo(array $data,array $field = []) : array
    {
        if (empty($field)) {
            $this->field = [];
        } else {
            $this->field = $field;
        }
        try {
            $result = $this->field($this->field)->where($data)->find();
            return $result ? $result->toArray() : [];
        } catch (\Exception $e) {
            Log::sql($e->getMessage(),$e->getTrace());
            return [];
        }
    }

    /**
     * 根据当前角色ID获取菜单权限
     * @param array $data
     * @return array
     */
    public function getMenuList(array $data): array
    {
        return Menu::column('name','id');
    }

    /**
     * 获取角色列表
     * @return array
     */
    public function getRoleList():array
    {
        return Role::column('name','id');
    }


    /**
     * 根据当前用户角色id获取角色对应的权限
     * @param array $data
     * @return array
     */
    public function getRoleAuth(array $data):array
    {
        return (new Menu())->routersByTree($data['user_info']->role_id);
    }


}
