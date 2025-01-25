#!/bin/bash

RUNNERS_DIR=src/zts
RUNNERS=$(basename -a "$RUNNERS_DIR"/zts_calculate_*.php)
ITERATIONS=10
NUM_OF_LINES_SUFFIXES=(1B)

# Split `ls` output into an array
readarray -t RUNNERS <<<"$RUNNERS"

# Remove previous log files
rm -f results/*.log results-zts.tar.gz

rm -f data/measurements.txt
#python3 create_measurements.py 1_000_000 && mv data/measurements.txt data/measurements_1M.txt
#python3 create_measurements.py 10_000_000 && mv data/measurements.txt data/measurements_10M.txt
#python3 create_measurements.py 100_000_000 && mv data/measurements.txt data/measurements_100M.txt
python3 create_measurements.py 1_000_000_000 && mv data/measurements.txt data/measurements_1B.txt

for NUM_OF_LINES_SUFFIX in "${NUM_OF_LINES_SUFFIXES[@]}"; do
  # Symlink the correct measurements source file
  rm -f data/measurements.txt && ln -s "measurements_${NUM_OF_LINES_SUFFIX}.txt" data/measurements.txt

  for RUNNER in "${RUNNERS[@]}"; do
    # Benchmark the runner
    perf stat -o "results/zts-${NUM_OF_LINES_SUFFIX}-${RUNNER%.php}.log" -r "${ITERATIONS}" -d /opt/php8.4-zts/bin/php "${RUNNERS_DIR}/${RUNNER}" 32
  done
done

tar -cvzf results-zts.tar.gz results