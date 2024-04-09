#!/bin/bash

# Run tests for step 2

# Function to run a test case
run_test() {
    echo "Testing file: $1"
    php jsonExtend.php "$(cat "$1")"
    if [ $? -eq 0 ]; then
        echo "Test PASSED"
    else
        echo "Test FAILED"
    fi
    echo "----------------------------------------"
}

# Run tests
echo "Starting tests for Step 2 and 3"

run_test "tests/step2/valid.json"
run_test "tests/step2/valid2.json"
run_test "tests/step2/invalid.json"
run_test "tests/step2/invalid2.json"
run_test "tests/step3/valid.json"
run_test "tests/step3/invalid.json"

echo "All tests completed"
