<?php

namespace App\Utilities;

/**
 * Safe mathematical expression evaluator without eval().
 * Supports basic arithmetic (+, -, *, /), decimal numbers, unary operators, and nested parentheses.
 */
class SafeMathEvaluator
{
    private string $expr;
    private int $pos = 0;
    private int $len = 0;

    /**
     * Safely evaluate a mathematical string expression.
     *
     * @param string $raw
     * @return float|int
     * @throws \InvalidArgumentException
     */
    public static function evaluate(string $raw): float|int
    {
        $raw = trim($raw);
        if ($raw === '') {
            throw new \InvalidArgumentException('Invalid mathematical expression: empty input.');
        }

        // Whitelist allowed characters: digits, comma, period, +, -, *, /, x, X, parentheses, whitespace
        if (!preg_match('/^[0-9,+\-x*\/().\s]+$/i', $raw)) {
            throw new \InvalidArgumentException('Invalid characters in mathematical expression.');
        }

        // Normalize decimal comma to dot, x/X to multiplication *
        $normalized = str_replace(',', '.', $raw);
        $normalized = str_ireplace('x', '*', $normalized);

        $evaluator = new self($normalized);
        $result = $evaluator->parse();

        if (!is_finite($result)) {
            throw new \InvalidArgumentException('Calculation produced non-finite result.');
        }

        // Return int if exact integer, otherwise float
        return (floor($result) == $result && !is_nan($result)) ? (int) $result : (float) $result;
    }

    private function __construct(string $expr)
    {
        $this->expr = $expr;
        $this->pos = 0;
        $this->len = strlen($expr);
    }

    private function skipWhitespace(): void
    {
        while ($this->pos < $this->len && ctype_space($this->expr[$this->pos])) {
            $this->pos++;
        }
    }

    private function peek(): ?string
    {
        $this->skipWhitespace();
        return $this->pos < $this->len ? $this->expr[$this->pos] : null;
    }

    private function getChar(): ?string
    {
        $this->skipWhitespace();
        return $this->pos < $this->len ? $this->expr[$this->pos++] : null;
    }

    public function parse(): float
    {
        $result = $this->parseExpression();
        $this->skipWhitespace();
        if ($this->pos < $this->len) {
            throw new \InvalidArgumentException("Unexpected character '{$this->expr[$this->pos]}' at position {$this->pos}.");
        }
        return $result;
    }

    /**
     * Level 3: Addition and Subtraction
     */
    private function parseExpression(): float
    {
        $result = $this->parseTerm();

        while (true) {
            $op = $this->peek();
            if ($op === '+' || $op === '-') {
                $this->getChar();
                $term = $this->parseTerm();
                if ($op === '+') {
                    $result += $term;
                } else {
                    $result -= $term;
                }
            } else {
                break;
            }
        }

        return $result;
    }

    /**
     * Level 2: Multiplication and Division
     */
    private function parseTerm(): float
    {
        $result = $this->parseFactor();

        while (true) {
            $op = $this->peek();
            if ($op === '*' || $op === '/') {
                $this->getChar();
                $factor = $this->parseFactor();
                if ($op === '*') {
                    $result *= $factor;
                } else {
                    if ($factor == 0.0) {
                        throw new \InvalidArgumentException('Division by zero.');
                    }
                    $result /= $factor;
                }
            } else {
                break;
            }
        }

        return $result;
    }

    /**
     * Level 1: Unary operators, Parentheses, and Numbers
     */
    private function parseFactor(): float
    {
        $this->skipWhitespace();
        $ch = $this->peek();

        if ($ch === null) {
            throw new \InvalidArgumentException('Unexpected end of expression.');
        }

        // Unary +
        if ($ch === '+') {
            $this->getChar();
            return $this->parseFactor();
        }

        // Unary -
        if ($ch === '-') {
            $this->getChar();
            return -$this->parseFactor();
        }

        // Nested expression in parentheses
        if ($ch === '(') {
            $this->getChar(); // consume '('
            $result = $this->parseExpression();
            $closing = $this->getChar();
            if ($closing !== ')') {
                throw new \InvalidArgumentException('Mismatched parentheses: missing closing bracket.');
            }
            return $result;
        }

        // Numeric literal
        if (ctype_digit($ch) || $ch === '.') {
            return $this->parseNumber();
        }

        throw new \InvalidArgumentException("Unexpected character '{$ch}' at position {$this->pos}.");
    }

    private function parseNumber(): float
    {
        $start = $this->pos;
        $hasDot = false;

        while ($this->pos < $this->len) {
            $c = $this->expr[$this->pos];
            if (ctype_digit($c)) {
                $this->pos++;
            } elseif ($c === '.' && !$hasDot) {
                $hasDot = true;
                $this->pos++;
            } else {
                break;
            }
        }

        $numStr = substr($this->expr, $start, $this->pos - $start);
        if ($numStr === '.' || $numStr === '') {
            throw new \InvalidArgumentException('Malformed number in expression.');
        }

        return (float) $numStr;
    }
}
