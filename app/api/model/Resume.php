<?php


namespace app\api\model;


use think\Model;

class Resume extends Model
{
    protected $name = "resume";
    //忽略某些字段
    protected $hidden = ['degree_id', 'position_id', 'user_id', 'createtime', 'updatetime'];

    //查询用户
    public function userId()
    {
        //只查某些字段
        return $this->belongsTo(UserModel::class, 'user_id', 'id')->field('id,username,nickname,avatar');
    }

    //学历
    public function degree()
    {
        //只查某些字段
        return $this->belongsTo(Degree::class, 'degree_id', 'id')->field('id,name');
    }

    //岗位
    public function position()
    {
        //只查某些字段
        return $this->belongsTo(Position::class, 'position_id', 'id')->field('id,name');
    }

    //工作经历
    public function workTime()
    {
        //忽略某些字段
        return $this->hasMany(Work::class, 'resume_id')->hidden(['createtime', 'updatetime']);
    }

    //教育经历
    public function learnTime()
    {
        //忽略某些字段
        return $this->hasMany(LearnTime::class, 'resume_id')->hidden(['createtime', 'updatetime', 'status']);
    }

    //工作经历
    public function workTimes()
    {
        //忽略某些字段
        return $this->hasOne(Work::class, 'resume_id')->order('end_time desc')->hidden(['createtime', 'updatetime']);
    }

}