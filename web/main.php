<?php

//step： 0  订单提交OK
//status： 0： 初始状态 1: 订单提交OK  99: 第三方推送中  3: 第三方推送未成功  4: 第三方推送失败(到达上限) 5. 第三方推送成功

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'app.php');

$type = $_REQUEST['TYPE'];
$solar_request_config = $app['solar_request_config'];
if(!in_array($type, array_keys($solar_request_config))){
    exit(0);
}

$request = $_REQUEST;
$request['IP_Address'] = getRemoteIp();
$log = '/var/log/webform2post/request_v2_' . date("Y-m-d");
$insertDate = date('Y-m-d H:i:s');
file_put_contents($log, '['.$insertDate.'] TYPE='. $type . ' ' . json_encode($request)."\n",FILE_APPEND);

$pdo = $app['db'];
$redirect_url = isset($_REQUEST['Redirect_URL']) ? $_REQUEST['Redirect_URL'] : 'http://solar-rebate.com/thanks.html';

$sql = 'insert into solar_request (`type`,`data`,`step`, `status`, `insert_date`,`update_date`) values( ?, ?, ?, ?, ?, ?)';
#error_log($sql);
$sth = $pdo->prepare($sql);
$sth->execute(array($type, json_encode($request), 0, 1, $insertDate, $insertDate));

header('location: '.$redirect_url);
exit();
