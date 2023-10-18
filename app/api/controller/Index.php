<?php


namespace app\api\controller;


use app\BaseController;
use app\api\model\Banner;
use app\api\model\Filtering;
use app\api\model\FirmMess;
use app\api\model\FirmProve;
use app\api\model\HiresPost;
use app\api\model\LabelGroup;
use app\api\model\LaborMess;
use app\api\model\Link;
use app\api\model\Resume;
use app\api\model\TrainingRegister;
use app\api\model\UserModel;
use app\api\model\Work;
use app\api\validate\ResumeValidate;
use think\Request;
use think\response\Json;

class Index extends BaseController
{
    //获取轮播图
    public function banner()
    {
        $banner = Banner::field('id,title,image')->where('status', '=', 1)->select()->toArray();
        $this->success('成功', $banner);
    }

    /**
     * @note 获取金刚区
     * 判断用户类型，个人与企业展示不同
     */
    public function link(Request $request)
    {
        //  判断用户类型
        $type = UserModel::where('id', $request->user_id)->value('type');
        if (!empty($type)) {
            $type_ids = $type == 1 ? [1, 2] : [1, 3];
            $link = Link::whereIn('type', $type_ids)->order('sort', 'desc')->select()->toArray();
            $this->success('成功', $link);
        } else {
            $this->error('请先登录!');
        }
    }

    /**
     * @note 根据角色返回不同数据信息
     * @return json
     */
    public function indexInfo(Request $request): Json
    {
        //  判断用户类型
        $type = UserModel::where('id', '=', $request->user_id)->value('type');
        if (empty($type)) $type = 1;
        if ($type == 1) {
            //  企业用户
            //  获取简历
            $resume = Resume::with(['degree', 'position'])
                ->field('id,name,degree_id,worktime,image,position_id,sex')
                ->withAttr('sex', function ($val) {
                    return $val == 1 ? '男' : '女';
                })
                ->select()
                ->toArray();
            foreach ($resume as $k =>$item) {
                $degreeName = $item["degree"]["name"];
                $positionName = $item["position"]["name"];
                $workTime = $item["worktime"];
                $newField = []; // 初始化新的描述数组
                $newField[] = $degreeName . '.' .'工作'. $workTime .'年'. '.' . $positionName;
                $resume[$k]['describes'] = $newField;
                unset($resume[$k]["degree"]);
                unset($resume[$k]["position"]);
                unset($resume[$k]["worktime"]);
            }
               $this->success('成功', $resume);
        } else {
            //岗位
            $Hires = HiresPost::with(['Firm', 'userId'])->select()->toArray();
            //计算薪资
            foreach ($Hires as $k => $item) {
                $min = $item['min'];
                $max = $item['max'];
                $Hires[$k]['min'] = $min / 1000;
                $Hires[$k]['max'] = $max / 1000;
            }
            ////处理期望薪资
            foreach ($Hires as $k => $item) {
                $degreeName = $Hires[$k]["min"];
                $positionName = $Hires[$k]["max"];
                $YearValue = "{$degreeName}-{$positionName}K";
                $Hires[$k]['Salary'] = $YearValue;
            }
            //处理标签
            foreach ($Hires as $key => $item) {
                // 使用 explode() 函数将字符串标签拆分为数组
                $labelArray = explode(',', $item['label']);
                // 使用 array_map() 函数将数组中的每个元素转换为整数
                $labelIds = array_map('intval', $labelArray);
                foreach ($labelIds as $k => $item) {
                    $Hires[$key]['labels'][] = LabelGroup::where('id', $item)->value('name');
                    unset($Hires[$k]["label"]);
                    unset($Hires[$k]["firm_id"]);
                    unset($Hires[$k]["min"]);
                    unset($Hires[$k]["max"]);
                }
            }
            $this->success('成功', $Hires);
        }
    }

    //获取人才详情
    public function resumeInfo(Request $request)
    {
        $resume = Resume::with(['degree', 'position', 'workTime', 'learnTime'])
            ->field('id,name,degree_id,image,salary,position_id,sex,address,email,adept_at,describes')
            ->select()->toArray();
        foreach ($resume as $k => $item) {
            $many = $item['salary'];
            $resume[$k]['salary'] = $many / 1000;
        }
        //处理性别字段
        foreach ($resume as &$item) {
            $item['sex'] = $item['sex'] == 1 ? '男' : '女';
        }
        foreach ($resume as $k => $item) {
            $degreeName = $resume[$k]["salary"];
            $YearValue = "{$degreeName}K";
            $resume[$k]['Salary'] = $YearValue;
        }
        foreach ($resume as $k => $item) {
            $years = 0; // 记录总年数
            foreach ($item['workTime'] as $work) {
                $startYear = date('Y', $work['start_time']);
                $endYear = date('Y', $work['end_time']);
                $years += ($endYear - $startYear);
            }
            $newItem = [
                'name' => $item['name'],
                'abstract' => "{$item['degree']['name']}·工作{$years}年·{$item['position']['name']}·{$item['Salary']}",
                'email' => $item['email'],
                'address' => $item['address']
            ];
            $resume[$k]['title'] = $newItem;
        }
        $this->success('1', $resume);
    }

