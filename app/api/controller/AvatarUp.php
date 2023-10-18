<?php


namespace app\api\controller;


use app\BaseController;
//require root_path().'vendor\qiniu\autoload.php';
//halt(root_path().'vendor\qiniu\autoload.php');
use Qiniu\Auth;
// 引入上传类
use Qiniu\Storage\UploadManager;
class AvatarUp extends BaseController
{
      //头像上传

      public function upload()
      {
          $file = $this->request->file('image');
          $extension = $file->getOriginalExtension(); // 获取文件后缀
          $path = $file->getPathname();
          // 需要填写你的 Access Key 和 Secret Key
          $accessKey ="MXLREcC35mfL9YboRK3bPInQllas_pj2WV-qkMoF";
          $secretKey = "jL85KCOnOOkQOCNmjPPh_Swl6njCfQKrLwlyj2Oe";
          $bucket = "wudixiaoyang";
          // 构建鉴权对象
          $auth = new Auth($accessKey, $secretKey);
          // 生成上传 Token
          $token = $auth->uploadToken($bucket);
          // 要上传文件的本地路径
          $filePath = $path;
          // 上传到存储后保存的文件名
          $key = uniqid().time().'.'.$extension;
          // 初始化 UploadManager 对象并进行文件的上传。
          $uploadMgr = new UploadManager();
          // 调用 UploadManager 的  putFile 方法进行文件的上传。
          list($ret, $err) = $uploadMgr->putFile
          ($token, $key, $filePath, null, 'application/octet-stream', true, null, 'v2');
          $data = [
              'url' => 'http://qiniu.gaowa.love/'.$key,
          ];
               $this->success('上传成功',$data);
      }
}