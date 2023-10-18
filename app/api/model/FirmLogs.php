<?php


namespace app\api\model;


use think\Model;

class FirmLogs extends Model

{
    protected $name = "firm_cv_logs";
    protected $hidden = ['user_id', 'hires_id', 'resume_id'];
    //发布的岗位
    public function hires()
    {
        return $this->hasOne(HiresPost::class, 'id', 'hires_id');
    }
    //简历
    public function resume()
    {
        return $this->hasOne(Resume::class, 'id', 'resume_id');
    }
    //学历
    public function degree()
    {
        return $this->hasOne(Degree::class, 'id', 'degree_id')->field('id,name');
    }
    //
    public function position()
    {
        //只查某些字段
        return $this->belongsTo(Position::class, 'position_id', 'id')->field('id,name');
    }
}