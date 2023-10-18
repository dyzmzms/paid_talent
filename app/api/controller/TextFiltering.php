<?php


namespace app\api\controller;

use app\BaseController;
use app\api\model\Filtering;
class TextFiltering extends BaseController
{
      public function filtering()
      {
          $data =  $this->request->param('text','');
          $textFiltering = Filtering::where('badword', $data)
              ->select()
              ->toArray();
          $text = [''];
          foreach ($textFiltering as $item) {
              // 处理敏感词
              $text = $item['badword'];
              $replacement = mb_substr($text, 0, 1) . str_repeat('*', mb_strlen($text) - 1); // 使用星号替换除第一个字以外的部分

              $text = str_replace($text, $replacement, $text);
          }
          if (!empty($textFiltering)){
              $this->error('不许说脏话',$text);
          }
      }
}