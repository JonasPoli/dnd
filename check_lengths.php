<?php

$json = file_get_contents('data/open5e/monsters.json');
$data = json_decode($json, true);

$maxLengths = [
    'type' => 0,
    'subtype' => 0,
    'group' => 0,
    'alignment' => 0,
    'armor_desc' => 0,
    'cr' => 0,
    'challenge_rating' => 0,
];

foreach ($data['results'] as $monster) {
    foreach ($maxLengths as $field => $max) {
        $val = $monster[$field] ?? '';
        if (is_array($val))
            $val = json_encode($val);
        $len = strlen((string) $val);
        if ($len > $maxLengths[$field]) {
            $maxLengths[$field] = $len;
        }
    }
}

print_r($maxLengths);
