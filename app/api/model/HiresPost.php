<?php


namespace app\api\model;


use think\Model;

class HiresPost extends Model
{
    protected $name = "hires_post";
    protected $hidden = [ 'updatetime', 'user_id', 'job_id', 'status', 'sort', 'degree_id'];

    public function degree()
    {
        return $this->hasOne(Degree::class, 'id', 'degree_id')->field('id,name');
    }

    public function jobIntroduce()
    {
        return $this->hasOne(JobIntroduce::class, 'id', 'job_id')->field('id,start,end,content');
    }

    public function userId()
    {
        //只查某些字段
        return $this->belongsTo(UserModel::class, 'user_id', 'id')->field('id,username,nickname,avatar,mobile');
    }
    public function Firm()
    {
        //只查某些字段
        return $this->belongsTo(FirmProve::class, 'firm_id', 'id')->field('id,name,address');
    }

}