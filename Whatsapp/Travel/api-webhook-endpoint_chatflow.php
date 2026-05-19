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



if(isset($interactive_reply_title)&&$interactive_reply_title=='New Booking') 
{
        $list = [
            [
                "id" => "Yes",
                "title" => "Yes",
                "description" => ""
            ],
            [
                "id" => "No",
                "title" => "No",
                "description" => ""
            ],
            [
                "id" => "Back",
                "title" => "Back",
                "description" => ""
            ]
        ];

        $postData=['number' => $number,'header_text' => 'Choose an option','body_text' => 'Would you like to use this same number for booking?','footer_text' => '','list_button_name' => 'Select','list' => $list];

        $postData_json = json_encode($postData, true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url.'sendListMessageAPI.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
            echo $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

}

else if(isset($interactive_reply_title)&&$interactive_reply_title=='Yes')
{
        $list = getNextFiveDays();

        $postData=['number' => $number,'header_text' => 'Travel Date','body_text' => 'Please choose a Date [At this step, we need to connect with your CRM. Currently, we are displaying some dummy data]','footer_text' => '','list_button_name' => 'Date','list' => $list];

        $postData_json = json_encode($postData, true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url.'sendListMessageAPI.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
            echo $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

}

else if(isset($interactive_reply_description)&&$interactive_reply_description=='Date')
{

     $list = [
            [
                "id" => "LHR",
                "title" => "LHR",
                "description" => "Departure Airport"
            ],
            [
                "id" => "LCY",
                "title" => "LCY",
                "description" => "Departure Airport"
            ],
            [
                "id" => "TRV",
                "title" => "TRV",
                "description" => "Departure Airport"
            ],
            [
                "id" => "LTN",
                "title" => "LTN",
                "description" => "Departure Airport"
            ]
        ];

        $postData=['number' => $number,'header_text' => 'Departure Airport','body_text' => 'Please select Departure Airport','footer_text' => '','list_button_name' => 'Departure Airport','list' => $list];

        $postData_json = json_encode($postData, true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url.'sendListMessageAPI.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
            echo $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);   

}

else if(isset($interactive_reply_description)&&$interactive_reply_description=='Departure Airport')
{

        $list = [
            [
                "id" => "LHR",
                "title" => "LHR",
                "description" => "Arrival Airport"
            ],
            [
                "id" => "LCY",
                "title" => "LCY",
                "description" => "Arrival Airport"
            ],
            [
                "id" => "TRV",
                "title" => "TRV",
                "description" => "Arrival Airport"
            ],
            [
                "id" => "LTN",
                "title" => "LTN",
                "description" => "Arrival Airport"
            ]
        ];

        $postData=['number' => $number,'header_text' => 'Arrival Airport','body_text' => 'Please select Arrival Airport','footer_text' => '','list_button_name' => 'Arrival Airport','list' => $list];

        $postData_json = json_encode($postData, true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url.'sendListMessageAPI.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
            echo $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);   

}

else if(isset($interactive_reply_description)&&$interactive_reply_description=='Arrival Airport')
{

        $list = [
            [
                "id" => "1",
                "title" => "1",
                "description" => "No of Passengers"
            ],
            [
                "id" => "2",
                "title" => "2",
                "description" => "No of Passengers"
            ],
            [
                "id" => "3",
                "title" => "3",
                "description" => "No of Passengers"
            ],
            [
                "id" => "4",
                "title" => "4",
                "description" => "No of Passengers"
            ],
            [
                "id" => "5",
                "title" => "5",
                "description" => "No of Passengers"
            ],
            [
                "id" => "bulk",
                "title" => "Bulk Booking",
                "description" => "No of Passengers"
            ]
        ];

        $postData=['number' => $number,'header_text' => 'No of Passengers','body_text' => 'Please select No of Passengers','footer_text' => '','list_button_name' => 'Passengers','list' => $list];

        $postData_json = json_encode($postData, true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url.'sendListMessageAPI.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
            echo $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);   

}

else if(isset($interactive_reply_description)&&$interactive_reply_description=='No of Passengers')
{

        $list = [
            [
                "id" => "fgt1",
                "title" => "Flight 1",
                "description" => "Available Flights"
            ],
            [
                "id" => "fgt2",
                "title" => "Flight 2",
                "description" => "Available Flights"
            ],
            [
                "id" => "fgt3",
                "title" => "Flight 3",
                "description" => "Available Flights"
            ]
        ];

        $postData=['number' => $number,'header_text' => 'Available Flights','body_text' => 'Please select Flight','footer_text' => '','list_button_name' => 'Flights','list' => $list];

        $postData_json = json_encode($postData, true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url.'sendListMessageAPI.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
            echo $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);   

}

else if(isset($interactive_reply_description)&&$interactive_reply_description=='Available Flights')
{
       $message = "Please Review the travel details and Price Details.\n";
       $message .= "[Here, we can display a summary of travel details and price details either from your CRM or from the data collected in the chat above.]";

            $postData=['number' => $number,'message' => $message];

            $postData_json = json_encode($postData, true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url.'sendTextMessageAPI.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
            echo $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $button_actions = [
            [
                "id" => "Proceed",
                "title" => "Proceed",
            ],
            [
                "id" => "Cancel",
                "title" => "Cancel",
            ],
            [
                "id" => "Agent",
                "title" => "Connect with Agent",
            ]
                ];

                $postData=['number' => $number,'header_text' => 'Proceed','body_text' => 'Procced with booking?','footer_text' => '','buttons' => $button_actions];

                $postData_json = json_encode($postData, true);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $base_url.'sendButtonMessageAPI.php');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
                echo $response = curl_exec($ch);
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

}

else if(isset($interactive_reply_title)&&$interactive_reply_title=='Proceed')
{
        $message = "Thank you for your Booking\n";
       $message .= "Payment link sent to your mobile number. please pay for confirm Booking.";

            $postData=['number' => $number,'message' => $message];

            $postData_json = json_encode($postData, true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url.'sendTextMessageAPI.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
            echo $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $button_actions = [
            [
                "id" => "Agent",
                "title" => "Connect with Agent",
            ],
            [
                "id" => "Back",
                "title" => "Back",
            ]
            ];

                $postData=['number' => $number,'header_text' => 'Thank you','body_text' => 'Is there anything else I can help you with?','footer_text' => '','buttons' => $button_actions];

                $postData_json = json_encode($postData, true);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $base_url.'sendButtonMessageAPI.php');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
                echo $response = curl_exec($ch);
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);


$res =mysqli_query($mysqli, "SELECT * FROM whatsapp_message_logs WHERE contacts_id = '".$contact_id."' AND direction = 'Incoming' ORDER BY id DESC LIMIT 1,7");
$rows = [];
while ($r = mysqli_fetch_assoc($res)) 
{
    $rows[] = $r;
}
$rows = array_reverse($rows);

$task_description = "";
$lines = [];

foreach ($rows as $row) 
{
    $title = trim($row['interactive_reply_title']);
    $desc  = trim($row['interactive_reply_description']);
    $body  = trim($row['message_body']);

    if ($title != "") 
    {
        $label = $title;
        $value = ($desc != "") ? $desc : $body;
    } 
    else 
    {
        $label = "";
        $value = $body;
    }

    if (strcasecmp($desc, "Available Flights") == 0) 
    {
        $value = "Selected flight";
    }

    if ($value != "") 
    {
        $lines[] = $value . ": " . $label;
    }
    else if ($label == "Yes") 
    {
    }  
    else 
    {
        $lines[] = $label;
    }
}

$task_description = implode("\n", $lines);

$cur_time=date('Y-m-d H:i:s');

mysqli_query($mysqli, "INSERT INTO `employees_reminders` (`employee_id`, `reminder_at`, `note`) VALUES ('".$contact_id."', '".$cur_time."', '".$task_description."')");

}

else if(isset($interactive_reply_title)&&$interactive_reply_title=='Cancel')
{
        $button_actions = [
            [
                "id" => "Agent",
                "title" => "Connect with Agent",
            ],
            [
                "id" => "Back",
                "title" => "Back",
            ]
            ];

                $postData=['number' => $number,'header_text' => 'Connect with Agent','body_text' => 'Our Agent will assit you','footer_text' => '','buttons' => $button_actions];

                $postData_json = json_encode($postData, true);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $base_url.'sendButtonMessageAPI.php');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
                echo $response = curl_exec($ch);
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

}

