<?php


namespace app\api\model;


use think\Model;

class CvLogs extends Model
{
       protected $name = "cv_logs";
       protected $hidden = ['user_id','hires_id','status','createtime','updatetime'];
       public function hires()
       {
           return $this->hasOne(HiresPost::class,'id','hires_id');
       }
}