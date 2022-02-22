<?php
require(ROOT_PATH . "includes/erp_report/erp_new_user_function.php");

require(ROOT_PATH . "includes/erp_report/erp_user_buy_function.php");
require(ROOT_PATH . "includes/erp_report/erp_user_function.php");

function getAttachment($data, $header, $report_file_name){
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setactivesheetindex(0);
    $objSheet = $objPHPExcel->getActiveSheet();        //选取当前的sheet对象
    $objSheet->setTitle($report_file_name);     //对当前sheet对象命名
    $printData = array_merge($header,$data);
    $objSheet->fromArray($printData);
    //写excel
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');   //设定写入excel的类型
    global $excel_dir;
    $file_name = $excel_dir.$report_file_name.".xlsx";
    $objWriter->save($file_name);
    return $file_name;
}

function getXxlAttachment($data){
    $objPHPExcel = new PHPExcel();
    $i=0;
    foreach ($data as $key => $value) {
        if ($i>0) {
           //产生这个错误的原因是PHPExcel会自动创建第一个sheet，因此我们可以直接创建一个PHPEXCEL对象并且操作第一个sheet：$i>0需要手动创建！
           $objPHPExcel->createSheet();
        }
        $objPHPExcel->setactivesheetindex($i);
        $objSheet = $objPHPExcel->getActiveSheet();  
        $objSheet->setTitle($key);     //对当前sheet对象命名
        $keys = getKeyCol($value[0]);
        $printData = array_merge([$keys],$value);
        $objSheet->fromArray($printData);
        $i++;
    }
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');   //设定写入excel的类型
    global $excel_dir;
    $file_name = $excel_dir."xxl-cron_".date('Y-m-d H:i:s').".xlsx";
    $objWriter->save($file_name);
    //写excel
    return $file_name;
}

function getXxlAttachmentOut($data){
    $objPHPExcel = new PHPExcel();
    $i=0;
    foreach ($data as $key => $value) {
        if ($i>0) {
           //产生这个错误的原因是PHPExcel会自动创建第一个sheet，因此我们可以直接创建一个PHPEXCEL对象并且操作第一个sheet：$i>0需要手动创建！
           $objPHPExcel->createSheet();
        }
        $objPHPExcel->setactivesheetindex($i);
        $objSheet = $objPHPExcel->getActiveSheet();  
        $objSheet->setTitle($key);     //对当前sheet对象命名
        $keys = getKeyCol($value[0]);
        $printData = array_merge([$keys],$value);
        $objSheet->fromArray($printData);
        $i++;
    }
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');   //设定写入excel的类型
    // global $excel_dir;
    // $file_name = $excel_dir."xxl-cron_".date('Y-m-d H:i:s').".xlsx";
    $file_name = "xxl-cron_".date('Y-m-d H:i:s').".xlsx";
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$file_name.'"');
    header("Content-Disposition:attachment;filename={$file_name}.xlsx");
    header('Cache-Control: max-age=0');
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    $objWriter->save('php://output');
    //写excel
    return $file_name;
}

function getKeyCol($arr){
    $res = [];
    if (is_array($arr) && !empty($arr)) {
        $keys = array_keys($arr);
        foreach ($keys as $value) {
            $res[$value] = $value;
        }
        return $res;
    }
    return [];
}

function send_xxl_email($report_data, $is_content = 1, $send_email_ary = []){
    $attachment = getXxlAttachment($report_data);
    if ($is_content) {
        $content = getXxlContent($report_data);
    } else {
        $content = "内容见附件";
    }
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
    $mail->SetFrom($smtp_config['smtp_user_mail'], "erp_report");
    $mail->AddReplyTo($smtp_config['smtp_user_mail'], "erp_report");
    $mail->Subject = "xxl-cron_".date('Y-m-d H:i:s');
    $mail->MsgHTML($content);
    global $erp_report_send_to;
    if ( empty($send_email_ary)){
        $send_email_ary = $erp_report_send_to;
    }
    foreach($send_email_ary as $value){
        $mail->AddAddress($value, "erp");
    }
    if(is_file($attachment)){                   // 添加附件
        $mail->AddAttachment($attachment);
    }
    $state = $mail->Send();
    if($state==""){
        echo date("Y-m-d H:i:s") . "send failed,send to ".json_encode($erp_report_send_to).",content:{$content}";
    }else{
        echo date("Y-m-d H:i:s") . "send success,send to ".json_encode($erp_report_send_to);
    }
    if(file_exists($attachment)){
        $result = unlink($attachment);
    }
}

function getXxlContent($data_arr){
    $content = "";
    foreach ($data_arr as $key => $value) {
        $content .= " <div><b>----{$key}----</b><table border=1 cellspacing=0 ><tr>";
        $key_array = array_keys($value[0]);
        foreach($key_array as $column){
            $content .= "<td>".$column."</td>";
        }
        $content .= "</tr>";
        foreach ($value as $data) {
            $content .= (empty($data[$key_array[count($key_array)-1]]) || empty($data[$key_array[count($key_array)-2]]))?
            "<tr style='color:red;'>":"<tr>";
            foreach ($key_array as $key){
                $content .= "<td>{$data[$key]}</td>";
            }
            $content .= "</tr>";
        }
        $content .= "</table></div><br>";
    }
    return $content;
}

function getEmailContent($data_arr, $header, $report_file_name, $key_array){
    $content = "<div><b>{$report_file_name}</b><table border=1 cellspacing=0 ><tr>";
    foreach($header as $column){
        $content .= "<td>".$column."</td>";
    }
    $content .= "</tr>";
    // var_export($data_arr);die;
    foreach ($data_arr as $data) {
        $content .= "<tr>";
        foreach ($key_array as $key){
            $content .= "<td>{$data[$key]}</td>";
        }
        $content .= "</tr>";
    }
    $content .= "</table></div>";
    echo $content."\r\n";
    return $content;
}

function send_erp_email($title,$header,$key_array,$report_data, $is_content = 1, $send_email_ary = []){
    $attachment = getAttachment($report_data, $header, $title);
    if ($is_content) {
        $content = getEmailContent($report_data, $header, $title, $key_array);
    } else {
        $content = "内容见附件";
    }
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
    $mail->SetFrom($smtp_config['smtp_user_mail'], "erp_report");
    $mail->AddReplyTo($smtp_config['smtp_user_mail'], "erp_report");
    $mail->Subject = $title;
    $mail->MsgHTML($content);
    global $erp_report_send_to;
    if ( empty($send_email_ary)){
        $send_email_ary = $erp_report_send_to;
    }
    foreach($send_email_ary as $value){
        $mail->AddAddress($value, "wliu11");
    }
    if(is_file($attachment)){                   // 添加附件
        $mail->AddAttachment($attachment);
    }
    $state = $mail->Send();
    if($state==""){
        echo date("Y-m-d H:i:s") . "send failed,send to ".json_encode($erp_report_send_to).",content:{$content}";
    }else{
        echo date("Y-m-d H:i:s") . "send success,send to ".json_encode($erp_report_send_to);
    }
    if(file_exists($attachment)){
        $result = unlink($attachment);
    }

}
/**
 * 根据user分库分表规则将facility_id分组
 */
function getFacilityIdListByRds($user_list){
    $rds_list = array();
    foreach ($user_list as $user){
        if (empty($user['rds']) || empty($user['db'])) {
            $user['rds'] = 'default';
            $user['db'] = 'default';
        }
        $rds_list[$user['rds']][$user['db']][] = $user['facility_id'];
    }
    return $rds_list;
}