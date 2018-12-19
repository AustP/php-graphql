<?php

namespace GraphQL\Document\Parser\TypeSystem\Schema;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\util\getRepeating;
use function GraphQL\Document\Parser\util\Keyword;

/*
SchemaExtension :
- extend schema Directives[Const]? { OperationTypeDefinition+ }
- extend schema Directives[Const]
 */
function SchemaExtension($string)
{
    [$extend, $substr] = Keyword($string, 'extend');
    if ($extend !== 'extend') {
        return [null, $string];
    }

    [$schema, $substr] = Keyword($substr, 'schema');
    if ($schema !== 'schema') {
        return [null, $string];
    }

    [$directives, $substr] = DirectivesConst($substr, 'SCHEMA');
    [$leftBracket, $nextSubstr] = Punctuator($substr);
    if ($leftBracket !== '{') {
        if ($directives === null) {
            Parser::throwSyntax('DirectivesConst or {', $substr);
        } else {
            return [['directives' => $directives], $substr];
        }
    }

    [$operations, $substr] = getRepeating(
        $nextSubstr,
        function ($operations, $substr) {
            [$operation, $nextSubstr] = OperationTypeDefinition($substr);
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
        Parser::throwSyntax('OperationTypeDefinition', $substr);
    }

    [$rightBracket, $nextSubstr] = Punctuator($substr);
    if ($rightBracket !== '}') {
        Parser::throwSyntax('}', $substr);
    }

    $extension = [];

    if ($directives !== null) {
        $extension['directives'] = $directives;
    }

    $extension['operations'] = $operations;
    $extension['type'] = 'schema';

    return [$extension, $nextSubstr];
}
