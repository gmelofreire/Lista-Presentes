<?php

return [
    /*
    | Driver de push: "log" (padrão, só registra no log) ou "null" (desliga).
    | Para enviar de verdade, implemente App\Push\PushSender (ex.: FCM) e registre em AppServiceProvider.
    */
    'driver' => env('PUSH_DRIVER', 'log'),
];
