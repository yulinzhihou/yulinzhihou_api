<?php

use think\migration\Seeder;

class MenuInit extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $table = $this->table('menu');
        $str = <<<EOF
[{"id":1,"pid":0,"name":"Dashboard","path":"\/dashboard","component":"LAYOUT","title":"\u63a7\u5236\u53f0","affix":1,"icon":"ant-design:appstore-outlined","redirect":"\/dashboard\/analysis","type":0,"hidden_menu":0,"status":1,"sort":1},{"id":2,"pid":1,"name":"Analysis","path":"analysis","component":"\/dashboard\/analysis\/index","title":"\u5206\u6790\u9875","affix":0,"icon":"clarity:radar-solid","redirect":"","type":1,"hidden_menu":0,"status":1,"sort":9},{"id":3,"pid":1,"name":"Workbench","path":"workbench","component":"\/dashboard\/workbench\/index","title":"\u5de5\u4f5c\u53f0","affix":0,"icon":"clarity:display-solid","redirect":"","type":1,"hidden_menu":0,"status":1,"sort":10},{"id":4,"pid":0,"name":"System","path":"\/system","component":"LAYOUT","title":"\u7cfb\u7edf\u7ba1\u7406","affix":1,"icon":"ion:settings-outline","redirect":"\/system\/account","type":0,"hidden_menu":0,"status":1,"sort":1},{"id":5,"pid":4,"name":"AccountManagement","path":"account","component":"\/admin\/system\/account\/index","title":"\u8d26\u53f7\u7ba1\u7406","affix":0,"icon":"clarity:assign-user-solid","redirect":"","type":1,"hidden_menu":0,"status":1,"sort":9},{"id":6,"pid":5,"name":"","path":"","component":"","title":"\u6dfb\u52a0\u8d26\u53f7","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":7,"pid":5,"name":"","path":"","component":"","title":"\u7f16\u8f91\u8d26\u53f7","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":8,"pid":5,"name":"","path":"","component":"","title":"\u5220\u9664\u8d26\u53f7","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":9,"pid":5,"name":"","path":"","component":"","title":"\u83b7\u53d6\u7528\u6237token","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":10,"pid":4,"name":"RoleManagement","path":"role","component":"\/admin\/system\/role\/index","title":"\u89d2\u8272\u7ba1\u7406","affix":0,"icon":"clarity:group-solid","redirect":"","type":1,"hidden_menu":0,"status":1,"sort":1},{"id":11,"pid":10,"name":"","path":"","component":"","title":"\u6dfb\u52a0\u89d2\u8272","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":12,"pid":10,"name":"","path":"","component":"","title":"\u7f16\u8f91\u89d2\u8272","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":13,"pid":10,"name":"","path":"","component":"","title":"\u5220\u9664\u89d2\u8272","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":14,"pid":4,"name":"MenuManagement","path":"menu","component":"\/admin\/system\/menu\/index","title":"\u83dc\u5355\u7ba1\u7406","affix":0,"icon":"clarity:indent-line","redirect":"","type":1,"hidden_menu":0,"status":1,"sort":1},{"id":15,"pid":14,"name":"","path":"","component":"","title":"\u6dfb\u52a0\u83dc\u5355","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":16,"pid":14,"name":"","path":"","component":"","title":"\u7f16\u8f91\u83dc\u5355","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":17,"pid":14,"name":"","path":"","component":"","title":"\u5220\u9664\u83dc\u5355","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":18,"pid":4,"name":"Sysinfo","path":"sysinfo","component":"\/admin\/system\/sysinfo\/index","title":"\u7cfb\u7edf\u914d\u7f6e","affix":0,"icon":"clarity:settings-solid-badged","redirect":"","type":1,"hidden_menu":0,"status":1,"sort":1},{"id":19,"pid":18,"name":"","path":"","component":"","title":"\u6dfb\u52a0\u914d\u7f6e","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":20,"pid":18,"name":"","path":"","component":"","title":"\u7f16\u8f91\u914d\u7f6e","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":21,"pid":18,"name":"","path":"","component":"","title":"\u5220\u9664\u914d\u7f6e","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":22,"pid":4,"name":"Modify","path":"modify","component":"\/admin\/system\/account\/modify","title":"\u4e2a\u4eba\u8bbe\u7f6e","affix":0,"icon":"clarity:user-solid","redirect":"","type":1,"hidden_menu":0,"status":1,"sort":1},{"id":23,"pid":4,"name":"Crontab","path":"crontab","component":"\/admin\/system\/crontab\/index","title":"\u5b9a\u65f6\u7ba1\u7406","affix":0,"icon":"clarity:clock-solid","redirect":"","type":1,"hidden_menu":0,"status":1,"sort":1},{"id":24,"pid":23,"name":"","path":"","component":"","title":"\u6dfb\u52a0\u5b9a\u65f6","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":25,"pid":23,"name":"","path":"","component":"","title":"\u7f16\u8f91\u5b9a\u65f6","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":26,"pid":23,"name":"","path":"","component":"","title":"\u5220\u9664\u5b9a\u65f6","affix":0,"icon":"","redirect":"","type":2,"hidden_menu":1,"status":1,"sort":10},{"id":27,"pid":0,"name":"Logs","path":"\/logs","component":"LAYOUT","title":"\u65e5\u5fd7","affix":1,"icon":"ant-design:file-outlined","redirect":"","type":0,"hidden_menu":0,"status":1,"sort":1},{"id":28,"pid":27,"name":"AdminLog","path":"adminlog","component":"\/admin\/logs\/log-login\/index","title":"\u767b\u5f55\u65e5\u5fd7","affix":0,"icon":"clarity:checkbox-list-line","redirect":"","type":1,"hidden_menu":0,"status":1,"sort":9},{"id":29,"pid":27,"name":"Log","path":"log-sql","component":"\/admin\/logs\/log-sql\/index","title":"\u64cd\u4f5c\u65e5\u5fd7","affix":0,"icon":"clarity:e-check-line","redirect":"","type":1,"hidden_menu":0,"status":1,"sort":10},{"id":30,"pid":27,"name":"MyErrorLog","path":"errorlog","component":"\/admin\/logs\/log-error\/index","title":"\u9519\u8bef\u65e5\u5fd7","affix":0,"icon":"clarity:export-outline-alerted","redirect":"","type":1,"hidden_menu":0,"status":1,"sort":10},{"id":31,"pid":0,"name":"About","path":"\/about","component":"LAYOUT","title":"\u5173\u4e8e","affix":1,"icon":"simple-icons:about-dot-me","redirect":"\/about\/index","type":0,"hidden_menu":0,"status":1,"sort":127},{"id":32,"pid":31,"name":"AboutPage","path":"index","component":"\/sys\/about\/index","title":"\u5173\u4e8e","affix":0,"icon":"clarity:help-solid","redirect":"","type":1,"hidden_menu":0,"status":1,"sort":0},{"id":33,"pid":0,"name":"File","path":"\/file","component":"LAYOUT","title":"\u6587\u4ef6\u7d22\u5f15","affix":0,"icon":"ant-design:file-done-outlined","redirect":"","type":0,"hidden_menu":0,"status":1,"sort":9},{"id":34,"pid":33,"name":"commonitem","path":"\/file\/commonitem","component":"\/file\/commonitem","title":"\u7269\u54c1\u7d22\u5f15","affix":0,"icon":"ant-design:file-text-outlined","redirect":"\/file\/commonitem","type":1,"hidden_menu":0,"status":1,"sort":7}]
EOF;

        $data = json_decode($str,true);
        $table->insert($data)->saveData();
    }
}
