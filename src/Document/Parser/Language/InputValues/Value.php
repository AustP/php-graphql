<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use function GraphQL\Document\Parser\Language\Variables\Variable;

/*
Value[Const] :
- [~Const] Variable
- IntValue
- FloatValue
- StringValue
- BooleanValue
- NullValue
- EnumValue
- ListValue[?Const]
- ObjectValue[?Const]
 */
function Value($string)
{
    [$variable, $substr] = Variable($string);
    if ($variable !== null) {
        return [$variable, $substr];
    }

    [$int, $substr] = IntValue($string);
    if ($int !== null) {
        return [$int, $substr];
    }

    [$float, $substr] = FloatValue($string);
    if ($float !== null) {
        return [$float, $substr];
    }

    [$value, $substr] = StringValue($string);
    if ($value !== null) {
        return [$value, $substr];
    }

    [$bool, $substr] = BooleanValue($string);
    if ($bool !== null) {
        return [$bool, $substr];
    }

    [$null, $substr] = NullValue($string);
    if ($null !== null) {
        return [$null, $substr];
    }

    [$enum, $substr] = EnumValue($string);
    if ($enum !== null) {
        return [$enum, $substr];
    }

    [$list, $substr] = ListValue($string);
    if ($list !== null) {
        return [$list, $substr];
    }

    [$object, $substr] = ObjectValue($string);
    if ($object !== null) {
        return [$object, $substr];
    }

    return [null, $string];
}

function ValueConst($string)
{
    [$int, $substr] = IntValue($string);
    if ($int !== null) {
        return [$int, $substr];
    }

    [$float, $substr] = FloatValue($string);
    if ($float !== null) {
        return [$float, $substr];
    }

    [$value, $substr] = StringValue($string);
    if ($value !== null) {
        return [$value, $substr];
    }

    [$bool, $substr] = BooleanValue($string);
    if ($bool !== null) {
        return [$bool, $substr];
    }

    [$null, $substr] = NullValue($string);
    if ($null !== null) {
        return [$null, $substr];
    }

    [$enum, $substr] = EnumValue($string);
    if ($enum !== null) {
        return [$enum, $substr];
    }

    [$list, $substr] = ListValueConst($string);
    if ($list !== null) {
        return [$list, $substr];
    }

    [$object, $substr] = ObjectValueConst($string);
    if ($object !== null) {
        return [$object, $substr];
    }

    return [null, $string];
}
