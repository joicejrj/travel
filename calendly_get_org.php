<?php

$token = "eyJraWQiOiIxY2UxZTEzNjE3ZGNmNzY2YjNjZWJjY2Y4ZGM1YmFmYThhNjVlNjg0MDIzZjdjMzJiZTgzNDliMjM4MDEzNWI0IiwidHlwIjoiUEFUIiwiYWxnIjoiRVMyNTYifQ.eyJpc3MiOiJodHRwczovL2F1dGguY2FsZW5kbHkuY29tIiwiaWF0IjoxNzcxMzI1ODg2LCJqdGkiOiJlZDg1Y2U2MS0yY2FhLTQ1NmUtYTc5MC05YzBlM2M0MDQ2ZjQiLCJ1c2VyX3V1aWQiOiJlNTA4NmVmMi1iMzVkLTQxZjgtOWY0My04YWY2YTJlMTEzZTcifQ.iJnorW3QX3Jv-NUUTiUmtSY3y5nPoOJGHz1gAGExd5RWSJu7L2ApQX663uLtGWm4iQOv7aDQnYFaknnVxT2uXA";

$ch = curl_init("https://api.calendly.com/users/me");

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    die("Curl Error: " . curl_error($ch));
}

curl_close($ch);

$data = json_decode($response, true);

echo "<pre>";
print_r($data);
echo "</pre>";
?>