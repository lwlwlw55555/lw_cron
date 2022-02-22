<?php
function post_data($url,$data){
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt ($ch, CURLOPT_TIMEOUT,10);
	$ret = curl_exec ( $ch );
	$curl_errno = curl_errno($ch);
	$curl_error = curl_error($ch);
	curl_close($ch);

	if ($curl_errno > 0) {
		echo (date("Y-m-d H:i:s") . "[curl]][error] {$curl_errno}:{$curl_error} \r\n");
		return null;
	} else {
		var_dump($ret);
		return $ret;
	}
}

function post_json_data($url, $data) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json; charset=utf-8',
		'Content-Length: ' . strlen($data))
	);
	ob_start();
	curl_exec($ch);
	$return_content = ob_get_contents();
	ob_end_clean();
	return $return_content;
}

function http_get_data($url) {
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	$output = curl_exec ( $ch );
	curl_close ( $ch );
	return $output;
}

function send_email($send_to,$title,$content,$attachment){
	global $smtp_config;
	$mail    = new PHPMailer();
	$mail->CharSet    = 'UTF-8';
	$mail->IsSMTP();
	$mail->SMTPAuth   = true;
	$mail->SMTPSecure = '';

	$mail->Host       = $smtp_config['smtp_server'];  // SMTP 服务器
	$mail->Port       = $smtp_config['smtp_server_port'];  // SMTP服务器的端口号
	$mail->Username   = $smtp_config['smtp_user'];  // SMTP服务器用户名
	$mail->Password   = $smtp_config['smtp_pass'];  // SMTP服务器密码
	$mail->SetFrom($smtp_config['smtp_user_mail'], "qxu");
	$mail->AddReplyTo($smtp_config['smtp_user_mail'], "qxu");
	$mail->Subject    = $title;
	$mail->MsgHTML($content);
	foreach($send_to as $value){
		$mail->AddAddress($value, "qxu");
	}
	if(is_file($attachment)){                   // 添加附件
		$mail->AddAttachment($attachment);
	}
	$state = $mail->Send();
	if($state==""){
		echo date("Y-m-d H:i:s") . "send failed,send to ".json_encode($send_to).",content:{$content}";
	}else{
		echo date("Y-m-d H:i:s") . "send success,send to ".json_encode($send_to);
	}
}

function send_email_arr($send_to,$title,$content,$arr){//多附件发送邮件
	global $smtp_config;
	$mail    = new PHPMailer();
	$mail->CharSet    = 'UTF-8';
	$mail->IsSMTP();
	$mail->SMTPAuth   = true;
	$mail->SMTPSecure = '';

	$mail->Host       = $smtp_config['smtp_server'];  // SMTP 服务器
	$mail->Port       = $smtp_config['smtp_server_port'];  // SMTP服务器的端口号
	$mail->Username   = $smtp_config['smtp_user'];  // SMTP服务器用户名
	$mail->Password   = $smtp_config['smtp_pass'];  // SMTP服务器密码
	$mail->SetFrom($smtp_config['smtp_user_mail'], "qxu");
	$mail->AddReplyTo($smtp_config['smtp_user_mail'], "qxu");
	$mail->Subject    = $title;
	$mail->MsgHTML($content);
	foreach($send_to as $value){
		$mail->AddAddress($value, "qxu");
	}
	foreach($arr as $file){
		if(is_file($file)){                   // 添加附件
			$mail->AddAttachment($file);
		}
	}
	$state = $mail->Send();
	if($state==""){
		echo date("Y-m-d H:i:s") . "send failed,send to ".json_encode($send_to).",content:{$content}";
	}else{
		echo date("Y-m-d H:i:s") . "send success,send to ".json_encode($send_to);
	}
}

function getRedis ($database = null) {
    global $redis_config, $redis;
    if ($redis) {
    	return $redis;
    }
    require_once 'predis-1.1/autoload.php';
    $redis = new Predis\Client([
          'host' => $redis_config['host'],
          'port' => $redis_config['port']
    ]);
    if ($redis_config['auth']) {
        $redis->auth($redis_config['auth']);
    }
    if ($database) {
    	$redis->select($database);
    }elseif ($redis_config['database']) {
        $redis->select($redis_config['database']);
    }
    return $redis;
}

function selectRdsByShopId($shop_id, $is_force = 0) {
    global $sync_db, $db_user;
    $party_id = $sync_db->getOne("select party_id from shop where shop_id = {$shop_id}");
    $user = $db_user->getRow("select rds, db from user where party_id = {$party_id}");
    selectRds($user['rds'], $user['db'], $is_force);
}

function selectRdsByFacilityId($facility_id) {
    global $sync_db, $db_user;
    // $party_id = $sync_db->getOne("select party_id from shop where shop_id = {$shop_id}");
    $user = $db_user->getRow("select rds, db from user where facility_id = {$facility_id}");
    selectRds($user['rds'], $user['db']);
}

function selectRds($user_rds = null, $user_db = null, $is_force = 0) {
    global $db, $piece_list, $db_conf, $db_map;
    
    if (! $user_rds || $user_rds == 'default' || ! $user_db || $user_db == 'default') {
        $user_rds = 'default';
        $user_db = null;
    } 

    if (! $is_force &&  $user_db && isset($db_map[$user_db])) {
        $db = $db_map[$user_db];
    } elseif ($user_rds == 'default') {
        $db = null;
    } else {
        if (! isset($piece_list[$user_rds])) {
            throw new Exception("unknown user_rds:{$user_rds}");
        }
        $config = $piece_list[$user_rds];
		if ($user_db) {
            $config['name'] = $user_db;
		}
        $db = ClsPdo::getInstance($config);
    }
    if ($user_db) {
		$db_map[$user_db] = $db;
	}

}

function selectRouteRds($user_route_rds = null, $user_route_db = null) {
    global $route_drds_db, $route_rds_list, $route_db_conf, $route_db_map;
    
    if (! $user_route_rds || $user_route_rds == 'default' || ! $user_route_db || $user_route_db == 'default') {
        $user_route_rds = 'default';
        $user_route_db = null;
    } 

    if (isset($route_db_map[$user_route_rds])) {
        $route_drds_db = $route_db_map[$user_route_rds];
    } elseif ($user_route_rds == 'default') {
        $route_drds_db = ClsPdo::getInstance($route_db_conf);
    } else {
        if (! isset($route_rds_list[$user_route_rds])) {
            throw new Exception("unknown user_route_rds:{$user_route_rds}");
        }
        $route_drds_db = ClsPdo::getInstance($route_rds_list[$user_route_rds]);
    }

    if ($user_route_db) {
        $route_drds_db->query("use {$user_route_db}");
    }
    $route_db_map[$user_route_rds] = $route_drds_db;

}


?>
