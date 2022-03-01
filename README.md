# Telegram

## use

```php
<?php

// Autoload files using the Composer autoloader.
require_once __DIR__ . '/../../vendor/autoload.php';

use Bedri\Telegram\Telegram;

$telegramToken = "<telegram token>";
$chatId = '<chat id>';

$telegram = new Telegram($telegramToken);
$sendMsg = $telegram->sendMessage($chatId ,"\xE2\x9D\x8C <b>"."something"."</b> OFFLINE");

if ($sendMsg['ok'] == 1) echo "msg was sent";
else echo "msg was not sent!";
```