else if(isset($interactive_reply_title)&&$interactive_reply_title=='No')
{
        $button_actions = [
            [
                "id" => "Agent",
                "title" => "Connect with Agent",
            ],
            [
                "id" => "Back",
                "title" => "Back",
            ]
            ];

                $postData=['number' => $number,'header_text' => 'Connect with Agent','body_text' => 'Our Agent will assit you','footer_text' => '','buttons' => $button_actions];

                $postData_json = json_encode($postData, true);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $base_url.'sendButtonMessageAPI.php');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
                echo $response = curl_exec($ch);
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

}

else if(isset($interactive_reply_title)&&$interactive_reply_title=='Back')
{
        $button_actions = [
            [
                "id" => "NewBK",
                "title" => "New Booking",
            ],
            [
                "id" => "Status",
                "title" => "Booking Status",
            ],
            [
                "id" => "others",
                "title" => "Others",
            ]
        ];

        $postData=['number' => $number,'header_text' => 'Continue','body_text' => 'Please Choose an Option','footer_text' => '','buttons' => $button_actions];

        $postData_json = json_encode($postData, true);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url.'sendButtonMessageAPI.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
        echo $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

}

else if(isset($interactive_reply_title)&&$interactive_reply_title=='Booking Status')
{
        $button_actions = [
            [
                "id" => "samenum",
                "title" => "Same Number",
            ],
            [
                "id" => "othernum",
                "title" => "Other Number",
            ],
            [
                "id" => "Back",
                "title" => "Back",
            ]
        ];

        $postData=['number' => $number,'header_text' => '','body_text' => 'Did you use this phone number for your booking?','footer_text' => '','buttons' => $button_actions];

        $postData_json = json_encode($postData, true);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url.'sendButtonMessageAPI.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
        echo $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

}

