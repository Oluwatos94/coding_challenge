<?php

class JSONLexer
{
    private $input;
    private $position;

    public function __construct($input) {
        $this->input = $input;
        $this->position = 0;
    }

    public function getNextToken() {
        // skip whitespace characters
        while ($this->position < strlen($this->input) && ctype_space($this->input[$this->position])) {
            $this->position++;
        }
         // check for emd of input
        if ($this->position >= strlen($this->input)) {
            return ['type' => 'EOF', 'value' => null];
        }

        // check for valid json token
        $char = $this->input[$this->position];
        $this->position++;

        switch($char){
            case '{' :
                return ['type' => 'L_BRACE', 'value' => '{'];
            case '}' :
                return ['type' => 'R_BRACE', 'value' => '}'];
            case '"' :
                return $this->parseString();
            case ':' :
                return ['type' => 'COLON', 'Value' => ':'];
            case ',' :
                return ['type' => 'COMMA', 'value' => ','];
            default:
                return ['type' => 'INVALID', 'value' => $char];
        }
    }

    private function parseString() {
        $start = $this->position;
        while ($this->position < strlen($this->input) && $this->input[$this->position] !== '"') {
            $this->position++;
        }

        if ($this->input[$this->position] === '"') {
            $value = substr($this->input, $start, $this->position - $start);
            $this->position++; // skip the quote
            return ['type' => 'STRING', 'value' => $value];
        } else {
            return ['type' => 'INVALID', 'value' => substr($this->input, $start)];
        }
    }
}

class JSONParser
{
    public $lexer;
    public $currentToken;

    public function __construct($lexer) {
        $this->lexer = $lexer;
        $this->currentToken = $this->lexer->getNextToken();
    }

    public function parse() {
        if ($this->currentToken['type'] === 'L_BRACE') {
            $this->match('L_BRACE');
            $this->match('R_BRACE');
            if ($this->currentToken['type'] !== 'EOF') {
                $this->error("Unexpected token {$this->currentToken['value']}");
            }

            return true;
        } else {
            return false;
        }
    }

    private function match($expectedType) {
        if ($this->currentToken['type'] === $expectedType) {
            $this->currentToken = $this->lexer->getNextToken();
        } else {
            $this->error("Unexpected token {$this->currentToken['value']}");
        }
    }

    private function error($message) {
        fwrite(STDERR, "Error: $message\n");
        exit(1);
    }

    public function parseKeyValue() {
        $keyToken = $this->currentToken;
        $this->match('STRING'); // expecting a string key
        $this->match('COLON'); // Expecting a colon key
        $valueToken = $this->currentToken;
        $this->match('STRING'); // Expecting a string value
        return [$keyToken['value'], $valueToken['value']];
    }
    
    public function parseObject() {
        // parse the input as a JSON object
        if ($this->currentToken['type'] === 'L_BRACE') {
            $this->match('L_BRACE');
            $keyValuePairs = [];
            
          // Parse key-value pairs if the object is not empty
            if ($this->currentToken['type'] === 'R_BRACE') {
                $keyValuePairs[] = $this->parseKeyValue();
                while ($this->currentToken['type'] === 'COMMA') {
                    $this->match('COMMA');
                    $keyValuePairs[] = $this->parseKeyValue();
                }
            }

        $this->match('R_BRACE');
        return $keyValuePairs; // return key value pairs
        } else {
            return false; // invalide JSON object
        }
    }
}

// Read input from command line argument

if ($argc != 2 ) {
    die("usage: php jsonExtend.php <input>\n");
}

$input = $argv[1];

//create lexer and parser objects
$lexer = new JSONLexer($input);
$parser = new JSONParser($lexer);

// parse the input and check validibility

$object = $parser->parseObject();  // Call parseObject method instead of parse

if ($object !== false) {
    echo "Valid JSON object\n";
    print_r($object); // Output parsed object (for debugging)
    exit(0);
} else {
    echo "Invalid JSON object\n";
    exit(1);
}