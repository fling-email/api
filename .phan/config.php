<?php

declare(strict_types=1);

return [
    "analyzed_file_extensions" => ["php"],
    "directory_list" => [
        ".",
    ],

    "exclude_file_list" => [
        "vendor/illuminate/database/Eloquent/Collection.php",
    ],
    "exclude_analysis_directory_list" => [
        ".phan/",
        "vendor/",
    ],

    "target_php_version" => "8.1",

    "enable_extended_internal_return_type_plugins" => true,

    "plugins" => [
        "AlwaysReturnPlugin",
        "DollarDollarPlugin",

        "DuplicateArrayKeyPlugin",
        "DuplicateConstantPlugin",
        "DuplicateExpressionPlugin",

        "PregRegexCheckerPlugin",
        "SleepCheckerPlugin",
        "UnreachableCodePlugin",

        "UseReturnValuePlugin",
        "EmptyStatementListPlugin",
        "LoopVariableReusePlugin",

        "NotFullyQualifiedUsagePlugin",
        "AvoidableGetterPlugin",
        "InvalidVariableIssetPlugin",

        "StrictComparisonPlugin",
        "StrictLiteralComparisonPlugin",

        "NonBoolBranchPlugin",
        "NonBoolInLogicalArithPlugin",

        "UnknownElementTypePlugin",
        "UnknownClassElementAccessPlugin",
    ],
];
