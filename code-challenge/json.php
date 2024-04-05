<?php

class JSONLexe
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

class JSONPaser
{
    private $lexer;
    private $currentToken;

    public function __construct($lexer) {
        $this->lexer = $lexer;
        $this->currentToken = $this->lexer->generateNextToken;
    }

    public function parse() {
        if ($this->currentToken['type'] === 'L_BRACE') {
            $this->match('L_BRACE');
            $this->match('R_BRACE');
            $this->match('EOL');

            return true;
        } else {
            return false;
        }
    }

    private function match($expectedType) {
        if ($this->currentToken['type'] === $expectedType) {
            $this->currentToken = $this->lexer->generatedNextToken();
        } else {
            $this->error("Eexpected {$expectedType}, but got {$this->currentToken['type']}");
        }
    }

    private function error($message) {
        fwrite(STDERR, "Error: $message\n");
        exit(1);
    }
}