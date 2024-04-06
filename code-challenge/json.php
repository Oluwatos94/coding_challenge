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
            default:
                return ['type' => 'INVALID', 'value' => $char];
        }

    }
}

class JSONParser
{
    private $lexer;
    private $currentToken;

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
}

// Read input from command line argument

if ($argc != 2 ) {
    die("usage: php json.php <input>\n");
}

$input = $argv[1];

//create lexer and parser objects
$lexer = new JSONLexer($input);
$parser = new JSONParser($lexer);

// parse the input and check validibility

if ($parser->parse()) {
    echo "valid JSON object\n";
    exit(0);
} else {
    echo "invalid JSON object\n";
    exit(1);
}