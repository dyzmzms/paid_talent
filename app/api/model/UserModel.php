<?php


namespace app\api\model;


use think\Model;

class UserModel extends Model
{
    protected $name = "talents_user";

    public function FirmProve()
    {
        return $this->belongsTo(FirmProve::class, 'user_id', 'id');
    }

}