    /**
     * @note 获取招聘岗位详情
     */
    public function hiresInfo()
    {
        // 1.查询岗位列表
        $model = HiresPost::with(['Firm', 'degree'])
            ->select()->toArray();
        // 2.处理岗位名称 公司名称 公司地址 薪资 学历名称 创建时间
        foreach ($model as $k => $item) {
            $min = $item['min'];
            $max = $item['max'];
            $model[$k]['min'] = $min / 1000;
            $model[$k]['max'] = $max / 1000;
            $degreeName = $model[$k]["min"];
            $positionName = $model[$k]["max"];
            $YearValue = "{$degreeName}-{$positionName}K";
            $model[$k]['Salary'] = $YearValue;
            $newItem = [
                'position' => $item['position'],
                'firm_name' => $item['Firm']['name'],
                'address' => $item['address'],
                'degree' => $item['degree']['name'],
                'Salary' => $model[$k]['Salary'],
                'createtime' => date('Y/m/d H:i', $item['createtime'])
            ];
            $model[$k]['title'] = $newItem;
            $model['start_time'] = substr($item['start_time'], 0, 5);
            $model['end_time'] = substr($item['end_time'], 0, 5);
            $TimeValue = "{$model['start_time']}-{$model['end_time']}";
            //            halt($TimeValue);
            $newkey = [
                'worktime' => $TimeValue,
                'workadd' => $item['workadd'],
                'welfare' => $item['welfare']
            ];
            $model[$k]['hiresProfile'] = $newkey;
            $keys = ['label', 'firm_id', 'min', 'max', 'Salary', 'createtime', 'position', 'workadd', 'start_time', 'end_time', 'welfare', 'Firm', 'degree'];
            foreach ($keys as $item) {
                unset($model[$k][$item]);
            }
        }
        $this->success('成功', $model);
    }

    /**
     * @note 搜索人才详情
     */
    public function searchResume()
    {
        $position = $this->request->post('position_id', 0);
        $degree = $this->request->post('degree_id', 0);
        $sex = $this->request->post('sex', 1);
        $min = $this->request->post('min', '');
        $max = $this->request->post('max', '');
        $model = Resume::with(['degree', 'position', 'workTime'])
            ->where('position_id', '=', $position)
            ->where('degree_id', '=', $degree)
            ->where('sex', '=', $sex)
            ->whereBetween('worktime', [$min, $max])
            ->select()
            ->toArray();
        //处理性别字段
        foreach ($model as &$item) {
            $item['sex'] = $item['sex'] == 1 ? '男' : '女';
        }
        $this->success('成功', $model);
    }

    /**
     * @note 岗位筛选
     */
    public function searchHires()
    {
        $name = input('name', '');
        $position = input('position', '');
        if (empty($name) && !empty($position)) {
            $model = HiresPost::with('Firm')->where('position', $position)->select()->toArray();
            $this->success('成功', $model);
        } elseif (!empty($name) && empty($position)) {
            $companies = FirmProve::where('name', 'like', '%' . $name . '%')->select();
            $this->success('成功', $companies);
        }
    }

    //获取我的留言
    public function mess()
    {
        $id = input();
        //验证器验证数据
        $validate = new ResumeValidate();
        $result = $validate->scene('Mess')->check($id);
        if ($result !== true) {
            $this->success($validate->getError());
        }
        $model = LaborMess::where('user_id', $id['user_id'])->select()->toArray();
        $this->success('成功', $model);
    }

    //
    public function firmMess()
    {
        $data = $this->request->post();
        //验证器验证数据
        $validate = new ResumeValidate();
        $result = $validate->scene('firmMess')->check($data);
        if ($result !== true) {
            $this->success($validate->getError());
        }
        $textFiltering = Filtering::where('badword', $data['comments'])
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
        $firmMess = new FirmMess();
        $firmMess->user_id = $data['user_id'];
        $firmMess->users_id = $data['users_id'];
        $firmMess->content = $data['comments'];
        if ($firmMess->save()) {
            $this->success('成功');
        } else {
            $this->error('失败');
        }
    }

    public function getMess()
    {
        $id = input('users_id');
        $model = FirmMess::with('userId')->where(['status' => 1, 'users_id' => $id])->withoutField('createtime,updatetime')->order('createtime', 'desc')->select()->toArray();
        if (empty($model)) {
            $this->error('查无结果');
        }
        $this->success('成功', $model);
    }

    //培训报名
    public function application(Request $request)
    {
        $data = $this->request->post();

        $training = new TrainingRegister();
        //判断是否报名
        $model = $training->where('mobile', $data['mobile'])->select()->toArray();
        if (!empty($model)) {
            $this->error('你已经报名！');
        }
        $training->username = $data['name'];
        $training->mobile = $data['mobile'];
        $training->user_id = $request->user_id;
        $training->educat_id = $data['educat_id'];
        if ($training->save()) {
            $this->success('报名成功');
        } else {
            $this->error('报名失败');
        }
    }
}