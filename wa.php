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
    "text": "$message"
  },
  "footer": {
    "text": "$footer"
  },
  "action": {
    "buttons": [
      {
        "type": "url",
        "title": "$title_link",
        "id": "1",
        "url": "$link"
      }
    ]
  },
  "type": "button",
  "to": "$number"
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
