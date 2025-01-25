<?php

$fp = fopen('data/measurements.txt', 'r');

$results = [];
while (($line = fgets($fp, 4096)) !== false) {
    [$station, $temp] = explode(';', $line);
    $temp = (float) $temp;

    if (isset($results[$station])) {
        $resultStation = &$results[$station];

        if ($temp < $resultStation[0]) {
            $resultStation[0] = $temp;
        }

        if ($temp > $resultStation[1]) {
            $resultStation[1] = $temp;
        }

        $resultStation[2] += $temp;
        $resultStation[3]++;
    } else {
        $results[$station] = [$temp, $temp, $temp, 1];
    }
}
fclose($fp);

ksort($results);

echo '{', PHP_EOL;
foreach ($results as $name => &$temps) {
    echo "\t", $name, '=', $temps[0], '/', number_format($temps[2]/$temps[3], 1), '/', $temps[1], ',', PHP_EOL;
}
echo '}', PHP_EOL;
