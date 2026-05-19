<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';

$data = file_get_contents('php://input');

$array_data=get_object_vars(json_decode($data));

//print_r($array_data);

$number=$array_data['number'];
$contact_name=$array_data['contact_name'];
$message_body=$array_data['message_body'];

$interactive_reply_id=$array_data['interactive_reply_id'];
$interactive_reply_title=$array_data['interactive_reply_title'];
$interactive_reply_description=$array_data['interactive_reply_description'];

$base_url='https://erp.jrjapp.com/Whatsapp/';


$sel_contact_id=mysqli_fetch_array(mysqli_query($mysqli, "select * from employees where phone='".$number."'"));

$contact_id=$sel_contact_id['id'];

if($contact_id=='' && $number!='')
{
    $emp_id=rand(1000,9999);
    $stmt =mysqli_query($mysqli, "INSERT INTO `employees` (`agent_id`, `emp_id`, `name`, `company`, `phone`) VALUES ('2','".$emp_id."','".$contact_name."','".$contact_name."','".$number."')");
    $contact_id =mysqli_insert_id($mysqli);
}

if(isset($contact_id)&&$contact_id!='')
{
mysqli_query($mysqli, "INSERT INTO `whatsapp_message_logs` (`contacts_id`, `direction`, `message_body`, `interactive_reply_title`, `interactive_reply_description`) VALUES ('".$contact_id."', 'Incoming', '".$message_body."', '".$interactive_reply_title."', '".$interactive_reply_description."')");
}


if($number!='')
{
$sql_session = mysqli_query($mysqli, "SELECT id, session_started FROM whatsapp_customer_session WHERE phone = '".$number."' LIMIT 1");

$num_session=mysqli_num_rows($sql_session);

if ($num_session == 0) 
{
    $mysqli->query("INSERT INTO whatsapp_customer_session (contact_id, phone, session_started) VALUES ('".$contact_id."','".$number."', NOW())");
} 
else 
{
    $row_session = mysqli_fetch_assoc($sql_session);
    $session_started = strtotime($row_session['session_started']);
    $now = time();

    if (($now - $session_started) > 24 * 3600) 
    {
        $id = (int)$row_session['id'];
        mysqli_query($mysqli, "UPDATE whatsapp_customer_session SET session_started = NOW() WHERE id = $id");
    }
}
}


?>