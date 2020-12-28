<?php

declare(strict_types=1);

return [
    // List of case-insensitive file extensions supported by Phan.
    // (e.g. php, html, htm)
    "analyzed_file_extensions" => ["php"],

    // A list of directories that should be parsed for class and
    // method information. After excluding the directories
    // defined in exclude_analysis_directory_list, the remaining
    // files will be statically analyzed for errors.
    //
    // Thus, both first-party and third-party code being used by
    // your application should be included in this list.
    "directory_list" => [
        ".",
    ],

    // A list of directories holding code that we want
    // to parse, but not analyze
    "exclude_analysis_directory_list" => [
        "vendor/",
    ],

    "target_php_version" => "8.0",

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
