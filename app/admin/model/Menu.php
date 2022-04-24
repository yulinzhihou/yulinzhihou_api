<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\facade\Log;
use think\helper\Str;

/**
 * 菜单模型
 */
class Menu extends Base
{
    protected $schema = [
        'id'	                =>	'int',
        'pid'	                =>	'int',
        'name'	                =>	'string',
        'path'	                =>	'string',
        'component'	            =>	'string',
        'title'	                =>	'string',
        'affix'	                =>	'int',
        'icon'	                =>	'string',
        'redirect'	            =>	'string',
        'permission'            =>	'string',
        'real_path'	            =>	'string',
        'frame_src'	            =>	'string',
        'transition_name'	    =>	'string',
        'current_active_menu'	=>	'int',
        'type'	                =>	'int',
        'hidden_menu'	        =>	'int',
        'hidden_children_in_menu'=>	'int',
        'hide_breadcrumb'	    =>	'int',
        'hidden_tab'	        =>	'int',
        'ignore_keep_alive'	    =>	'int',
        'ignore_route'	        =>	'int',
        'carry_param'	        =>	'int',
        'show_menu'	            =>	'int',
        'no_cache'	            =>	'int',
        'sort'	                =>	'int',
        'status'	            =>	'int',
        'create_time'	        =>	'int',
        'update_time'	        =>	'int'
    ];

    protected $type = [
        'hidden_menu'               => 'boolean',
        'hidden_children_in_menu'   => 'boolean',
        'hide_breadcrumb'           => 'boolean',
        'hidden_tab'                => 'boolean',
        'ignore_keep_alive'         => 'boolean',
        'ignore_route'              => 'boolean',
        'carry_param'               => 'boolean',
        'show_menu'                 => 'boolean',
        'no_cache'                  => 'boolean',
        'status'                    => 'boolean'
    ];

    /**
     * 通过角色ID获取菜单权限
     * @param $roleId
     * @return array
     */
    public function routersByTree($roleId):array
    {
        try {
            $role = (new Role())->getInfo($roleId);
            $where[] = $role['menu'] == '*' ? true : ['id', 'in', explode(',', $role['menu'])];
            $order = ['sort' => 'asc'];  // 按排序序号由大到小排序（0-99）
            $result = $this->where($where)->order($order)->select()->toArray();
            $boolField = ['hidden_menu','hidden_children_in_menu','hide_breadcrumb','hidden_tab','ignore_keep_alive','ignore_route','carry_param','show_menu','no_cache','status'];
            $newData = [];
            if (!empty($result)) {
                foreach ($result as $key => $item) {
                    $newData[$key] = $item;
                    $newData[$key]['meta'] = [
                        'icon'              => $item['icon'],
                        'title'             => $item['title'],
                        'hideMenu'          => !($item['hidden_menu'] == 0),
                        'ignoreKeepAlive'   => !($item['ignore_keep_alive'] == 0),
                        'showMenu'          => !($item['show_menu'] == 0),
                        'currentActiveMenu' => $item['current_active_menu']
                    ];
                    foreach ($item as $k1 => $v1) {
                        if (in_array($k1,$boolField)) {
                            $newData[$key][Str::camel($k1)] = !($v1 == 0);
                        } else {
                            $newData[$key][Str::camel($k1)] = $v1;
                        }
                    }
                }
            }
            return tree($newData);
        } catch (\Exception $e) {
            Log::sql($e->getMessage(),$e->getTrace());
            return [];
        }
    }

}
