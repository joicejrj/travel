<?php
// cron_imap_fetch.php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

//testing
// $date = "2025-10-29";

// Get current domain (with protocol)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)? "https://" : "http://";
$domain = $protocol . $_SERVER['HTTP_HOST'];
$current_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$current_url = $domain . $current_path . '/';
// $current_url = $domain . $_SERVER['REQUEST_URI'];

$reminders_by_agent = [];

$getr = $db->get('[contacts_reminders=r]',array('#all'=>1,'#cus'=>"(DATE(reminder_at)=DATE('".$date."'))",'#join'=>'left join contacts as c on r.contact_id=c.id left join people as p on c.agent_id=p.id'),'r.id AS reminder_id,r.reminder_at,r.note,c.id AS contact_id,c.name AS contact_name,c.phone,c.email AS contact_email,p.id AS agent_id,p.name AS agent_name,p.email AS agent_email');
foreach ($getr->data as $key => $rem) {
    $agent_email = $rem->agent_email;
    $reminders_by_agent[$agent_email][] = $rem;
}

foreach ($reminders_by_agent as $agent_email => $reminders) {
    $agent_name = $reminders[0]->agent_name ?? 'Agent';
    $agent_id = $reminders[0]->agent_id ?? '';

    $manage_link = $current_url;
    if($agent_id!='') {
        $token = $site->randomstr(60);
        $db->update('people',array('id'=>$agent_id),array('login_token'=>$token));
        $manage_link = $current_url.'manage_reminders.php?token='.$token;
    }

    // Build HTML table for reminders
    $table = '<table border="1" cellspacing="0" cellpadding="6" style="border-collapse:collapse;width:100%;font-family:Arial,sans-serif;">
                <thead>
                  <tr style="background:#007bff;color:white;text-align:left;">
                    <th>#</th>
                    <th>When</th>
                    <th>Contact</th>
                    <th>Notes</th>
                  </tr>
                </thead><tbody>';
    $i = 1;
    foreach ($reminders as $r) {
        $table .= '<tr>
                    <td>' . $i++ . '</td>
                    <td>' . date('h:i A', strtotime($r->reminder_at)). '</td>
                    <td>' . htmlspecialchars($r->contact_name) .($r->contact_email!=''?'<br>'.$r->contact_email:'').($r->phone!=''?'<br>'.$r->phone:''). '</td>
                    <td>' . nl2br(htmlspecialchars($r->note)) . '</td>
                   </tr>';
    }
    $table .= '</tbody></table>';

    // Email content
    $subject = "Today's Reminders - " . date('d M Y',strtotime($date));
    $body = "Dear {$agent_name},
    <p>Here are your reminders for today (" . date('d M Y',strtotime($date)) . "):</p>
    {$table}
    <p style='margin-top:20px;text-align:center;'>
      <a href='{$manage_link}' 
         style='display:inline-block;background:#007bff;color:#fff;text-decoration:none;
                padding:5px 10px;border-radius:6px;font-weight:bold;'>
         Manage Reminders
      </a>
    </p>
    ";

    //testing
    // $agent_email = "joicekurups@gmail.com";
    
    $sentt = $site->send_mail($agent_email,$subject,['[content]'],[$body]);
    if ($sentt) {
        echo "Reminder email sent to {$agent_name} ({$agent_email})<br>";
    }
    else {
        echo "Email to {$agent_email} failed<br>";
    }
}

?>