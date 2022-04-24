<?php
declare (strict_types = 1);

namespace app\admin\model;

/**
 * 角色模型
 */
class Role extends Base
{
    protected $schema = [
        'id'	        =>	'int',
        'name'	        =>	'string',
        'value'	        =>	'string',
        'menu'	        =>	'string',
        'remark'	    =>	'string',
        'sort'	        =>	'int',
        'status'	    =>	'int',
        'create_time'	=>	'int',
        'update_time'	=>	'int'
    ];


    /**
     * 通过角色id 获取菜单
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenuByRoleId($id):array
    {
        if (isset($id) && $id > 0) {
            $data = [];
            if ($id == 1) {
                $data = Menu::select()->column('permission');
            } else {
                $temp = $this->where('id',$id)->select()->toArray();
                if (!empty($temp[0]) && $temp[0]['menu'] != '') {
                    $data = Menu::where(['id', 'in', explode(',', $temp[0]['menu'])])->select()->toArray();
                }
            }
            return $data;
        } else {
            return [];
        }
    }
}
