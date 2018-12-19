<?php

namespace GraphQL\Document\Parser\Language\Document;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\util\getRepeating;
use function GraphQL\Document\Parser\util\arrayMergeUnique;

// Document : Definition+
function Document($string)
{
    [$definitions, $substr] = getRepeating(
        $string,
        function ($definitions, $string) {
            [$definition, $substr] = Definition($string);
            if ($definition === null) {
                return null;
            }

            if (isset($definition['name']) &&
                strpos($definition['name'], '__') === 0
            ) {
                Parser::throwInvalid(
                    "Definition `{$definition['name']}` cannot start with " .
                    "`__`.",
                    $string
                );
            }

            if ($definition['__Definition'] === 'TypeSystemExtension') {
                $name = $definition['name'] ?? '__schema';
                $type = $definition['type'];

                // 1. The named type must already be defined and must be the
                //    same type.
                if (!isset($definitions[$name])) {
                    if ($name === '__schema') {
                        $name = 'schema';
                    }

                    Parser::throwInvalid(
                        "Definition `$name` is not defined.",
                        $string
                    );
                }

                $oldDefinition = $definitions[$name];
                $oldType = $oldDefinition['type'];
                $identifier = $name === '__schema' ?
                    'schema' :
                    ($oldType . ' ' . $name);

                if ($oldType !== $type) {
                    Parser::throwInvalid(
                        "Cannot extend `$identifier` as `$type`.",
                        $string
                    );
                }

                // 2. Any directives provided must not already apply to the
                //    original type.
                if (isset($definition['directives'])) {
                    $oldDefinition['directives'] = arrayMergeUnique(
                        $oldDefinition['directives'] ?? [],
                        $definition['directives'],
                        $identifier,
                        $string,
                        'Directive'
                    );
                }

                if ($type === 'enum') {
                    if (isset($definition['values'])) {
                        $oldDefinition['values'] = arrayMergeUnique(
                            $oldDefinition['values'],
                            $definition['values'],
                            $identifier,
                            $string,
                            'Enum value'
                        );
                    }
                } elseif ($type === 'input') {
                    if (isset($definition['fields'])) {
                        $oldDefinition['fields'] = arrayMergeUnique(
                            $oldDefinition['fields'],
                            $definition['fields'],
                            $identifier,
                            $string,
                            'Field'
                        );
                    }
                } elseif ($type === 'interface') {
                    if (isset($definition['fields'])) {
                        $oldDefinition['fields'] = arrayMergeUnique(
                            $oldDefinition['fields'],
                            $definition['fields'],
                            $identifier,
                            $string,
                            'Field'
                        );
                    }
                } elseif ($type === 'scalar') {
                    // do nothing
                } elseif ($type === 'schema') {
                    if (isset($definition['operations'])) {
                        $oldDefinition['operations'] = arrayMergeUnique(
                            $oldDefinition['operations'],
                            $definition['operations'],
                            $identifier,
                            $string,
                            'Operation'
                        );
                    }
                } elseif ($type === 'type') {
                    if (isset($definition['fields'])) {
                        $oldDefinition['fields'] = arrayMergeUnique(
                            $oldDefinition['fields'],
                            $definition['fields'],
                            $identifier,
                            $string,
                            'Field'
                        );
                    }

                    if (isset($definition['implements'])) {
                        $oldDefinition['implements'] = arrayMergeUnique(
                            $oldDefinition['implements'] ?? [],
                            $definition['implements'],
                            $identifier,
                            $string,
                            'Implementation'
                        );
                    }
                } elseif ($type === 'union') {
                    if (isset($definition['types'])) {
                        $oldDefinition['types'] = arrayMergeUnique(
                            $oldDefinition['types'],
                            $definition['types'],
                            $identifier,
                            $string,
                            'Member type'
                        );
                    }
                } else {
                    Parser::throwInvalid(
                        "Cannot extend `$type`.",
                        $string
                    );
                }

                $definitions[$name] = $oldDefinition;
                return [$definitions, $substr];
            } elseif ($definition['__Definition'] === 'TypeSystemDefinition') {
                $name = $definition['name'] ?? '__schema';
                if (isset($definitions[$name])) {
                    if ($name === '__schema') {
                        Parser::throwInvalid(
                            "`schema` is already defined.",
                            $string
                        );
                    }

                    Parser::throwInvalid(
                        "Definition `$name` is already defined.",
                        $string
                    );
                }

                $definitions[$name] = $definition;
                return [$definitions, $substr];
            } elseif ($definition['__Definition'] === 'ExecutableDefinition') {
                $name = $definition['name'] ?? '__anonymous';
                if ($definition['type'] !== 'fragment') {
                    if ($name === '__anonymous') {
                        foreach ($definitions as $definition) {
                            if ($definition['type'] !== 'fragment') {
                                Parser::throwInvalid(
                                    "An anonymous operation must be the only " .
                                    "defined operation.",
                                    $string
                                );
                            }
                        }
                    }

                    if (isset($definitions['__anonymous'])) {
                        Parser::throwInvalid(
                            "An anonymous operation must be the only defined " .
                            "operation.",
                            $string
                        );
                    }
                }

                if (isset($definitions[$name])) {
                    Parser::throwInvalid(
                        "Operation `$name` is already defined.",
                        $string
                    );
                }

                $definitions[$name] = $definition;
                return [$definitions, $substr];
            }
        },
        [],
        1
    );
    if ($definitions === null) {
        Parser::throwSyntax('Definition', $substr);
    }

    return $definitions;
}
