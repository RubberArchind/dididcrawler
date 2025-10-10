<?php

return [
    'host' => env('MQTT_HOST', '127.0.0.1'),
    'port' => env('MQTT_PORT', 1883),
    'username' => env('MQTT_USERNAME'),
    'password' => env('MQTT_PASSWORD'),
    'topic_prefix' => env('MQTT_TOPIC_PREFIX', 'devices'),
    'client_id' => env('MQTT_CLIENT_ID', 'dididcrawler-listener'),
    'keep_alive' => env('MQTT_KEEP_ALIVE', 60),
    'connection_timeout' => env('MQTT_CONNECTION_TIMEOUT', 30),
];
