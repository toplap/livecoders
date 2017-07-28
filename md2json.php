#!/usr/bin/env php
<?php

$file = 'README.md';
$trimb4 = '# People';

$h = fopen($file, 'r');
$p = false;

$allArtists = [];

$artist = null;

while (false !== ($l = fgets($h, 4096))) {
    if (! $p) {
        if (0 === strpos($l, $trimb4)) {
            $p = true;
        }
        continue;
    }

    if (preg_match('/^#{2}/', $l)) {
        if (is_array($artist)) {
            $allArtists[] = $artist;
            $artist = [];
        }
        $artist['name'] = trim(trim($l, '#'));
    }
    else if (preg_match('/^\s+-/', $l)) {
        $clean = trim(substr($l, 1+strpos($l, '-')));
        $b = strpos($clean, ':');
        $key = trim(substr($clean, 0, $b));
        $val = trim(substr($clean, 1+$b));
        $artist[$key] = $val;
    }
}
if (is_array($artist) && count($artist)) {
    $allArtists[] = $artist;
}

fclose($h);

$cleanArtists = array_map(function ($a) {
    if (array_key_exists('aliases', $a)) {
        $a['aliases'] = array_map('trim', explode(',', $a['aliases']));
    }

    if (array_key_exists('audio', $a)) {
        $a['audio'] = array_map('trim', explode(',', $a['audio']));
    }

    if (array_key_exists('collaborations', $a)) {
        $a['collaborations'] = array_map('trim', explode(',', $a['collaborations']));
    }

    if (array_key_exists('livecoding', $a)) {
        $a['livecoding'] = array_map('trim', explode(',', $a['livecoding']));
    }

    if (array_key_exists('twitter', $a)) {
        preg_match('/\[([^\]]+)\]/', $a['twitter'], $m);
        $a['twitter'] = end($m);
    }

    if (array_key_exists('video', $a)) {
        $a['video'] = array_map('trim', explode(',', $a['video']));
    }

    return $a;
}, $allArtists);

$schema = json_decode(file_get_contents('artists-schema.json'), true);

$data = [
    '$schema' => $schema['$id'],
    'artists' => $cleanArtists
];

$options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;

echo json_encode($data, $options), "\n";
