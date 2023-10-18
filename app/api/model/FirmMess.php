<?php


namespace app\api\model;


use think\Model;

class FirmMess extends Model
{
    protected $name = "firm_mess";
    protected $createTime  = 'createtime';
    protected $hidden = ['user_id','users_id','status'];
    public function userId()
    {
        //只查某些字段
        return $this->belongsTo(UserModel::class, 'user_id','id')->field('id,username,nickname,avatar');
    }
}