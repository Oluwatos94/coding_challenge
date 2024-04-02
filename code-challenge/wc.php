<?php

function count_bytes($file_path) {
    // Open the file in binary mode
    $file = fopen($file_path, 'rb');

// check if the file is opened successfully
    if ($file === false) {
        echo "Error: unable to open the file\n";
        return;
    }

// seek to the end of the file to get the byte count
    fseek($file, 0, SEEK_END);
    // check if the file is seeked successfully
    if ($file === -1) {
        echo "Error: unable to seek the file\n";
    }
    $byte_count = ftell($file);

    fclose($file);

    echo $byte_count . PHP_EOL; 
}

function count_lines($file_path) {
    // Open the file in binary mode
    $file = fopen($file_path, 'r');

// check if the file is opened successfully
    if ($file === false) {
        echo "Error: unable to open the file\n";
        return;
    }

    // initialize a line count
    $line_count = 0;
    
    // loop through the file line by line
    while (!feof($file)) {
        // read the file line by line
        $file_line = fgets($file);

        // Increment the line count if not empty
        if ($file_line !== false) {
            $line_count++;
        }
    }

    fclose($file);

    echo "Line count: $line_count" . PHP_EOL; 
}

function count_words($file_path) {
    // get the file content
    $file_contents = file_get_contents($file_path);

    // read the file content and count the words
    $word_count = str_word_count($file_contents);

    // print the word count
    echo "word count: $word_count" . PHP_EOL;
}

function number_of_characters($file_path) {
    //  get the content of the file
    $contents = file_get_contents($file_path, 'r');

    // count the number of characters
    $character_count = mb_strlen($contents, "UTF-8");

    // print the character count
    echo "character count: $character_count\n";
}

// Check if correct number of arguments are passed and handle default behavior
if ($argc == 2 || ($argc == 3 && in_array($argv[1], ['-c', '-l', '-w', '-m']))) {
    $file_path = $argc == 2 ? $argv[1] : $argv[2];
    
    if (!file_exists($file_path)) {
        die("Error: File does not exist\n");
    }

    // check the option and call the respective function
    if ($argc == 3) {
        if ($argv[1] === '-l') {
            count_lines($file_path);
        } else if ($argv[1] === '-c') {
            count_bytes($file_path);
        } else if ($argv[1] === '-w') {
            count_words($file_path);
        } else if ($argv[1] === '-m') {
            number_of_characters($file_path);
        }
    } else {
        // no option provided, perform default behavior
        count_bytes($file_path);
        count_lines($file_path);
        count_words($file_path);
        number_of_characters($file_path);
    }
} else {
    die("Usage: php wc.php [-c|-l|-w|-m] filename\n");
}