<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://gate.whapi.cloud/messages/interactive',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
  "body": {
    "text": "Hello World"
  },
  "footer": {
    "text": "iczysender"
  },
  "action": {
    "buttons": [
      {
        "type": "url",
        "title": "Visit link",
        "id": "1",
        "url": "https://iczyser.com"
      }
    ]
  },
  "type": "button",
  "to": "6281287718800"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer {{bearerToken}}'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
