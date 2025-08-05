<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

Route::get('hello/:name', 'index/hello');

// 用户认证相关路由
Route::group('user', function () {
    Route::post('login', 'User/login');
    Route::post('register', 'User/register');
    Route::get('info', 'User/info')->middleware('jwt');
    Route::post('change-password', 'User/changePassword')->middleware('jwt');
});

// 用户管理相关路由
Route::group('user', function () {
    Route::get('list', 'User/index');
    Route::post('update-status', 'User/updateStatus');
    Route::post('reset-password', 'User/resetPassword');
    Route::get('login-logs', 'User/loginLogs');
})->middleware('admin');

// 角色权限相关路由
Route::group('role', function () {
    Route::get('list', 'Role/index')->middleware('jwt');
    Route::post('add', 'Role/add')->middleware('jwt');
    Route::post('update', 'Role/update')->middleware('jwt');
    Route::post('delete', 'Role/delete')->middleware('jwt');
    Route::get('permissions', 'Role/permissions')->middleware('jwt');
    Route::post('assign-permissions', 'Role/assignPermissions')->middleware('jwt');
})->middleware('admin');

// 权限相关路由
Route::group('permission', function () {
    Route::get('list', 'Permission/index')->middleware('jwt');
    Route::post('add', 'Permission/add')->middleware('jwt');
    Route::post('update', 'Permission/update')->middleware('jwt');
    Route::post('delete', 'Permission/delete')->middleware('jwt');
})->middleware('admin');

// 需要认证的路由组

    // 实验室管理
    Route::group('lab', function () {
        Route::get('/', 'Lab/index');
        Route::get('detail', 'Lab/detail');
        Route::post('add', 'Lab/add');
        Route::post('update', 'Lab/update');
        Route::post('delete', 'Lab/delete');
    })->middleware('jwt');

    // 设备管理
    Route::group('equipment', function () {
        Route::get('/', 'Equipment/index');
        Route::get('detail', 'Equipment/detail');
        Route::post('add', 'Equipment/add');
        Route::post('update', 'Equipment/update');
        Route::post('delete', 'Equipment/delete');
    })->middleware('jwt');

    // 试剂管理
    Route::group('reagent', function () {
        Route::get('/', 'Reagent/index');
        Route::get('detail', 'Reagent/detail');
        Route::post('add', 'Reagent/add');
        Route::post('update', 'Reagent/update');
        Route::post('delete', 'Reagent/delete');
        Route::post('in-out', 'Reagent/inOut');
        Route::post('approve', 'Reagent/approve');
    })->middleware('jwt');

    // 预约管理
    Route::group('reservation', function () {
        Route::post('create', 'Reservation/create');
        Route::get('list', 'Reservation/list');
        Route::get('my', 'Reservation/my');
        Route::post('cancel', 'Reservation/cancel');
        Route::post('review', 'Reservation/review');
    })->middleware('jwt');

// 仪表盘路由
Route::group('dashboard', function () {
    Route::get('statistics', 'Dashboard/statistics');
    Route::get('today-lab-usage', 'Dashboard/todayLabUsage');
    Route::get('tomorrow-reservations', 'Dashboard/tomorrowReservations');
    Route::get('pending-reagents', 'Dashboard/pendingReagents');
    Route::post('approve-reagent', 'Dashboard/approveReagent');
    Route::post('reject-reagent', 'Dashboard/rejectReagent');
})->middleware('jwt');

