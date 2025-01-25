<?php

$fp = fopen('data/measurements.txt', 'r');

$stations = [];
while (($line = fgets($fp)) !== false) {
    $station = strtok($line, ';');
    $temp = (float) strtok(';');

    if (!isset($stations[$station])) {
        $stations[$station] = [];
    }

    $stations[$station][] = (float) $temp;
}
fclose($fp);

$results = [];
foreach ($stations as $key => $value) {
    $results[$key] = [];
    $results[$key][0] = min($value);
    $results[$key][1] = max($value);
    $results[$key][2] = array_sum($value);
    $results[$key][3] = count($value);
}

ksort($results);

echo '{', PHP_EOL;
foreach ($results as $name => &$temps) {
    echo "\t", $name, '=', $temps[0], '/', number_format($temps[2]/$temps[3], 1), '/', $temps[1], ',', PHP_EOL;
}
echo '}', PHP_EOL;
