<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class MenuCreate extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('menu',['engine'=>'innodb','charset'=>'utf8mb4','auto_increment'=>true,'comment'=>'菜单表']);
        $table
            ->addColumn('pid','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'父ID'])
            ->addColumn('name','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'路由名称'])
            ->addColumn('path','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'路由地址'])
            ->addColumn('component','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'组件地址'])
            ->addColumn('title','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'页面title'])
            ->addColumn('affix','integer',['limit'=>MysqlAdapter::INT_TINY,'null'=>false,'default'=>0,'comment'=>'是否固定：1是，0否'])
            ->addColumn('icon','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'图标'])
            ->addColumn('redirect','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'重定向地址'])
            ->addColumn('permission','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'权限标识'])
            ->addColumn('type','integer',['limit'=>MysqlAdapter::INT_TINY,'null'=>false,'default'=>1,'comment'=>'类型：0=目录,1=菜单,2=按钮'])
            ->addColumn('hidden_menu','integer',['limit'=>MysqlAdapter::INT_TINY,'null'=>false,'default'=>0,'comment'=>'路由不再菜单显示：1是，0否'])
            ->addColumn('hidden_children_in_menu','integer',['limit'=>MysqlAdapter::INT_TINY,'null'=>false,'default'=>0,'comment'=>'是否隐藏子菜单：1是，0否'])
            ->addColumn('hide_breadcrumb','integer',['limit'=>MysqlAdapter::INT_TINY,'null'=>false,'default'=>0,'comment'=>'面包屑是否隐藏：1是，0否'])
            ->addColumn('hidden_tab','integer',['limit'=>MysqlAdapter::INT_TINY,'null'=>false,'default'=>0,'comment'=>'路由不再标签页显示：1是，0否'])
            ->addColumn('ignore_keep_alive','integer',['limit'=>MysqlAdapter::INT_TINY,'null'=>false,'default'=>0,'comment'=>'忽略保持激活：1是，0否'])
            ->addColumn('ignore_route','integer',['limit'=>MysqlAdapter::INT_TINY,'null'=>false,'default'=>0,'comment'=>'忽略路由：1是，0否'])
            ->addColumn('carry_param','integer',['limit'=>MysqlAdapter::INT_TINY,'null'=>false,'default'=>0,'comment'=>'是否带参数：1是，0否'])
            ->addColumn('show_menu','integer',['limit'=>MysqlAdapter::INT_TINY,'null'=>false,'default'=>0,'comment'=>'总是显示：1是，0否'])
            ->addColumn('no_cache','integer',['limit'=>MysqlAdapter::INT_TINY,'null'=>false,'default'=>0,'comment'=>'是否缓存：1是，0否'])
            ->addColumn('sort','integer',['limit'=>MysqlAdapter::INT_TINY,'null'=>false,'default'=>0,'comment'=>'菜单排序,只对第一级有效：0-255'])
            ->addColumn('status','integer',['limit'=>MysqlAdapter::INT_TINY,'null'=>false,'default'=>1,'comment'=>'状态：为1正常，为0禁用'])
            ->addColumn('real_path','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'动态路由实际路径'])
            ->addColumn('frame_src','string',['limit'=>255,'null'=>false,'default'=>'','comment'=>'iframe内嵌链接'])
            ->addColumn('transition_name','string',['limit'=>255,'null'=>false,'default'=>'','comment'=>'路由切换动画名'])
            ->addColumn('current_active_menu','string',['limit'=>255,'null'=>false,'default'=>'','comment'=>'是否为当前激活菜单'])
            ->addColumn('create_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'创建时间'])
            ->addColumn('update_time','integer',['limit'=>11,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'更新时间'])
            ->setPrimaryKey('id')
            ->addIndex('id')
            ->addIndex('name')
            ->addIndex('path')
            ->addIndex('title')
            ->create();
    }
}
