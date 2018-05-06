<?php
$accessToken = getenv('LINE_CHANNEL_ACCESS_TOKEN');
$pCloud_UID = getenv('PCLOUD_UID');
$pCloud_Pass = getenv('PCLOUD_PASS');

//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$jsonObj = json_decode($json_string);
//メッセージIDの取得
$messageId = $jsonObj->{"events"}[0]->{"message"}->{"id"};
//メッセージ種別の取得
$type = $jsonObj->{"events"}[0]->{"message"}->{"type"};

//画像の自動保存
if($type == "image"){
  $picture_filename = strval(floor(microtime(true)*1000))."_".substr(md5($messageId),0,8).".jpg";
  $picture_opts = array(
    'http'=>array(
      'method'=>"GET",
      'header'=>"Authorization: Bearer ".$accessToken."\r\n"
    )
  );
  $picture_context = stream_context_create($picture_opts);
  $img_raw = file_get_contents("https://api.line.me/v2/bot/message/".$messageId."/content", false, $picture_context);
  $curl_img = curl_init("https://webdav.pcloud.com/LINE/".$picture_filename);
  curl_setopt($curl_img, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($curl_img, CURLOPT_HTTPHEADER, array('Authorization: Basic '.base64_encode($pCloud_UID.':'.$pCloud_Pass).'\r\nContent-Type: application/octet-stream\r\nContent-Length: '.strlen($img_raw).'\r\n'));
  curl_setopt($curl_img, CURLOPT_POSTFIELDS, $img_raw);
  $img_result = curl_exec($curl_img);
  curl_close($curl_img);
}
exit;