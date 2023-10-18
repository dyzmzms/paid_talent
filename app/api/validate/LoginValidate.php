<?php
namespace app\api\validate;

use think\Validate;

class LoginValidate extends Validate
{
    // 定义验证规则
    protected $rule = [
        'mobile' => 'require|mobile|unique:talents_user',
        'code' => 'require|length:6',
        'password' => 'require',
        'passwords'  =>'require',
        'type'  =>'require',
    ];

    // 定义提示信息
    protected $message = [
        'mobile.require' => '手机号不能为空',
        'mobile.mobile' => '手机号格式错误',
        'code.require' => '验证码不能为空',
        'password.require' => '密码不能为空',
        'code.length' => '验证码格式错误',
        'mobile.unique' => '用户被注册',
        'type.require' => '用户类型不能为空',
    ];

    // 定义验证场景
    protected $scene = [
        'register' => ['mobile', 'code','password','passwords','type'],
        'login' => ['', 'password'],
        'code' => ['mobile','code'],
        'sendCode'=>['mobile']
    ];
}