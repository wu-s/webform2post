<?php

//step： 0  订单提交OK
//status： 0： 初始状态 1: 订单提交OK  99: 第三方推送中  3: 第三方推送未成功  4: 第三方推送失败(到达上限) 5. 第三方推送成功

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'app.php');

function createModel($params){
    $map = get_portal_param_mapping();
    $rtn = array();
    foreach($map as $key => $value){
        if(array_key_exists($key, $params)){
            $rtn[$value] = $params[$key];
        }else{
            $rtn[$value] = '';
        }
    }
    return $rtn;
}

file_put_contents('/var/log/webform2post/request_'.date("Y-m-d"), '['.date('Y-m-d H:i:s').'] '.json_encode($_REQUEST)."\n",FILE_APPEND);

$pdo = $app['db'];
$redirect_url = isset($_REQUEST['Redirect_URL']) ? $_REQUEST['Redirect_URL'] : 'http://solar-rebate.com/thanks.html';
$rtn = createModel($_REQUEST);
$rtn['step'] = 0;
$rtn['status'] = 1;

$cols = join('`,`', array_keys($rtn));
$t = array();
$vals = join(',', array_pad($t, count($rtn), '?'));
#error_log($cols);
#error_log($vals);
$sql = 'insert into solar (`'.$cols.'`,`insert_date`,`update_date`) values('.$vals.', now(), now())';
#error_log($sql);
$sth = $pdo->prepare($sql);
$sth->execute(array_values($rtn));

header('location: '.$redirect_url);
exit();
