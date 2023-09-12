Coding standards
================

As for all our application we use a custom set of coding standards.
Here is a description and links to documentation to help follow them :
- [PSR-1](https://www.php-fig.org/psr/psr-1/) for files side-effects and classes declarations 

- [PSR-2](https://www.php-fig.org/psr/psr-2/) for namespaces, uses, classes, properties, methods, elseif, and switch  
  declarations as well as control structure spacing and end file new line

- [PHPCS/Generic](https://github.com/squizlabs/PHP_CodeSniffer/tree/master/src/Standards/Generic/Docs) for 
  [CodeAnalysis/EmptyStatement](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/CodeAnalysis/EmptyStatementStandard.xml),
  [CodeAnalysis/ForLoopShouldBeWhileLoop](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/CodeAnalysis/ForLoopShouldBeWhileLoopStandard.xml),
  [CodeAnalysis/ForLoopWithTestFunctionCall](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/CodeAnalysis/ForLoopWithTestFunctionCallStandard.xml),
  [CodeAnalysis/JumbledIncrementer](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/CodeAnalysis/JumbledIncrementerStandard.xml),
  [CodeAnalysis/UnconditionalIfStatement](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/CodeAnalysis/UnconditionalIfStatementStandard.xml),
  [CodeAnalysis/UnnecessaryFinalModifier](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/CodeAnalysis/UnnecessaryFinalModifierStandard.xml),
  [CodeAnalysis/UselessOverridingMethod](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/CodeAnalysis/UselessOverridingMethodStandard.xml),
  [ControlStructures/InlineControlStructure](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/ControlStructures/InlineControlStructureStandard.xml),
  [Files/ByteOrderMark](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/Files/ByteOrderMarkStandard.xml),
  [Files/LineEndings](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/Files/LineEndingsStandard.xml),
  [Files/LineLength](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/Files/LineLengthStandard.xml) with a hard limit at 120 char,
  [Formatting/DisallowMultipleStatements](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/Formatting/DisallowMultipleStatementsStandard.xml),
  [Formatting/MultipleStatementAlignment](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/Formatting/MultipleStatementAlignmentStandard.xml),
  [Formatting/SpaceAfterCast](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/Formatting/SpaceAfterCastStandard.xml),
  [Functions/CallTimePassByReference](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/Functions/CallTimePassByReferenceStandard.xml),
  [Functions/FunctionCallArgumentSpacing](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/Functions/FunctionCallArgumentSpacingStandard.xml),
  [Functions/OpeningFunctionBraceBsdAllman](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/Functions/OpeningFunctionBraceBsdAllmanStandard.xml),
  [NamingConventions/ConstructorName](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/NamingConventions/ConstructorNameStandard.xml),
  [NamingConventions/UpperCaseConstantName](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/NamingConventions/UpperCaseConstantNameStandard.xml),
  [NamingConventions/CamelCapsFunctionName](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/NamingConventions/CamelCapsFunctionNameStandard.xml),
  [PHP/CharacterBeforePHPOpeningTag](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/PHP/CharacterBeforePHPOpeningTagStandard.xml),
  [PHP/DeprecatedFunctions](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/PHP/DeprecatedFunctionsStandard.xml),
  [PHP/DisallowShortOpenTag](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/PHP/DisallowShortOpenTagStandard.xml),
  [PHP/ForbiddenFunctions](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/PHP/ForbiddenFunctionsStandard.xml),
  [PHP/LowerCaseConstant](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/PHP/LowerCaseConstantStandard.xml),
  [PHP/NoSilencedErrors](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/PHP/NoSilencedErrorsStandard.xml),
  [WhiteSpace/DisallowTabIndent](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/WhiteSpace/DisallowTabIndentStandard.xml),
  [WhiteSpace/ScopeIndent](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Generic/Docs/WhiteSpace/ScopeIndentStandard.xml),

- [PHPCS/MySource](https://github.com/squizlabs/PHP_CodeSniffer/tree/master/src/Standards/MySource) for 
  [PHP/GetRequestData](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/MySource/Sniffs/PHP/GetRequestDataSniff.php),

- [PHPCS/Squiz](https://github.com/squizlabs/PHP_CodeSniffer/tree/master/src/Standards/Squiz/Docs) for
  [PHP/DisallowSizeFunctionsInLoops](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/PHP/DisallowSizeFunctionsInLoopsSniff.php),
  [PHP/Eval](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/PHP/EvalSniff.php),
  [PHP/ForbiddenFunctions](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/PHP/ForbiddenFunctionsSniff.php) with functions exit, var_dump, sizeof, delete, print_r, eval,
  [PHP/GlobalKeyword](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/PHP/GlobalKeywordSniff.php),
  [PHP/InnerFunctions](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/PHP/InnerFunctionsSniff.php),
  [PHP/LowercasePHPFunctions](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/PHP/LowercasePHPFunctionsSniff.php),
  [PHP/NonExecutableCode](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/PHP/NonExecutableCodeSniff.php),
  [Scope/MemberVarScope](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/Scope/MemberVarScopeSniff.php),
  [Scope/MethodScope](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/Scope/MethodScopeSniff.php),
  [Scope/StaticThisUsage](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Docs/Scope/StaticThisUsageStandard.xml),
  [WhiteSpace/CastSpacing](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Docs/WhiteSpace/CastSpacingStandard.xml),
  [WhiteSpace/LanguageConstructSpacing](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Docs/WhiteSpace/LanguageConstructSpacingStandard.xml),
  [WhiteSpace/ScopeKeywordSpacing](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Docs/WhiteSpace/ScopeKeywordSpacingStandard.xml),
  [WhiteSpace/LogicalOperatorSpacing](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/WhiteSpace/LogicalOperatorSpacingSniff.php),
  [WhiteSpace/OperatorSpacing](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/WhiteSpace/OperatorSpacingSniff.php),
  [WhiteSpace/ScopeClosingBrace](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/WhiteSpace/ScopeClosingBraceSniff.php),
  [WhiteSpace/SuperfluousWhitespace](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/WhiteSpace/SuperfluousWhitespaceSniff.php),
  
  [Squiz/ArrayDeclarationSniff:NoCommaAfterLast,CommaAfterLast,FirstValueNoNewline](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/src/Standards/Squiz/Sniffs/Arrays/ArrayDeclarationSniff.php)
  