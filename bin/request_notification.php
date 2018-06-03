<?php

//step： 0  订单提交OK
//status： 0： 初始状态 1: 订单提交OK  99: 第三方推送中  3: 第三方推送未成功  4: 第三方推送失败(到达上限) 5. 第三方推送成功

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'app.php');


function leadportalNotification($postfields){
    $leadportal = 'https://tl.leadportal.com/genericPostlead.php';
    //$leadportal = 'https://tl.leadportal.com/genericPostlead.php?Test_Lead=1&TYPE=19&s1=s1&s2=s2&s3=s3&s4=s4&s5=s5&User_Agent=User_Agent&SRC=test&Landing_Page=landing&IP_Address=75.2.92.149&Sub_ID=12&Pub_ID=12345&Optout=Optout&Unique_Identifier=Unique_Identifier&___pageid___=__pageid__&TCPA_Consent=Yes&TCPA_Language=TCPA_Language&universal_leadid=LeadiD_Token&xxTrustedFormCertUrl=TrustedFormCertUrl&First_Name=John&Last_Name=Doe&Address=123%20Main%20St.&City=Chicago&State=IL&Zip=60610&Primary_Phone=3125555076&Secondary_Phone=3125553713&County=County&Email=test@nags.us&property_ownership=Own&Shade=A%20Little%20Shade&Monthly_Electric_Bill=$0-$100&Utility_Provider=Utility_Provider';

    $rtn = array('post_url' => $leadportal, 'params' => $postfields, 'success' => true);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $leadportal);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
//    print_r($rtn);
//    exit(0);
    $result = curl_exec($ch);
    $rtn['response'] = $result;
    if(!preg_match('/<status>/', $result) || preg_match('/<status>Error<\/status>/', $result)){
        $rtn['success'] = false;
    }
    //print_r($rtn);
    //exit(0);
    curl_close ($ch);

    return $rtn;
}

function getPortalParams($type, $config, $data){
    $result = array();
    $defaultValue = $config['default_value'];
    $excludesParam = $config['excludes_param'];
    $postParam = $config['post_param'];
    foreach($postParam as $param){
        if(!array_key_exists($param, $excludesParam)){
            continue;
        }
        if(array_key_exists($param, $data)){
            $result[$param] = $data[$param];
        }elseif(array_key_exists($param, $defaultValue)){
            $result[$param] = $defaultValue[$param];
        }
    }
    return $result;
}

$pdo = $app['db'];
$solar_request_config = $app['solar_request_config'];
while(1){
    $now = time();
    $sth = $pdo->prepare("SELECT * FROM solar_request WHERE status in (?, ?)");
    $sth->execute(array(1, 3));
    $results = $sth->fetchAll(PDO::FETCH_ASSOC);
    $sth = $pdo->prepare('update solar set status = ?, update_date = now(), retried_times = retried_times + ? where id = ?');
    foreach ($results as $solar) {
        if($solar['status'] == 3 && $now < (strtotime($solar['update_date']) + $solar['retried_times'] * 3600)){
            continue;
        }

        $sth->execute(array(99, 0, $solar['id']));              //标记为处理中

        //print_r($solar);
        $type = $solar['type'];
        if(!array_key_exists($type, $solar_request_config)){
            $sth->execute(array(1000, 1, $solar['id']));   //type不存在
            continue;
        }
        $data = json_decode($solar['data'], true);
        $params = getPortalParams($solar['type'], $solar_request_config[$type], $data);
        $rtn = leadportalNotification($params);
        //print_r($rtn);
        if($rtn['success']){
            $sth->execute(array(5, 0, $solar['id']));        //标记为已成功
        }else{
            if($solar['retried_times'] >= 5){
                $sth->execute(array(4, 1, $solar['id']));    //失败，但是到达上限，标记为错误超过重试次数
            }else{
                $sth->execute(array(3, 1, $solar['id']));    //失败，但是未到上限，下次继续
            }
        }

        $statement = $pdo->prepare("insert into request_post_log (solar_id, success, request, response, insert_date) values (?, ?, ?, ?, now())");
        $statement->execute(array($solar['id'], $rtn['success'], json_encode(array($rtn['params'], $rtn['post_url'])), $rtn['response']));
        sleep(30);
    }
}
//print_r($results);

//print_r($app['config']);
