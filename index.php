<?php

// require_once __DIR__ . '/vendor/autoload.php';

// use Telegram\Bot\Api;
// header('Content-Type: text/html; charset=utf-8');

// CONNECT TO DB

$servername = "localhost:8889";
$username = "root";
$password = "root";
$dbname = "telegrambot";
$ruta_local = null;

// Crea la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica la conexión
if ($conn->connect_error) {
    die("La conexión ha fallado: " . $conn->connect_error);
}

// Seleccionar el registro de la tabla "config" con ID = 1
$sql = "SELECT * FROM config WHERE ID = 1";
$result = $conn->query($sql);
$config = $result->fetch_assoc();


// Verificar que mensaje se ha enviado
$sql = "SELECT max(ID) as last_message_id FROM messages";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$last_message_id = $row['last_message_id'];

// Seleccionar el mensaje a enviar
$selectIDMessage = $config['lastMessageID'];

if ( $last_message_id == $config['lastMessageID'] ) {
    $selectIDMessage = 1;
}else{
    $selectIDMessage = $config['lastMessageID'] + 1;
}

// Seleccionar mensaje a enviar
$sql = "SELECT * FROM messages WHERE ID = {$selectIDMessage}";
$result = $conn->query($sql);
$message = $result->fetch_assoc();
$newLastID = $message['ID'];

// Guardar ultimo ID de mensaje 
$sql = "UPDATE config SET lastMessageID = {$newLastID} WHERE id = 1";

$conn->query($sql); 

$conn->close();

// Envio del mensaje a Telegram
$mensajeATelegram = $message['message'];

if(!$mensajeATelegram) $mensajeATelegram = '';

if( $message['addLink'] ){
    $mensajeATelegram = $mensajeATelegram . '

**' . $message['callToAction'] . ':** ' . $message['link'];
}

// $telegram = new Api($config['token']);

// Establecer el token del bot y el ID de chat
$token   = $config['token'];
$chat_id = $config['chatID'];
$url;

// Configurar los parámetros de la solicitud
$params = null;

if( !$message['image'] ){
    // Construir la URL de la solicitud simple menssage
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
    
    $params = array(
        'chat_id' => $chat_id,
        'text' => utf8_encode( $mensajeATelegram )
    );
}else{
    // Construir la URL de la solicitud photo
    $url = "https://api.telegram.org/bot" . $token . "/sendPhoto";
    
    // Descargar la imagen en una variable
    $image_url = file_get_contents($message['image']);

    // Eliminar los bytes nulos de la ruta de archivo
    // $image_data = str_replace("\0", "", $image_url);
    // Establecer el nombre de archivo deseado para la imagen
    $nombre_imagen = "imagen.jpg";

    // Guardar la imagen en el directorio actual
    $ruta_local = __DIR__ . "/" . $nombre_imagen;
    
    file_put_contents($ruta_local, $image_url);

    $params = array(
        'chat_id' => $chat_id,
        'photo' => new CURLFile($ruta_local),
        'caption' => utf8_encode( $mensajeATelegram )
    );
}

// Realizar la solicitud HTTP POST
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// $response = curl_exec($ch);
// curl_close($ch);

// Verificar la respuesta
if($response){
    var_dump($response);
    echo "Mensaje enviado correctamente.";
} else{
    echo "Error al enviar el mensaje.";
}

// Eliminar la imagen después de enviarla
if($ruta_local) unlink($ruta_local);

return;

// $response = $telegram->getUpdates();

// $telegram->sendMessage([
//     'chat_id' => $config['chatID'],
//     'text' => $mensajeATelegram,
//     'parse_mode' => 'Markdown'
// ]);
