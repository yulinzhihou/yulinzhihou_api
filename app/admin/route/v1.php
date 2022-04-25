<?php

use think\facade\Route;

return [
    Route::group('v1', function () {
        /*后台登录接口*/
        Route::group('login', function () {
            Route::post('login', 'Login@login');
            Route::get('logout', 'Login@logout');
        });

        /*后台管理员模块路由*/
        Route::group('admin', function () {
            Route::get('getUserInfo','Admin@userInfo');
            Route::post('upload','Admin@upload');
        });
        Route::resource('admin','Admin');

        /*菜单组*/
        Route::group('menu', function () {
            Route::get('getMenuList','Menu@index');
        });
        Route::resource('menu','Menu')->expect(['create','edit']);

        /*角色组*/
        Route::group('role', function () {
            Route::get('getPermCode','Role@getRoleMenu');
        });
        Route::resource('role','Role')->expect(['create','edit']);

    })->prefix('app\admin\controller\v1\\')->middleware([
        app\admin\middleware\checkSign::class
    ]),

    Route::miss(function(){
        return json([
            "status"    =>  504,
            'message'   =>  '路由地址未定义,不支持直接请求，请使用正确的接口地址和参数，请联系后端小哥哥',
            'method'    =>  request()->method(),
            'route'     =>  request()->url(),
            'create_time'   =>  time(),
            'date_time'     =>  date("Y-m-d H:i:s",time())
        ]);
    })
];