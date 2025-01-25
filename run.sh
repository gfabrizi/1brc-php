#!/bin/bash

RUNNERS_DIR=src
RUNNERS=$(basename -a "$RUNNERS_DIR"/calculate_*.php)
ITERATIONS=10
NUM_OF_LINES_SUFFIXES=(1M 10M)

# Split `ls` output into an array
readarray -t RUNNERS <<<"$RUNNERS"

# Remove previous log files
rm -f results/*.log results.tar.gz

rm -f data/measurements.txt
python3 create_measurements.py 1_000_000 && mv data/measurements.txt data/measurements_1M.txt
python3 create_measurements.py 10_000_000 && mv data/measurements.txt data/measurements_10M.txt
#python3 create_measurements.py 100_000_000 && mv data/measurements.txt data/measurements_100M.txt
#python3 create_measurements.py 1_000_000_000 && mv data/measurements.txt data/measurements_1B.txt

for NUM_OF_LINES_SUFFIX in "${NUM_OF_LINES_SUFFIXES[@]}"; do
  # Symlink the correct measurements source file
  rm -f data/measurements.txt && ln -s "measurements_${NUM_OF_LINES_SUFFIX}.txt" data/measurements.txt

  for RUNNER in "${RUNNERS[@]}"; do
    # Benchmark the runner
    perf stat -o "results/${NUM_OF_LINES_SUFFIX}-${RUNNER%.php}.log" -r "${ITERATIONS}" -d php "${RUNNERS_DIR}/${RUNNER}"
  done
done

tar -cvzf results.tar.gz results