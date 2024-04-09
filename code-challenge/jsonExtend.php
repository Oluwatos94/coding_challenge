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
            case '[' :
                return ['type' => 'L_BRACKET', 'value' => '['];
            case ']' :
                return ['type' => 'R_BRACKET', 'value' => ']'];
            case ':' :
                return ['type' => 'COLON', 'Value' => ':'];
            case ',' :
                return ['type' => 'COMMA', 'value' => ','];
            case '"' :
                return $this->parseString();
            default:
                if (is_numeric($char) || $char === '-') {
                    return $this->parseNumber($char);
                } else {
                    $this->position--; // move back one position
                    return $this->parseKeyword();
                }
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

    private function parseNumber($firstChar) {
        // Implementation for parsing Number
        $value = $firstChar;
        while ($this->position < strlen($this->input)) {
            $char = $this->input[$this->position];
            if (is_numeric($char) || $char === '.' || $char === 'e' || $char === 'E' || $char === '-') {
                $value .= $char;
                $this->position++;
            } else {
                break;
            }
        }
        return ['type' => 'Number', 'value' => $value];
    }

    private function parseKeyword() {
        // implementation for parsing keywords
        $start = $this->position - 1;
        while ($this->position < strlen($this->input) && ctype_alpha($this->input[$this->position])) {
            $this->position++;
        }
        $keyword = substr($this->input, $start, $this->position - $start);
        if ($keyword === 'true' || $keyword === 'false') {
            return ['type' => 'BOOLEAN', 'value' => $keyword === 'true'];
        } elseif ($keyword === 'null') {
            return ['type' => 'NULL', 'value' => null];
        } else {
            return ['type' => 'INVALID', 'value' => $keyword];
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
            $object = $this->parseObject(); // parse the object
            $this->match('R_BRACE'); // match closing object brace
            if ($this->currentToken['type'] !== 'EOF') {
                $this->error("Unexpected token {$this->currentToken['value']}");
            }
            return $object;
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

    public function parseValue() {
        // parse a JSON vale (string, number, object, boolean, null)
        switch ($this->currentToken['type']) {
            case 'STRING' :
            case 'NUMBER' :
            case 'BOOLEAN' :
            case 'NULL' :
                return $this->currentToken['value'];
            case 'L_BRACE' :
                return $this->parseObject();
            case 'L_BRACKET' :
                return $this->parseArray();
            default:
                $this->error('unexpected token ' . $this->currentToken['value']);
        }
    }

    public function parseKeyValue() {
        // parse a key-value pair
        $keyToken = $this->currentToken;
        $this->match('STRING'); // expecting a string key
        $this->match('COLON'); // Expecting a colon key
        $value = $this->parseValue(); // parse the value
        return [$keyToken['value'], $value]; // return key-value pair
    }
    
    public function parseObject() {
        // Parse a JSON object
        $object = [];
        while ($this->currentToken['type'] !== 'R_BRACE') {
            $keyValue = $this->parseKeyValue(); // Parse a key-value pair
            $object[$keyValue[0]] = $keyValue[1]; // Add key-value pair to object
            if ($this->currentToken['type'] === 'COMMA') {
                $this->match('COMMA'); // Match comma between key-value pairs
            }
        }
        return $object;
    }

    public function parseArray() {
        // parse a JSON array
        $array = [];
        $this->match('L_BRACKET');
        if ($this->currentToken['type'] !== 'R_BRACKET') {
            $array[] = $this->parseValue();
            while ($this->currentToken['type'] === 'COMMA') {
                $this->match('COMMA');
                $array[] = $this->parseValue();
            }
        }
        $this->match('R_BRACKET');
        return $array;
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

$object = $parser->parse();  // Call parseObject method instead of parse

if ($object !== false) {
    echo "Valid JSON object\n";
    print_r($object); // Output parsed object (for debugging)
    exit(0);
} else {
    echo "Invalid JSON object\n";
    exit(1);
}