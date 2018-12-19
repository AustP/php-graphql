<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use function GraphQL\Document\Parser\Language\SourceText\LineTerminator;
use function GraphQL\Document\Parser\Language\SourceText\SourceCharacter;
use function GraphQL\Document\Parser\Language\SourceText\WhiteSpace;
use function GraphQL\Document\Parser\util\getRepeating;
use function GraphQL\Document\Parser\util\LexicalToken;

/*
StringValue ::
- " StringCharacter* "
- """ BlockStringCharacter* """
 */
function StringValue($string)
{
    return LexicalToken($string, function ($string) {
        $chars = substr($string, 0, 3);
        if ($chars === '"""') {
            [$value, $substr] = getRepeating(
                substr($string, 3),
                function ($value, $substr) {
                    [$char, $substr] = BlockStringCharacter($substr);
                    if ($char === null) {
                        return null;
                    }

                    return [$value . $char, $substr];
                },
                ''
            );

            $chars = substr($substr, 0, 3);
            if ($chars === '"""') {
                return [
                    ['type' => 'String', 'value' => BlockStringValue($value)],
                    substr($substr, 3)
                ];
            }

            return [null, $string];
        }

        if (($chars[0] ?? '') === '"') {
            [$value, $substr] = getRepeating(
                substr($string, 1),
                function ($value, $substr) {
                    [$char, $substr] = StringCharacter($substr);
                    if ($char === null) {
                        return null;
                    }

                    return [$value . $char, $substr];
                },
                ''
            );

            $char = substr($substr, 0, 1);
            if ($char === '"') {
                return [
                    ['type' => 'String', 'value' => $value],
                    substr($substr, 1)
                ];
            }

            return [null, $string];
        }

        return [null, $string];
    });
}

// BlockStringValue(rawValue):
function BlockStringValue($rawValue)
{
    // Let {lines} be the result of splitting {rawValue} by {LineTerminator}.
    $lines = [];

    $line = '';
    $substr = $rawValue;
    while (true) {
        if (strlen($substr) === 0) {
            break;
        }

        [$char, $charSubstr] = SourceCharacter($substr);
        [$terminator, $terminatorSubstr] = LineTerminator($substr);
        if ($terminator !== null) {
            $lines[] = $line;
            $line = '';

            $substr = $terminatorSubstr;
        } else {
            $line .= $char;
            $substr = $charSubstr;
        }
    }

    $lines[] = $line;
    $line = '';

    // Let {commonIndent} be {null}.
    $commonIndent = null;

    // For each {line} in {lines}:
    foreach ($lines as $i => $line) {
        // If {line} is the first item in {lines}, continue to the next line.
        if ($i === 0) {
            continue;
        }

        // Let {length} be the number of characters in {line}.
        $length = strlen($line);

        // Let {indent} be the number of leading consecutive {WhiteSpace}
        // characters in {line}.
        $substr = $line;
        $indent = 0;
        while (true) {
            [$whitespace, $nextSubstr] = WhiteSpace($substr);
            if ($whitespace === null) {
                break;
            }

            $indent++;
            $substr = $nextSubstr;
        }

        // If {indent} is less than {length}:
        if ($indent < $length) {
            // If {commonIndent} is {null} or {indent} is less than
            // {commonIndent}:
            if ($commonIndent === null || $indent < $commonIndent) {
                // Let {commonIndent} be {indent}.
                $commonIndent = $indent;
            }
        }
    }

    // If {commonIndent} is not {null}:
    if ($commonIndent !== null) {
        // For each {line} in {lines}:
        foreach ($lines as $i => $line) {
            // If {line} is the first item in {lines}, continue to the next
            // line.
            if ($i === 0) {
                continue;
            }

            // Remove {commonIndent} characters from the beginning of {line}.
            $lines[$i] = substr($line, $commonIndent);
        }
    }

    // While the first item {line} in {lines} contains only {WhiteSpace}:
    while (true) {
        $substr = $lines[0] ?? null;
        if ($substr === null) {
            break;
        }

        while (true) {
            [$whitespace, $nextSubstr] = WhiteSpace($substr);
            if (strlen($nextSubstr) === 0) {
                // Remove the first item from {lines}.
                array_shift($lines);
                break;
            }

            if ($whitespace === null) {
                break 2;
            }

            $substr = $nextSubstr;
        }
    }

    // While the last item {line} in {lines} contains only {WhiteSpace}:
    while (true) {
        $substr = $lines[count($lines) - 1] ?? null;
        if ($substr === null) {
            break;
        }

        while (true) {
            [$whitespace, $nextSubstr] = WhiteSpace($substr);
            if (strlen($nextSubstr) === 0) {
                // Remove the last item from {lines}.
                array_pop($lines);
                break;
            }

            if ($whitespace === null) {
                break 2;
            }

            $substr = $nextSubstr;
        }
    }

    // Let {formatted} be the empty character sequence.
    $formatted = '';

    // For each {line} in {lines}:
    foreach ($lines as $i => $line) {
        // If {line} is the first item in {lines}:
        if ($i === 0) {
            // Append {formatted} with {line}.
            $formatted .= $line;

        // Otherwise:
        } else {
            // Append {formatted} with a line feed character (U+000A).
            $formatted .= "\u{000A}";

            // Append {formatted} with {line}.
            $formatted .= $line;
        }
    }

    // Return {formatted}.
    return $formatted;
}
