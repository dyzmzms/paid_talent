<?php


namespace app\api\model;


use think\Model;

class LaborMess extends Model
{
    protected $name = "labor_mess";
    protected $hidden = ['user_id', 'createtime', 'updatetime', 'status', 'labor_id'];
    protected $createTime  = 'createtime';
    public function userId()
    {
        //只查某些字段
        return $this->belongsTo(UserModel::class, 'user_id', 'id')->field('id,username,nickname,avatar');
    }

}