else if(isset($interactive_reply_title)&&$interactive_reply_title=='Other Number')
{
        $button_actions = [
            [
                "id" => "Agent",
                "title" => "Connect with Agent",
            ],
            [
                "id" => "Back",
                "title" => "Back",
            ]
            ];

                $postData=['number' => $number,'header_text' => 'Connect with Agent','body_text' => 'Our Agent will assit you','footer_text' => '','buttons' => $button_actions];

                $postData_json = json_encode($postData, true);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $base_url.'sendButtonMessageAPI.php');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
                echo $response = curl_exec($ch);
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

}

else if(isset($interactive_reply_title)&&$interactive_reply_title=='Same Number')
{
        $button_actions = [
            [
                "id" => "extbk",
                "title" => "Existing Bookings",
            ],
            [
                "id" => "pastbk",
                "title" => "Past Bookings",
            ],
            [
                "id" => "Back",
                "title" => "Back",
            ]
        ];

        $postData=['number' => $number,'header_text' => 'Booking Type','body_text' => 'Please choose from list','footer_text' => '','buttons' => $button_actions];

        $postData_json = json_encode($postData, true);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url.'sendButtonMessageAPI.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
        echo $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

}

else if(isset($interactive_reply_title)&&$interactive_reply_title=='Existing Bookings')
{
        $list = [
            [
                "id" => "Ref1",
                "title" => "#123852369",
                "description" => "Booking ID"
            ],
            [
                "id" => "Ref2",
                "title" => "#852147963",
                "description" => "Booking ID"
            ],
            [
                "id" => "Ref3",
                "title" => "#445698753",
                "description" => "Booking ID"
            ]
        ];

        $postData=['number' => $number,'header_text' => 'Booking Reference','body_text' => 'Please choose a Booking ID','footer_text' => '','list_button_name' => 'Booking List','list' => $list];

        $postData_json = json_encode($postData, true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url.'sendListMessageAPI.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
            echo $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

}

else if(isset($interactive_reply_title)&&$interactive_reply_title=='Past Bookings')
{
        $list = [
            [
                "id" => "Ref1",
                "title" => "#123852369",
                "description" => "Booking ID"
            ],
            [
                "id" => "Ref2",
                "title" => "#852147963",
                "description" => "Booking ID"
            ],
            [
                "id" => "Ref3",
                "title" => "#445698753",
                "description" => "Booking ID"
            ]
        ];

        $postData=['number' => $number,'header_text' => 'Booking Reference','body_text' => 'Please choose a Booking ID','footer_text' => '','list_button_name' => 'Booking List','list' => $list];

        $postData_json = json_encode($postData, true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url.'sendListMessageAPI.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
            echo $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

}

else if(isset($interactive_reply_description)&&$interactive_reply_description=='Booking ID')
{
        $message = "Booking Details:\n";
        $message .= "Booking ID: $interactive_reply_title\n";
        $message .= "[Dispaly the booking details from CRM]";
        $date=date('d/m/Y H:i');
        $message .= "Status Date & Time: " . $date . "\n";

        $postData=['number' => $number,'message' => $message];

        $postData_json = json_encode($postData, true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url.'sendTextMessageAPI.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
            echo $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $button_actions = [
            [
                "id" => "Agent",
                "title" => "Connect with Agent",
            ],
            [
                "id" => "Back",
                "title" => "Back",
            ]
            ];

                $postData=['number' => $number,'header_text' => 'Thank you','body_text' => 'Is there anything else I can help you with?','footer_text' => '','buttons' => $button_actions];

                $postData_json = json_encode($postData, true);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $base_url.'sendButtonMessageAPI.php');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
                echo $response = curl_exec($ch);
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
}

else if(isset($interactive_reply_title)&&$interactive_reply_title=='Others') 
{
        $message = "[Dispaly any other information or contact details from CRM]";

        $postData=['number' => $number,'message' => $message];

        $postData_json = json_encode($postData, true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url.'sendTextMessageAPI.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
            echo $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $button_actions = [
            [
                "id" => "Back",
                "title" => "Back",
            ]
            ];

                $postData=['number' => $number,'header_text' => 'Options','body_text' => 'Please choose an option','footer_text' => '','buttons' => $button_actions];

                $postData_json = json_encode($postData, true);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $base_url.'sendButtonMessageAPI.php');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
                echo $response = curl_exec($ch);
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
}

else if(isset($interactive_reply_title)&&$interactive_reply_title=='Connect with Agent')
{
        $message = "Please wait our agent will contact you soon";

        $postData=['number' => $number,'message' => $message];

        $postData_json = json_encode($postData, true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $base_url.'sendTextMessageAPI.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
            echo $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

}

else if (strpos($message_body, 'TRV2025') !== false)
{

       $button_actions = [
            [
                "id" => "NewBK",
                "title" => "New Booking",
            ],
            [
                "id" => "Status",
                "title" => "Booking Status",
            ],
            [
                "id" => "others",
                "title" => "Others",
            ]
        ];

        $postData=['number' => $number,'header_text' => 'Welcome','body_text' => 'Please Choose an Option','footer_text' => '','buttons' => $button_actions];

        $postData_json = json_encode($postData, true);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url.'sendButtonMessageAPI.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
        echo $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
}

else if (strpos($message_body, 'Hi') !== false)
{

       $button_actions = [
            [
                "id" => "NewBK",
                "title" => "New Booking",
            ],
            [
                "id" => "Status",
                "title" => "Booking Status",
            ],
            [
                "id" => "others",
                "title" => "Others",
            ]
        ];

        $postData=['number' => $number,'header_text' => 'Welcome','body_text' => 'Please Choose an Option','footer_text' => '','buttons' => $button_actions];

        $postData_json = json_encode($postData, true);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url.'sendButtonMessageAPI.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);
        echo $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
}



// common functions ////

function getNextFiveDays() 
{
    $days = [];
    $now = new DateTime();
    $currentHour = (int) $now->format('H'); 

    if ($currentHour >= 15) {
        $now->modify('+1 day');
    }

    for ($i = 1; $i <= 7; $i++) {
        $days[] = [
            "id" => "DAY" . $i,
            "title" => $now->format('Y-m-d'), 
            "description" => "Date"
        ];
        $now->modify('+1 day');
    }

    return $days;
}

function getAvailableTimes($date) 
{
    $times = [];
    $startHour = 9;
    $endHour = 17;
    $now = new DateTime();
    $currentHour = (int) $now->format('H');
    
    if ($date == $now->format('Y-m-d')) 
    {
        $startHour = max($currentHour + 1, $startHour); 
    }

    $counter = 1;
    for ($hour = $startHour; $hour < $endHour; $hour++) 
    {
        $times[] = [
            "id" => "TIME" . $counter,
            "title" => sprintf("%02d:00", $hour), 
            "description" => "Time"
        ];
        $counter++;
    }

    return $times;
}

?>