<?php
declare (strict_types = 1);

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\Menu as MenuModel;
use app\admin\validate\Menu as MenuValidate;

/**
 * 菜单控制器
 */
class Menu extends Base
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new MenuModel();
        $this->validate = new MenuValidate();
    }

    /**
     * 显示资源列表
     */
    public function index() :\think\Response
    {
        $result = $this->model->routersByTree($this->adminInfo['user_info']->role_id);
        if (!empty($result)) {
            //构建返回数据结构
            return $this->jr('获取成功',$result);
        }
        //构建返回数据结构
        return $this->jr('获取失败');
    }
}
