<?php

define('ENVI_BASE_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('ENVI_ROOT_DIR', realpath(ENVI_BASE_DIR.'..'.DIRECTORY_SEPARATOR));

date_default_timezone_set('UTC');

$app = array();
$app['config'] = require(ENVI_ROOT_DIR.'/config/config.php');
$app['solar_request_config'] = require(ENVI_ROOT_DIR.'/config/solar_request.php');

$pdo_config = $app['config']['PDO'];
$app['db'] = new PDO('mysql:host='.$pdo_config['database_host'].';dbname='.$pdo_config['database_name'].';port='.$pdo_config['database_port'].';charset='.$pdo_config['charset'], $pdo_config['database_user'], $pdo_config['database_password']);
$app['db']->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

function get_portal_param_mapping(){
    return array(
        'TYPE' => 'type',
        'Test_Lead' => 'test_lead',
        'Skip_XSL' => 'skip_xsl',
        'Match_With_Partner_ID' => 'match_with_partner_id',
        'Redirect_URL' => 'redirect_url',
        's1' => 's1',
        's2' => 's2',
        's3' => 's3',
        's4' => 's4',
        's5' => 's5',
        'User_Agent' => 'user_agent',
        'SRC' => 'src',
        'Landing_Page' => 'landing_page',
        'IP_Address' => 'ip_address',
        'Sub_ID' => 'sub_id',
        'Pub_ID' => 'pub_id',
        'Optout' => 'optout',
        'Unique_Identifier' => 'unique_identifier',
        '___pageid___' => 'pageid',
        'TCPA_Consent' => 'tcpa_consent',
        'TCPA_Language' => 'tcpa_language',
        'universal_leadid' => 'universal_leadid',
        'xxTrustedFormCertUrl' => 'xx_trusted_form_cert_url',
        'First_Name' => 'first_name',
        'Last_Name' => 'last_name',
        'Address' => 'address',
        'City' => 'city',
        'State' => 'state',
        'Zip' => 'zip',
        'Primary_Phone' => 'primary_phone',
        'Secondary_Phone' => 'secondary_phone',
        'County' => 'county',
        'Email' => 'email',
        'property_ownership' => 'property_ownership',
        'Shade' => 'shade',
        'Monthly_Electric_Bill' => 'monthly_electric_bill',
        'Utility_Provider' => 'utility_provider',
    );
}

function getRemoteIp(){
    $rtn = '';
    if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        $ips=explode (', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
        for ($i=0; $i < count($ips); $i++){
            if(!preg_match('/^(10|172\.16|192\.168)\./' , $ips[$i])) {
                $rtn = $ips[$i];
                break;
            }
        }
    }
    return $rtn;
}

global $app;

