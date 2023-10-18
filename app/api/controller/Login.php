<?php


namespace app\api\controller;

use app\BaseController;
use app\api\model\SendSms;
use app\api\model\UserModel;
use app\api\validate\LoginValidate;
use Firebase\JWT\JWT;
use think\Request;

require root_path() . "extend/aliyun-dysms-php-sdk/api_demo/SmsDemo.php";


class Login extends BaseController
{
    /**
     * @note 注册和登录
     * @return
     */
    public function SendSms()
    {
        // 接收手机号
        $mobile = $this->request->param('mobile');
        try {
            // 验证发送时间
            $sms = new SendSms();
            $lastSendTime = $sms
                ->where('mobile', '=', $mobile)
                ->order('createtime', 'desc')
                ->value('createtime');
//            halt($lastSendTime);
            // 比较时间差值，判断是否需要等待
            if (!empty($lastSendTime)) {
                $nowTime = time();
                $timeDiff = $nowTime - $lastSendTime;
                if ($timeDiff < 300) {
                    $waitTime = 300 - $timeDiff;
                    return json([
                        'code' => 1,
                        'msg' => "每 5 分钟只能发送一次验证码，请等待 ' . $waitTime . ' 秒后再重新发送"
                    ]);
                }
            }
//             发送验证码
            $code = rand(111111, 999999);
            $SendSms = new SendSms();
            $smsData = $SendSms->save([
                'mobile' => $mobile,
                'code' => $code,
                'expiretime' => strtotime('+5 minutes')
            ]);
            // 模拟发送验证码
            if ($smsData) {
                return json([
                    'code' => 1,
                    'msg' => "验证码发送成功",
                    'data' => ['code' => $code]
                ]);
            } else {
                $this->error('验证码发送失败');
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @note 注册
     */
    public function register(Request $request)
    {
        $data = $this->request->param();
        //验证器验证
        $validate = new LoginValidate();
        $result = $validate->scene('register')->check($data);
        if ($result !== true) {
            $this->success($validate->getError());
        }
        //判断手机号是否注册
        $Model = new UserModel();
        $mobile = $Model
            ->where('mobile', '=', $data['mobile'])
            ->value('mobile');
        if ($mobile == $data['mobile']) {
            $this->error('该手机号已存在');
        }
        //判断两次密码输入是否一样
        if ($data['password'] != $data['passwords']) $this->error('两次密码输入不一致');

        //判断验证码是否正确,先查询
        $SmsModel = new SendSms();
        $SmsCode = $SmsModel
            ->where('mobile', '=', $data['mobile'])
            ->order('createtime', 'desc')
            ->value('code');
        //判断验证码是否过期
        $SmsData = $SmsModel
            ->where('mobile', '=', $data['mobile'])
            ->order('createtime', 'desc')
            ->value('expiretime');
        //对比是否过期
        if (time() > $SmsData) {
            //如果过期，就把过期的验证码删除
            $SmsModel
                ->where('expiretime', '<', time())
                ->delete();
            $this->error('验证码无效');
        }
        if ($SmsCode == $data['code']) {
            $UserModel = new UserModel();
            $UserModel->save([
                'type' => $data['type'],
                'mobile' => $data['mobile'],
                'avatar' =>'http://qiniu.gaowa.love/652cdec627bb51697439430.JPG',
                'password' => md5($data['password']),
                'username' => $data['mobile'],
                'login_ip' => $request->ip(),
                'createtime' => time()
            ]);
            $this->success('注册成功');
        } else {
            $this->error('验证码错误');
        }

    }

    /**
     * @note 账号密码登录
     */
    public function login(Request $request)
    {
        $data = $this->request->post();
        try {
            validate(LoginValidate::class)->scene('login')->check($data);
            $model = new UserModel();
            //  1.判断用户是否注册
            $user = $model->field('id,mobile,password,token')->where('username', $data['mobile'])->findOrEmpty()->toArray();
            if (empty($user))
                return json([
                    'code' => 1,
                    'msg' => "用户不存在,请先注册"
                ]);
            //  2.验证账号密码
            if (md5($data['password']) !== $user['password']) return json([
                'code' => 1,
                'msg' => "密码错误"
            ]);
            //  3.签发token
            $Model = new UserModel();
            $userData = $Model->field('id')
                ->where('mobile', '=', $data['mobile'])
                ->select()
                ->toArray();
            $jwtContent = [
                // 签发人，这里采用当前站点域名
                'iss' => request()->domain(),
                // 签发时间，当前时间戳
                'iat' => time(),
                // 到期时间，1天后
                'exp' => time() + 86400,
                // 自定义数据
                'data' => [
                    'user_id' => $userData,
                    'mobile' => $data['mobile']
                ]
            ];
            // 使用 HS256 算法，生成 token 。
            $token = [
                'token' => JWT::encode($jwtContent, config('app.jwtKey'), 'HS256')
            ];
            $smsLogModel = new SendSms();
            $smsLogModel->deleteSmsCode($data['mobile']);
            // 更新 token 字段
            $user = $Model->where('mobile', $data['mobile'])->find();
            $user->token = JWT::encode($jwtContent, config('app.jwtKey'), 'HS256');
            //更新ip字段
            $user->loginip = $request->ip();
            $user->save();
            return json([
                'code' => 1,
                'msg' => "登录成功！",
                'data' => $token
            ]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    //判断用户类型
    public function type(Request $request)
    {
//        halt(123);
        $model = new UserModel();
        $data = $model->where('id', $request->user_id)->value('type');
        $this->success('', $data);
    }

    //验证码登录
    public function codeLogin(Request $request)
    {
        $data = $this->request->param();
        //验证器验证数据
        $validate = new LoginValidate();
        $result = $validate->scene('code')->check($data);
        if ($result !== true) {
            $this->success($validate->getError());
        }
        //判断验证码是否正确,先查询
        $SmsModel = new SendSms();
        $SmsCode = $SmsModel
            ->where('mobile', '=', $data['mobile'])
            ->order('createtime', 'desc')
            ->value('code');
        if ($SmsCode != $data['code']) {
            $this->error('验证码错误');
        }
        $Model = new UserModel();
        //判断用户是否注册
        $isRes = $Model->where('username', '=', $data['mobile'])->value('mobile');
        if (empty($isRes)) {
            $this->error('用户不存在 请先注册');
        }
        //判断是否选择了角色
        $isRole = $Model->where('username', '=', $data['mobile'])->value('type');
        if ($isRole == 0) {
            $this->error('该用户未选择角色！');
        }
        //判断验证码是否过期
        $SmsData = $SmsModel
            ->where('mobile', '=', $data['mobile'])
            ->order('createtime', 'desc')
            ->value('expiretime');
        //对比是否过期
        if (time() > $SmsData) {
            //如果过期，就把过期的验证码删除
            $SmsModel
                ->where('expiretime', '<', time())
                ->delete();
            $this->error('验证码过期');
        }
        //签发token
        $userData = $Model->field('id')
            ->where('mobile', '=', $data['mobile'])
            ->select()
            ->toArray();
        $jwtContent = [
            // 签发人，这里采用当前站点域名
            'iss' => request()->domain(),
            // 签发时间，当前时间戳
            'iat' => time(),
            // 到期时间，1天后
            'exp' => time() + 86400,
            // 自定义数据
            'data' => [
                'user_id' => $userData,
                'mobile' => $data['mobile']
            ]
        ];
        // 使用 HS256 算法，生成 token 。
        $token = [
            'token' => JWT::encode($jwtContent, config('app.jwtKey'), 'HS256')
        ];
        if ($SmsCode == $data['code']) {
            //登录成功后删除code
            $smsLogModel = new SendSms();
            $smsLogModel->deleteSmsCode($data['mobile']);
            // 更新 token 字段
            $user = $Model->where('mobile', $data['mobile'])->find();
            $user->token = JWT::encode($jwtContent, config('app.jwtKey'), 'HS256');
            //更新ip字段
            $user->loginip = $request->ip();
            $user->save();
            $this->success('登录成功', $token);
        } else {
            $this->error('登录失败');
        }
    }

    //忘记密码
    public function backPassword()
    {
        $data = $this->request->param();
        //验证器验证数据
        $validate = new LoginValidate();
        $result = $validate->scene('register')->check($data);
        if ($result !== true) {
            $this->success($validate->getError());
        }
        //判断两次密码输入是否一样
        if ($data['password'] != $data['passwords']) {
            $this->error('两次密码输入不一致');
        }
        //判断验证码是否正确,先查询
        $SmsModel = new SendSms();
        $SmsCode = $SmsModel
            ->where('mobile', '=', $data['mobile'])
            ->order('createtime', 'desc')
            ->value('code');
        if ($SmsCode != $data['code']) {
            $this->error('验证码错误');
        }
        //判断验证码是否过期
        $SmsData = $SmsModel
            ->where('mobile', '=', $data['mobile'])
            ->order('createtime', 'desc')
            ->value('expiretime');
        //对比是否过期
        if (time() > $SmsData) {
            //如果过期，就把过期的验证码删除
            $SmsModel
                ->where('expiretime', '<', time())
                ->delete();
            $this->error('验证码过期');
        }
        $Model = new UserModel();
        //判断用户是否注册
        $isRes = $Model->where('username', '=', $data['mobile'])->value('mobile');
        if (empty($isRes)) {
            $this->error('用户不存在 请先注册');
        }
        //开始修改密码
        $password = $Model->where('mobile', '=', $data['mobile'])->update(['password' => md5($data['password'])]);
//        halt($password);
        if (empty($password)) {
            $this->success('密码修改成功！');
        } else {
            $this->error('密码修改失败');
        }
    }
}