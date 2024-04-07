#!/bin/bash

# Run tests for step 1

# Function to run a test case
run_test() {
    echo "Testing file: $1"
    php json.php "$(cat "$1")"
    if [ $? -eq 0 ]; then
        echo "Test PASSED"
    else
        echo "Test FAILED"
    fi
    echo "----------------------------------------"
}

# Run tests
echo "Starting tests for Step 1"

run_test "tests/step1/valid.json"
run_test "tests/step1/invalid.json"

echo "All tests completed"
