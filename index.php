<?php
 
/** 
 *
 * @author Grigoryev Vladimir <veryrich2022@gmail.com.com>
 *  
 *  */

$data = json_decode(file_get_contents('php://input'), TRUE); //Принимаем данные от телеграмма

if (!isset($data['update_id'])) { //Проверяем что запрос сделан с api телеграма
    die(-1);
}

const TOKEN = "7358658888:AAGhq8pX-C2og0gdRq0tGU2pl3vMMqgysnE";  // Токен для связи с api телеграма

function getMessage() //Функция для получения Цитат и афоризмов со стороннего api
{
    $state = true;
    $ch = curl_init();
    $url = 'http://api.forismatic.com/api/1.0/';


    $postFields = [
        'method'=>'getQuote',
        'format'=>'json',
        'key'=> rand(0,6),
        'lang'=>'ru'
    ];
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);  // Указываем, что это POST-запрос
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));  // Устанавливаем параметры запроса
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Возвращать ответ как строку

    $response = curl_exec($ch);
    if ($response === FALSE) {
        $state = false;
    }
    curl_close($ch);

    $data = json_decode($response, true);
    
    if ($state) {
        $ret = $data;
    } else {
        $ret = null; 
    }
    return $ret;
    
    

}


switch ($data['message']['text']) { //Проверяем что ввел пользователь в чате
    case '/start':
        $send_data = [
            'text'   => "Нажми на кнопку 'Хочу умную мысль!'",
            'reply_markup' => json_encode([
                'resize_keyboard' => true,
                'keyboard' => [
                    [
                        ['text' => 'Хочу умную мысль!']
                    ]
                ]
            ])
        ];
        break;
    case 'Хочу умную мысль!':
        $mess = getMessage();
        $txt = is_null($mess['quoteText']) ? "Упс, какая-то ошибка на серваке ( " : $mess['quoteText'];
        $auth = empty($mess['quoteAuthor']) ? "Неизвестно" : $mess['quoteAuthor'];
        $send_data = [
            'text' => $txt . " (" . $auth . ")",
            'reply_markup' => json_encode([
                'resize_keyboard' => true,
                'keyboard' => [
                    [
                        ['text' => 'Хочу умную мысль!']
                    ]
                ]
            ])
        ];    
        break;
    default: 
        $send_data = [
            'text' => "Ты дебил? Просто нажми на кнопку, не надо ничего вводить!",
            'reply_markup' => json_encode([
                'resize_keyboard' => true,
                'keyboard' => [
                    [
                        ['text' => 'Хочу умную мысль!']
                    ]
                ]
            ])
        ];
        
}




//Подготавливаем запрос
$ch = curl_init();
$send_data["chat_id"] = $data['message']['chat']['id'];

curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot'.TOKEN.'/sendMessage');
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($send_data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
    
$response = curl_exec($ch); // Отправляем и получаем ответ

if (curl_errno($ch)) {
    echo 'Ошибка cURL: ' . curl_error($ch);
}
curl_close($ch);
    
echo $response;

