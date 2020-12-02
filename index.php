<html lang="en">
<body>
<pre>
<?php

define('DAPR_HOST', 'http://localhost:3500/v1.0');

function get($url)
{
    $curl = curl_init($url);
    curl_setopt_array(
        $curl,
        [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ["Accept: application/json"],
        ]
    );
    $result = curl_exec($curl);

    return $result;
}

function post($url, $data)
{
    $curl = curl_init($url);
    curl_setopt_array(
        $curl,
        [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_HTTPHEADER     => ["Content-type: application/json", "Accept: application/json"],
        ]
    );

    return curl_exec($curl);
}

$initial_state = json_encode(
    [
        [
            'key'   => 'test',
            'value' => 'starting value',
        ],
    ],
    JSON_PRETTY_PRINT
);

echo "Setting some initial state: \n";
echo $initial_state;

$random_key = uniqid();

post(DAPR_HOST.'/state/statestore', $initial_state);

echo "\n\nRetrieving sent state:\n";
$state = get(DAPR_HOST.'/state/statestore/test');
echo $state;

echo "\n\nSending a transaction that should fail with a bogus etag:\n";
$transaction = json_encode(
    [
        'operations' => [
            [
                'operation' => 'upsert',
                'request'   => [
                    'key'     => 'test',
                    'value'   => 'failed value',
                    'etag'    => '3431231',
                    'options' => [
                        'concurrency' => 'first-write',
                        'consistency' => 'strong',
                    ],
                ],
            ],
            [
                'operation' => 'upsert',
                'request'   => [
                    'key'   => 'test',
                    'value' => 'should not be set',
                ],
            ],
            [
                'operation' => 'upsert',
                'request' => [
                    'key' => $random_key,
                    'value' => 'should not be set',
                ],
            ],
        ],
    ],
    JSON_PRETTY_PRINT
);
echo $transaction;

$result = post(DAPR_HOST.'/state/statestore/transaction', $transaction);
echo "\n\nGot this result from the transaction:\n$result\n";
echo "But have this value stored:\n";
$state = get(DAPR_HOST.'/state/statestore/test');
echo $state;

echo "\nAnd the following key should be empty:\n";
$state = get(DAPR_HOST.'/state/statestore/'.$random_key);
echo $state;