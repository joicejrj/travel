<?php
$data = file_get_contents('php://input');
$array_data=get_object_vars(json_decode($data));

require_once __DIR__ . '/../config/db.php';

//echo "<pre>";
//print_r($array_data);

$phoneNumber=$array_data['number'];
$header_text=$array_data['header_text'];
$body_text=$array_data['body_text'];
$footer_text=$array_data['footer_text'];

$buttons=$array_data['buttons'];


$WHATSAPP_API_URL="https://web.jrjconnect.com/API_V2/Whatsapp/send_session/MzVyM0RHb2pOaUhyNVYydjlySHZxdz09";

    $rows = array_map(fn($item) => $item->title, $buttons);


    $data = [
        "type" => "quick_reply",
        "header" => $header_text,
        "body" => $body_text,
        "footer" => $footer_text,
        "sender_phone" => $phoneNumber,
        "buttons" => $rows,
    ];

    print_r($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $WHATSAPP_API_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    echo $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $sel_contact_id=mysqli_fetch_array(mysqli_query($mysqli, "select * from employees where phone='".$phoneNumber."'"));

    $contact_id=$sel_contact_id['id'];

    if($contact_id!='')
    {
        mysqli_query($mysqli, "INSERT INTO `whatsapp_message_logs` (`contacts_id`, `direction`, `message_body`, `interactive_reply_title`, `interactive_reply_description`) VALUES ('".$contact_id."', 'Outgoing', '".$body_text."', '', '')");
    }

?>