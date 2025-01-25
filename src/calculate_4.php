<?php

$fp = fopen('data/measurements.txt', 'r');

$results = [];
while (($line = fgets($fp, 4096)) !== false) {
    [$station, $temp] = explode(';', $line);
    $temp = (float) $temp;

    if (!isset($results[$station])) {
        $results[$station] = [$temp, $temp, $temp, 1];
    } else {
        $results[$station][0] = $results[$station][0] < $temp ? $results[$station][0] : $temp;
        $results[$station][1] = $results[$station][1] > $temp ? $results[$station][1] : $temp;
        $results[$station][2] += $temp;
        $results[$station][3]++;
    }
}
fclose($fp);

ksort($results);

echo '{', PHP_EOL;
foreach ($results as $name => &$temps) {
    echo "\t", $name, '=', $temps[0], '/', number_format($temps[2]/$temps[3], 1), '/', $temps[1], ',', PHP_EOL;
}
echo '}', PHP_EOL;
