<?php


namespace app\api\controller;


use app\BaseController;
use app\api\model\Degree;
use app\api\model\FirmProve;
use app\api\model\HiresPost;
use app\api\model\LabelGroup;
use app\api\model\LearnTime;
use app\api\model\Position;
use app\api\model\Resume;
use app\api\model\UserModel;
use app\api\model\Work;
use app\api\model\WorkHires;
use app\Request;
use app\api\validate\ResumeValidate;

class AddMess extends BaseController
{
    //学历列表
    public function degree()
    {
        $Model = Degree::field('id,name')->select()->toArray();
        $this->success('成功', $Model);
    }

    //岗位列表
    public function position()
    {
        $Model = Position::field('id,name')->select()->toArray();
        $this->success('成功', $Model);
    }

    //新增简历
    public function addResume(Request $request)
    {
        $user = $request->user_id;
        //判断是否以post提交
        if ($this->request->isPost()) {
            $data = $this->request->param();
            //验证器验证数据
            $validate = new ResumeValidate();
            $result = $validate->scene('add')->check($data);
            if ($result !== true) {
                $this->success($validate->getError());
            }
            //先查询
            $model = Resume::where('user_id', '=', $user)->find();
            //如果为空,新增一条简历
            if (empty($model)) {
                $newResume = new Resume();
                //处理工作时间
               $ids =  Work::where('user_id',$user)->field('id,start_time,end_time')->select()->toArray();
//               halt($ids);
                $earliestTime = null;
                $latestTime = null;
                foreach ($ids as $item) {
                    $startTime = $item['start_time'];
                    $endTime = $item['end_time'];
                    if ($earliestTime === null || $startTime < $earliestTime) {
                        $earliestTime = $startTime;
                    }
                    if ($latestTime === null || $endTime > $latestTime) {
                        $latestTime = $endTime;
                    }
                }
                $diff = $latestTime - $earliestTime; // 计算时间差（秒）
                $years =intval(round($diff / (365 * 24 * 60 * 60))); // 将时间差转换为年数
                $new = $newResume->save(
                    [
                        'user_id' => $user,
                        'name' => $data['name'],
                        'image' => $data['image'],
                        'idcard' => $data['idcard'],
                        'phone' => $data['phone'],
                        'sex' => $data['sex'],
                        'email' => $data['email'],
                        'address' => $data['address'],
                        'worktime' =>$years,
                        'degree_id' => $data['degree_id'],
                        'position_id' => $data['position_id'],
                        'adept_at' => $data['adept_at'],
                        'salary' => $data['salary'],
                        'describes' => $data['describes'],
                        'status' => $data['status'],
                        'is_show' => $data['is_show']
                    ]);
                if ($new = true) {
                    $this->success('添加成功！');
                } else {
                    $this->error('添加失败');
                }
            } elseif (!empty($model)) { //如果简历存在
                //判断修改了哪些字段
                $newResumes = new Resume();
                $ResumeInfo = $newResumes
                    ->where('user_id', '=', $user)
                    ->select()
                    ->toArray();
                // 更新数据
                $result = $newResumes
                    ->where('user_id', '=', $user)
                    ->update($data);
                if ($result) {
                    $this->success('更新成功！');
                } else {
                    $this->error('未作出修改！');
                }
            }
        }
    }

    //新增教育经历
    public function learns(Request $request)
    {
        //14
        $user = $request->user_id;
//        halt($user);
        $data = $this->request->param();
//        halt($data);
        //验证器验证数据
        $validate = new ResumeValidate();
        $result = $validate->scene('hires')->check($data);
        if ($result !== true) {
            $this->success($validate->getError());
        }
        //先查询是哪个简历的id
        $resumeId = Resume::where('user_id', '=', $user)->value('id');
//        halt($resumeId);
        //查询有多少工作经验
        $newLearn = new LearnTime();
        $count = $newLearn->where('resume_id', '=', $resumeId)->count();
//        halt($count);
        if ($count < 5) {
            $res = $newLearn->save(
                [
                    'resume_id' => $resumeId,
                    'name' => $data['name'],
                    'major' => $data['major'],
                    'degree' => $data['degree'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'createtime' => time()
                ]);
            if ($res) {
                $this->success('添加成功！');
            } else {
                $this->error('添加失败！');
            }
        } else {
            $this->error('最多五条教育经历！');
        }
    }

    //新增工作经历
    public function workTime(Request $request)
    {
        $user = $request->user_id;
//        halt($user);14
        $data = $this->request->param();
//        halt($data);
        //验证器验证数据
        $validate = new ResumeValidate();
        $result = $validate->scene('work')->check($data);
        if ($result !== true) {
            $this->success($validate->getError());
        }
        //先查询是哪个简历的id 2
        $resumeId = Resume::where('user_id', '=', $user)->value('id');
//        halt($resumeId);
        //查询有多少工作经验
        $newWork = new Work();
        $count = $newWork->where('resume_id', '=', $resumeId)->count();
        //每个人上限五个工作经验
        if ($count < 5) {
            $res = $newWork->save(
                [
                    'user_id' => $request->user_id,
                    'name' => $data['name'],
                    'work_id' => $data['work_id'],
                    'job_describe' => $data['job_describe'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'createtime' => time()
                ]);
            if ($res) {
                $this->success('添加成功！');
            } else {
                $this->error('添加失败！');
            }
        } else {
            $this->error('最多五条工作经历！');
        }
//        halt($count);
    }

    //获取工作岗位
    public function workHires()
    {
        $workHires = WorkHires::where('status', '=', 1)
            ->field('id,name,abstract')
            ->select()->toArray();

        $this->success('成功', $workHires);
    }

    //获取标签
    public function label()
    {
        $list = LabelGroup::field('id,name,pid')->select()->toArray();
        $groupedData = [];
        //分组 以fid分组
        foreach ($list as $item) {
            $parentId = $item['pid'];

            if ($parentId === 0) {
                $groupedData[$item['id']] = $item;
            } else {
                if (!isset($groupedData[$parentId]['children'])) {
                    $groupedData[$parentId]['children'] = [];
                }
                $groupedData[$parentId]['children'][] = $item;
            }
        }
        $this->success('成功', $groupedData);
    }

    //新增招聘岗位
    public function addHires(Request $request)
    {
        //14
        $user = $request->user_id;
//        halt($user);
        $data = $this->request->param();
        //验证器验证数据
        $validate = new ResumeValidate();
        $result = $validate->scene('addhires')->check($data);
        if ($result !== true) {
            $this->success($validate->getError());
        }
        //获取这个用户关联的公司
        $firmID = UserModel::where('id', '=', $user)->value('firm_id');
        $FirmName = FirmProve::Where('id', '=', $firmID)->value('name');
        $newHires = new HiresPost();
        $res = $newHires->save(
            [
                'firm_id' => $firmID,
                'user_id' => $user,
                'position' => $data['position'],
                'label' => $data['label'],
                'degree_id' => $data['degree_id'],
                'min' => $data['min'],
                'max' => $data['max'],
                'content' => $data['content'],
                'workadd' => $data['workadd'],
                'address' => $data['address'],
                'firm_describes' => $data['firm_describes'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'createtime' => time()
            ]);
        if ($res) {
            $this->success('添加成功！');
        } else {
            $this->error('添加失败！');
        }
    }
}