<?php

use think\facade\Route;

Route::group('api',function (){

    //文本过滤
    Route::post('filtering', 'api.TextFiltering/filtering');
    Route::get('hello/:name', 'index/hello');
    //分组 （注册登录）
    Route::group('login', function () {
        //密码登录
        Route::any('sign', 'Login/login');
        //发送验证码
        Route::post('SendSms', 'Login/SendSms');
        //注册
        Route::post('signup', 'Login/register');
        //验证码登录
        Route::post('codeLogin', 'Login/codeLogin');
        //修改密码
        Route::post('back', 'Login/backPassword');
    });
//上传头像
    Route::post('upload', 'AvatarUp/upload');
//获取学历列表
    Route::post('degree', 'AddMess/degree');
//获取岗位列表
    Route::post('position', 'AddMess/position');
//获取工作岗位
    Route::post('workhires', 'AddMess/workHires');
//获取标签列表
    Route::post('label', 'AddMess/label');
//自定义标签
    Route::post('newLabel', 'AddMess/newLabel');
//获取用户信息
    Route::group('index', function () {
        //获取头像昵称
        Route::post('user', 'user/userData');
        //修改头像昵称
        Route::post('editUser', 'user/editUser');
        //获取简历
        Route::post('resume', 'user/resume');
        //获取简历投递记录
        Route::post('cvLogs', 'user/cvLogs');
        //获取发布的岗位
        Route::post('hires', 'user/hiresPost');
        //
        Route::post('firmLogs', 'user/firmCv');

        Route::post('type', 'Login/type');
    })->middleware(\app\api\middleware\ApiToken::class);
//新增信息
    Route::group('add', function () {
        //新增简历
        Route::post('addresume', 'AddMess/addResume');
        //新增教育经历
        Route::post('learn', 'AddMess/learns');
        //新增工作经历
        Route::post('work', 'AddMess/workTime');
        //新增招聘岗位
        Route::post('addhires', 'AddMess/addHires');
    })->middleware(\app\api\middleware\ApiToken::class);
//获取首页信息
    Route::group('index', function () {
        //轮播图
        Route::post('banner', 'Index/banner');
        //金刚区
        Route::post('link', 'Index/link');
        //首页列表
        Route::post('indexInfo', 'Index/indexInfo');
        //获取人才详情
        Route::post('resumeinfo','Index/resumeInfo');
        //获取岗位详情
        Route::post('hiresinfo','Index/hiresInfo');
        //搜索人才
        Route::post('searchResume','Index/searchResume');
        //搜索岗位
        Route::post('searchHires','Index/searchHires');
        //获取我的留言
        Route::post('mess','Index/mess');
        //企业留言
        Route::post('firmMess','Index/firmMess');
        //获取企业留言
        Route::post('getmess','Index/getMess');
        //报名
        Route::post('application','Index/application');
    })->middleware(\app\api\middleware\ApiToken::class);
//获取金刚区详细
    Route::group('link',function (){
        //获取人才政策列表
        Route::post('list', 'QuickLink/policyList');
        //获取人才政策详情
        Route::post('detail', 'QuickLink/policyDetail');
        //获取人才培训列表
        Route::post('training', 'QuickLink/training');
        //获取人才培训详情
        Route::post('trainingDetail', 'QuickLink/trainingDetail');
        //获取劳动关系列表
        Route::post('laborList', 'QuickLink/laborList');
        //获取劳动关系详情
        Route::post('laborDetail', 'QuickLink/laborDetail');
        //新增留言
        Route::post('addMess', 'QuickLink/addMess');
    })->middleware(\app\api\middleware\ApiToken::class);
});
