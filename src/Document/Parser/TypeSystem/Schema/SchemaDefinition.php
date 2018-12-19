<?php

namespace GraphQL\Document\Parser\TypeSystem\Schema;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\util\getRepeating;
use function GraphQL\Document\Parser\util\Keyword;

// SchemaDefinition : schema Directives[Const]? { RootOperationTypeDefinition+ }
function SchemaDefinition($string)
{
    [$schema, $substr] = Keyword($string, 'schema');
    if ($schema !== 'schema') {
        return [null, $string];
    }

    [$directives, $substr] = DirectivesConst($substr, 'SCHEMA');
    [$leftBracket, $nextSubstr] = Punctuator($substr);
    if ($leftBracket !== '{') {
        Parser::throwSyntax('{', $substr);
    }

    [$operations, $substr] = getRepeating(
        $nextSubstr,
        function ($operations, $substr) {
            [$operation, $nextSubstr] = RootOperationTypeDefinition($substr);
            if ($operation === null) {
                return null;
            }

            if (isset($operations[$operation['type']])) {
                Parser::throwInvalid(
                    "Operation `{$operation['type']}` is already defined.",
                    $substr
                );
            }

            $operations[$operation['type']] = ['name' => $operation['value']];
            return [$operations, $nextSubstr];
        },
        [],
        1
    );
    if ($operations === null) {
        Parser::throwSyntax('RootOperationTypeDefinition', $substr);
    }

    [$rightBracket, $nextSubstr] = Punctuator($substr);
    if ($rightBracket !== '}') {
        Parser::throwSyntax('}', $substr);
    }

    $definition = [];

    if ($directives !== null) {
        $definition['directives'] = $directives;
    }

    $definition['operations'] = $operations;
    $definition['type'] = 'schema';

    return [$definition, $nextSubstr];
}
