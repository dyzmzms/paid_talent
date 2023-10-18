<?php


namespace app\api\model;


use think\Model;

class Labor extends Model
{
    protected $name = "labor";
    public function mess()
    {
        return $this->hasMany(LaborMess::class, 'labor_id', 'id');
    }
}