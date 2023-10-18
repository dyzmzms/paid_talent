<?php


namespace app\api\model;


use think\Model;

class SendSms extends Model
{
    protected $name = 'code';

    protected $createTime  = 'createtime';

    // 清空某个手机号的验证码记录
    public function deleteSmsCode($mobile)
    {
        $this->where('mobile', '=', $mobile)->delete();
    }
}