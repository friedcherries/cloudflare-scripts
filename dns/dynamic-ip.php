<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

/**
 * Returns the external IP of the local machine
 */
function getHostIp()
{
    // Get our current IP
    $gzl = new Client();
    $response = $gzl->get('https://api.ipify.org/', [
        'query' => [
            'format' => 'json',
        ],
    ]);
    $data = json_decode($response->getBody()->getContents(), true);
    return $data['ip'];
}

/**
 * Returns the zone names/ids for a CloudFlare account
 */
function getZones($token)
{
    $gzl = new Client();
    $response = $gzl->get('https://api.cloudflare.com/client/v4/zones', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ],
    ]);
    $zones = json_decode($response->getBody()->getContents(), true);
    return $zones;
}

function getDnsRecords($zoneId, $token)
{
    $gzl = new Client();
    $response = $gzl->get("https://api.cloudflare.com/client/v4/zones/$zoneId/dns_records", [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ],
    ]);
    $records = json_decode($response->getBody()->getContents(), true);
    return $records;
}

function updateDnsRecordIp($zoneId, $dnsId, $token, $newIp)
{
    $gzl = new Client();
    $response = $gzl->patch("https://api.cloudflare.com/client/v4/zones/$zoneId/dns_records/$dnsId", [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ],
        'json' => [
            'content' => $newIp
        ]
    ]);

    $results = json_decode($response->getBody()->getContents(), true);
    if ($results['success']) {
        return $results['result'];
    } else {
        return false;
    }
}

/**
 * Loading our configuration data
 */
try {
    $data = json_decode(
        file_get_contents('cloudflare.json'),
        true,
        5,
        JSON_THROW_ON_ERROR
    );
} catch (JsonException $e) {
    throw new RuntimeException('Invalid JSON file: ' . $e->getMessage());
}

$zoneList = $data['zones'];
$token = $data['token'];
$zones = getZones($token);
$hostIp = getHostIp();

foreach ($zones['result'] as $zone) {
    if (in_array($zone['name'], $zoneList)) {
        $dnsRecords = getDnsRecords($zone['id'], $token);
        foreach ($dnsRecords['result'] as $record) {
            if (in_array($record['name'], $zoneList) && $record['type'] == 'A') {
                $ip = $record['content'];
                if ($ip != $hostIp) {
                    $result = updateDnsRecordIp($zone['id'], $record['id'], $token, $hostIp);
                    if (!$result) {
                        echo "Unknown error occurred.\n";
                    } else {
                        echo "IP UPDATE: {$record['name']} IP updated from {$ip} to {$hostIp}.\n";
                    }
                } else {
                    echo "IP MATCH: {$record['name']} IP of {$ip} matches {$hostIp}.\n";
                }
            }
        }
    }
}
