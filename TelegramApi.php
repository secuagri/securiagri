<?php 
  $website="https://api.telegram.org/bot7514110384:AAE0cQ_RJBJCDff1XFnR9Yhih_8f-TfjMmg";
  $params=[
      'chat_id'=>"1304426300",
      'text'=>$message,
  ];
  $ch = curl_init($website . '/sendMessage');
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $result = curl_exec($ch);
  curl_close($ch);