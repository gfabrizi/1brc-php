<?php

$file = 'data/measurements.txt';

// Usage example, passing argument from command line
if ($argc !== 2) {
    echo "Usage: ", __FILE__, " <number of threads>\n";
    exit(1);
}

$threads_cnt = (int) $argv[1];

/**
 * This function will open the file passed in `$file` and read and process the
 * data from `$chunk_start` to `$chunk_end`.
 *
 * The returned array has the name of the station as the key and an array as the
 * value, containing the min temp in key 0, the max temp in key 1, the sum of
 * all temperatures in key 2 and count of temperatures in key 3.
 *
 * @return array<string, array{0: float, 1: float, 2: float, 3: int}>
 */
$process_chunk = function (string $file, int $chunk_start, int $chunk_end): array {
    $results = [];
    $fp = fopen($file, 'rb');
    fseek($fp, $chunk_start);
    while (($line = fgets($fp)) !== false) {
        $chunk_start += strlen($line);
        if ($chunk_start > $chunk_end) {
            break;
        }

        $station = strtok($line, ';');
        $temp = (float) strtok(';');

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

    return $results;
};

$chunks = get_file_chunks($file, $threads_cnt);

$futures = [];
for ($i = 0; $i < $threads_cnt; $i++) {
    $futures[$i] = \parallel\run(
        $process_chunk,
        [
            $file,
            $chunks[$i][0],
            $chunks[$i][1]
        ]
    );
}

$results = [];
for ($i = 0; $i < $threads_cnt; $i++) {
    // `value()` blocks until a result is available, so the main thread waits
    // for the thread to finish
    $chunk_result = $futures[$i]->value();
    foreach ($chunk_result as $station => $measurement) {
        if (isset($results[$station])) {
            $result = &$results[$station];
            $result[2] += $measurement[2];
            $result[3] += $measurement[3];
            if ($measurement[0] < $result[0]) {
                $result[0] = $measurement[0];
            } elseif ($measurement[1] < $result[1]) {
                $result[1] = $measurement[1];
            }
        } else {
            $results[$station] = $measurement;
        }
    }
}

ksort($results);

echo '{', PHP_EOL;
foreach($results as $name => &$temps) {
    echo "\t", $name, '=', $temps[0], '/', number_format($temps[2]/$temps[3], 1), '/', $temps[1], ',', PHP_EOL;
}
echo '}', PHP_EOL;

/**
 * Get the chunks that each thread needs to process with start and end position.
 * These positions are aligned to \n chars (full lines).
 *
 * @return array<int, array{0: int, 1: int}>
 */
function get_file_chunks(string $file, int $threads): array {
    $filesize = filesize($file);
    $chunkSize = (int) ($filesize / $threads);

    $fp = fopen($file, 'rb');

    $chunks = [];
    $chunkStart = 0;

    while ($chunkStart < $filesize) {
        $chunkEnd = min($filesize, $chunkStart + $chunkSize);

        if ($chunkEnd < $filesize) {
            fseek($fp, $chunkEnd);
            fgets($fp);
            $chunkEnd = ftell($fp);
        }

        $chunks[] = [
            $chunkStart,
            $chunkEnd
        ];

        $chunkStart = $chunkEnd;
    }
    fclose($fp);

    return $chunks;
}