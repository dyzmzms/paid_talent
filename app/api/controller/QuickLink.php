<?php


namespace app\api\controller;


use app\BaseController;
use app\api\model\Filtering;
use app\api\model\Labor;
use app\api\model\LaborMess;
use app\api\model\Policy;
use app\api\model\Training;
use app\api\validate\ResumeValidate;

/**
 * Class QuickLink
 * @package app\controller\api
 * @note 金刚区详细内容
 * @author 小高
 */
class QuickLink extends BaseController
{
    // 提取重复代码部分为一个函数
    private function formatPolicyModel($model)
    {
        foreach ($model as $k => $item) {
            $model[$k]['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
        }
        return $model;
    }

    // 获取人才政策列表
    public function policyList()
    {
        $model = Policy::where('status', 1)
            ->order('createtime', 'desc')
            ->field('id,title,createtime')
            ->select()
            ->toArray();
        $model = $this->formatPolicyModel($model);
        $this->success('成功', $model);
    }

    // 获取人才政策详情
    public function policyDetail()
    {
        $id = input('id/d');
        $model = Policy::where(['status' => 1, 'id' => $id])
            ->field('id,title,createtime,content')
            ->select()
            ->toArray();
        if (empty($model)) $this->error('查无结果');
        $model = $this->formatPolicyModel($model);
        $this->success('成功', $model);
    }

    // 提取重复代码部分为一个函数
    private function formatTrainingModel($model)
    {
        foreach ($model as $k => $item) {
            $model[$k]['learn_time'] = date('Y/m/d H:i', $item['learn_time']);
            $model[$k]['learn_start_time'] = date('Y/m/d H:i', $item['learn_start_time']);
            $model[$k]['learn_end_time'] = date('Y/m/d H:i', $item['learn_end_time']);
            $model[$k]['reg_start_time'] = date('Y/m/d H:i', $item['reg_start_time']);
            $model[$k]['reg_end_time'] = date('Y/m/d H:i', $item['reg_end_time']);
            $model[$k]['learn_time_start'] = "{$model[$k]['learn_start_time']} - {$model[$k]['learn_end_time']}";
            $model[$k]['register_time_start'] = "{$model[$k]['reg_start_time']} - {$model[$k]['reg_end_time']}";
            $keys = ['learn_start_time', 'learn_end_time', 'reg_start_time', 'reg_end_time'];
            foreach ($keys as $item) {
                unset($model[$k][$item]);
            }
        }
        return $model;
    }

    // 获取人才培训列表
    public function training()
    {
        $model = Training::withoutField('author,image,sort,status,content,updatetime,createtime')
            ->where('status', 1)
            ->order('createtime', 'desc')
            ->select()
            ->toArray();
        $model = $this->formatTrainingModel($model);
        $this->success('成功', $model);
    }

    // 获取人才培训详情
    public function trainingDetail()
    {
        $id = input('id/d');
        // 查询培训内容
        $model = Training::withoutField('author,image,sort,status,updatetime,createtime')
            ->where(['status' => 1, 'id' => $id])
            ->order('createtime', 'desc')
            ->select()
            ->toArray();
        if (empty($model)) $this->error('查无结果');
        $model = $this->formatTrainingModel($model);
        $this->success('成功', $model);
    }

    //获取劳动关系列表
    public function laborList()
    {
        $model = Labor::where('status', 1)->field('id,image,title,mobile')->select()->toArray();
        $this->success('成功', $model);
    }

    //获取劳动关系列表
    public function laborDetail()
    {
        $id = input('id/d');
        $model = Labor::where(['status' => 1, 'id' => $id])
            ->with(['mess', 'mess.userId'])
            ->withoutField('sort,status,updatetime,createtime')
            ->where('status', 1)
            ->select()
            ->toArray();
        if (empty($model)) $this->error('查无结果');
        $this->success('成功', $model);
    }

    //新增劳动关系留言
    public function addMess()
    {
        $data = $this->request->post();
        $mess = $this->request->param('comments', '');
        //验证器验证数据
        $validate = new ResumeValidate();
        $result = $validate->scene('addMess')->check($data);
        if ($result !== true) {
            $this->success($validate->getError());
        }
        $textFiltering = Filtering::where('badword', $mess)
            ->select()
            ->toArray();
        $text = [''];
        foreach ($textFiltering as $item) {
            // 处理敏感词
            $text = $item['badword'];
            $replacement = mb_substr($text, 0, 1) . str_repeat('*', mb_strlen($text) - 1); // 使用星号替换除第一个字以外的部分

            $text = str_replace($text, $replacement, $text);
        }
        if (!empty($textFiltering)) {
            $this->error('不许说脏话', $text);
        }
        $laborMess = new LaborMess();
        $laborMess->labor_id = $data['labor_id'];
        $laborMess->user_id = $data['user_id'];
        $laborMess->content = $data['comments'];
        if ($laborMess->save()) {
            $this->success('成功');
        } else {
            $this->error('失败');
        }
    }
}