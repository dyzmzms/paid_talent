<?php


namespace app\api\controller;


use app\BaseController;
use app\api\model\CvLogs;
use app\api\model\FirmLogs;
use app\api\model\HiresPost;
use app\api\model\LabelGroup;
use app\api\model\Resume;
use app\api\model\UserModel;
use think\Request;

class User extends BaseController
{
    //获取用户头像昵称
    public function userData(Request $request)
    {
        //解析获取用户id
        $user = $request->user_id;
        //查询用户信息
        $Model = UserModel::field('nickname,avatar')
            ->where('id', '=', $user, 'status', '=', 1)
            ->select()
            ->toArray();
        if (empty($Model)) $this->error('用户不存在');
        $this->success('成功', $Model);
    }
    //修改个人信息
    public function editUser(Request $request)
    {
        $data = $this->request->post();
        $model = new UserModel;
        $userInfo = $model->find($request->user_id);
        // 更新头像和昵称
        $userInfo->avatar = $data['avatar'];
        $userInfo->nickname = $data['nickname'];
        if ($userInfo->save()) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }
    //获取用户简历
    public function resume(Request $request)
    {
        $user = $request->user_id;
        $Model = Resume::with(['userId', 'degree', 'position', 'workTime', 'learnTime'])
            ->where('user_id', '=', $user)
            ->where('is_show', '=', 1)
            ->withoutField('createtime,updatetime')
            ->select();
        if ($Model->isEmpty()) {
            $this->error('未找到该简历!');
        }
        //处理性别字段
        foreach ($Model as &$item) {
            $item['sex'] = $item['sex'] == 1 ? '男' : '女';
        }
        $this->success('成功', $Model);
    }

    //获取人才用户简历投递记录
    public function cvLogs(Request $request)
    {
        $user = $request->user_id;
        //查询投递记录
        $Model = CvLogs::with(['hires' => function ($query) {
            $query->with(['jobIntroduce', 'degree']);
        }])
            ->where('user_id', '=', $user)
            ->select();
        $this->success('成功', $Model);
    }

    //获取招聘岗位
    public function hiresPost(Request $request)
    {
        $user = $request->user_id;
        $where = [
            ['id', '=', $user],
            ['type', '=', 1],
        ];
        //判断是否为企业用户
        $userInfo = UserModel::where($where)->select();
        //如果为空
        if ($userInfo->isEmpty()) {
            $this->error('请使用企业用户登录！');
        }
        //查询用户发布的岗位招聘
        $data = HiresPost::with(['degree', 'jobIntroduce'])
            ->where('user_id', '=', $user)
            ->select()
            ->toArray();
        //处理标签字段
        foreach ($data as $key => $item) {
            // 使用 explode() 函数将字符串标签拆分为数组
            $labelArray = explode(',', $item['label']);
            // 使用 array_map() 函数将数组中的每个元素转换为整数
            $labelIds = array_map('intval', $labelArray);
            foreach ($labelIds as $item) {
                $data[$key]['labels'][] = LabelGroup::where('id', $item)->value('name');
            }
        }
        $this->success('成功', $data);
    }

    //获取企业收到简历
    public function firmCv(Request $request)
    {
           $user = $request->user_id;
           $Model = FirmLogs::with(['hires','resume' => function ($query) {
               $query->with(['degree', 'position','userId']);
           }])
               ->withoutField('createtime,updatetime,status')
               ->Where('user_id','=',$user)
               ->select()
               ->toArray();
        foreach ($Model as $key => $item) {
            // 使用 explode() 函数将字符串标签拆分为数组
            $labelArray = explode(',', $item['hires']['label']);
            // 使用 array_map() 函数将数组中的每个元素转换为整数
            $labelIds = array_map('intval', $labelArray);
            foreach ($labelIds as $item) {
                $Model[$key]['label'][] = LabelGroup::where('id', $item)->value('name');
            }
        }
            $this->success('查询成功',$Model);
    }
}