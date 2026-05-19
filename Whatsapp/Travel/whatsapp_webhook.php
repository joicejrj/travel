<?php

$data = file_get_contents('php://input');

$filePath = 'example_api_webhook.txt';
$file = fopen($filePath, 'w');
fwrite($file, $data);
fclose($file);

//$data='{"object":"whatsapp_business_account","entry":[{"id":"397551387608771","changes":[{"value":{"messaging_product":"whatsapp","metadata":{"display_phone_number":"919526543210","phone_number_id":"3148315218575257"},"contacts":[{"profile":{"name":"AdsBlink Media"},"wa_id":"917294141619"}],"messages":[{"from":"917294141619","id":"wamid.HBgMOTE3Mjk0MTQxNjE5FQIAEhggQTU1OEM1ODcxOUYxOUNCRTQwMjU5RUNENjlCRjY3RkQA","timestamp":"1761753531","text":{"body":"Same no"},"type":"text"}]},"field":"messages"}]}]}';

//$data='{"object":"whatsapp_business_account","entry":[{"id":"397551387608771","changes":[{"value":{"messaging_product":"whatsapp","metadata":{"display_phone_number":"919526543210","phone_number_id":"3148315218575257"},"contacts":[{"profile":{"name":"Sudeep S"},"wa_id":"919562543210"}],"messages":[{"context":{"from":"919526543210","id":"wamid.HBgMOTE5NTYyNTQzMjEwFQIAERgSMzQ5ODRGNjlCOTJBNDRDRDhEAA=="},"from":"919562543210","id":"wamid.HBgMOTE5NTYyNTQzMjEwFQIAEhgWM0VCMDg1RUUyMjdBRjY5RDlGNkZDRQA=","timestamp":"1761737572","type":"interactive","interactive":{"type":"button_reply","button_reply":{"id":"2","title":"Digital Marketing"}}}]},"field":"messages"}]}]}';

$payload = null;

$payload = json_decode($data ?? '');

$get = function($obj, $path, $default = '') {
    foreach ($path as $p) {
        if (is_object($obj) && isset($obj->$p)) {
            $obj = $obj->$p;
        } elseif (is_array($obj) && isset($obj[$p])) {
            $obj = $obj[$p];
        } else {
            return $default;
        }
    }
    return $obj;
};

// Entry -> changes -> value
$entry  = $get($payload, ['entry', 0], null);
$change = $get($entry, ['changes', 0], null);
$value  = $get($change, ['value'], null);

if (!$value) {
    // nothing useful
    error_log('Webhook: no value in payload');
    return;
}

// contacts and messages arrays
$contact = $get($value, ['contacts', 0], null);
$message = $get($value, ['messages', 0], null);

// Basic fields
$contact_name = $get($contact, ['profile', 'name'], '');
$from         = $get($message, ['from'], '');
$type         = $get($message, ['type'], '');

// Default interactive fields
$interactive_reply_id = '';
$interactive_reply_title = '';
$interactive_reply_description = '';

// If it's interactive, check both list_reply and button_reply
if ($message && isset($message->interactive)) 
{
    // LIST REPLY
    if (isset($message->interactive->list_reply)) 
    {
        $interactive_reply_id = $message->interactive->list_reply->id ?? '';
        $interactive_reply_title = $message->interactive->list_reply->title ?? '';
        $interactive_reply_description = $message->interactive->list_reply->description ?? '';
    }
    // BUTTON REPLY
    else if (isset($message->interactive->button_reply)) 
    {
        $interactive_reply_id = $message->interactive->button_reply->id ?? '';
        $interactive_reply_title = $message->interactive->button_reply->title ?? '';
        // button_reply usually has only id/title. description is uncommon; keep empty if not present.
        $interactive_reply_description = $message->interactive->button_reply->description ?? '';
    }
    // other interactive types could be handled here (e.g., reply, product_reply, etc.)
}

// If it's a plain text message, get text body
$text_body = '';
if ($type === 'text') 
{
    $text_body = $get($message, ['text', 'body'], '');
}


$number=$from;
$contact_name=$contact_name;
$type=$type;
$message_curl=$text_body;


$postData=['number' => $number,'contact_name' => $contact_name,'type' => $type,'message_body' => $message_curl,'interactive_reply_id' => $interactive_reply_id,'interactive_reply_title' => $interactive_reply_title,'interactive_reply_description' => $interactive_reply_description];

echo $postData_json = json_encode($postData, true);


$clientUrl = 'https://erp.jrjapp.com/Whatsapp/Travel/api-webhook-endpoint.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $clientUrl);
curl_setopt($ch, CURLOPT_POST, true); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json' 
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData_json);

echo $response = curl_exec($ch);

curl_close($ch);

?>