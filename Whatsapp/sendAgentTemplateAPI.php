<?php

require_once __DIR__ . '/../config/db.php';


$contact_id=$_POST['contact_id'];
$message=$_POST['message'];
$template_id=$_POST['template_id'];

$sel_contact_number=mysqli_fetch_array(mysqli_query($mysqli, "select * from employees where id='".$contact_id."'"));

$phoneNumber=$sel_contact_number['phone'];


    $WHATSAPP_API_URL="https://web.jrjconnect.com/API_V2/Whatsapp/send_template/MDNVZEZhZEVIMWdSelVXZ09XUHhKZz09";

    $data = [
        "type" => "template",
        "sender_phone" => $phoneNumber,
        "templateId" => $template_id,
        "templateLanguage" => "en"
    ];


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $WHATSAPP_API_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    echo $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    
    mysqli_query($mysqli, "INSERT INTO `whatsapp_message_logs` (`contacts_id`, `direction`, `message_body`, `interactive_reply_title`, `interactive_reply_description`) VALUES ('".$contact_id."', 'Outgoing', '".$message."', '', '')");

?>