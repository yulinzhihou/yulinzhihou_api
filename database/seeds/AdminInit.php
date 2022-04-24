<?php

use think\migration\Seeder;

class AdminInit extends Seeder
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
        $table = $this->table('admin');
        $data = [
            'id'	    =>	1,
            'username'	=>	'admin',
            'password'	=>	md5(md5('123456').'gsgameshare.com'),
            'real_name'	=>	'系统管理员',
            'avatar'	=>	'./public/img/logo.png',
            'desc'	    =>	'系统管理员',
            'role_id'	=>	1,
            'phone'	    =>	'18888888888',
            'email'	    =>	'yulinzhihou@gmail.com',
            'salt'	    =>	'gsgameshare.com',
            'extension'	=>	null,
            'sort'	    =>	1,
            'status'	=>	1,
            'create_time'	=>	time(),
            'update_time'	=>	time()
        ];

        $table->insert($data)->saveData();

    }
}