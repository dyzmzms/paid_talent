<?php


namespace app\api\validate;


use think\Validate;

class ResumeValidate extends Validate
{
    // 定义验证规则
    protected $rule = [
        'phone' => 'require|mobile|length:11',
        'sex' => 'require',
        'name' => 'require',
        'image' => 'require',
        'idcard' => 'require|length:18',
        'email' => 'require',
        'address' => 'require',
        'degree_id' => 'require',
        'degree' => 'require',
        'salary' => 'require',
        'adept_at' => 'require',
        'describes' => 'require',
        'position_id' => 'require',
        'position' => 'require',
        'label' => 'require',
        'min' => 'require',
        'max' => 'require',
        'content' => 'require',
        'workadd' => 'require',
        'firm_describes' => 'require',
        'status' => 'require',
        'is_show' => 'require',
        'major'=>'require',
        'start_time'=>'require',
        'end_time'=>'require',
        'work_id'=>'require',
        'user_id'=>'require',
        'labor_id'=>'require',
        'comments'=>'require',
        'hires_id'=>'require',
        'users_id'=>'require',
    ];

    // 定义提示信息
    protected $message = [
        'phone.require' => '手机号不能为空',
        'phone.mobile' => '手机号格式错误',
        'phone.length' => '手机号格式错误',
        'name.require' => '姓名不能为空',
        'sex.require' => '性别不能为空',
        'email.require' => '邮箱不能为空',
        'address.require' => '地址不能为空',
        'degree_id.require' => '学历不能为空',
        'degree.require' => '学历不能为空',
        'position_id.require' => '应聘岗位不能为空',
        'salary.require' => '期望薪资不能为空',
        'adept_at.require' => '擅长领域不能为空',
        'describes.require' => '个人介绍不能为空',
        'status.require' => '在职状态不能为空',
        'is_show.require' => '是否公布不能为空',
        'idcard.length' => '身份证格式错误',
        'start_time.require' => '开始时间不能为空',
        'end_time.require' => '结束时间不能为空',
        'work_id.require' => '工作岗位不能为空',
        'position.require' => '岗位名称不能为空',
        'label.require' => '岗位标签不能为空',
        'min.require' => '最低工作不能为空',
        'max.require' => '最高不能为空',
        'content.require' => '岗位内容不能为空',
        'workadd.require' => '工作地址不能为空',
        'firm_describes.require' => '公司介绍不能为空',
        'user_id.require' => '用户id不能为空',
        'labor_id.require' => '劳动关系id不能为空',
        'comments.require' => '评论不能为空',
        'hires_id'=>'require','岗位id不能为空',
        'users_id'=>'require','岗位id不能为空'
    ];

    // 定义验证场景
    protected $scene = [
        'add' => ['phone', 'name', 'sex', 'idcard','email','addres','degree_id','salary','adept_at','describes','hires','status','is_show'],
        'hires'=>['name','major','degree','start_time','end_time'],
        'work'=>['name','work_id','job_describe','start_time','end_time'],
        'addhires'=>['position','label','degree_id','min','max','content','workadd','address','firm_describes','start_time','end_time'],
        'addMess'=>['user_id','labor_id','comments'],
        'Mess'=>['user_id'],
        'firmMess'=>['user_id','comments','users_id'],
        ];
}