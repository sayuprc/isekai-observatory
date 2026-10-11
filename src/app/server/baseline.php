<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\.\\.\\.\\$constructors of method CuyZ\\\\Valinor\\\\MapperBuilder\\:\\:registerConstructor\\(\\) expects \\(pure\\-callable\\(\\)\\: mixed\\)\\|class\\-string, Closure\\(string, string, int\\)\\: Person\\\\Domain\\\\Models\\\\Person given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/packages/Support/Infrastructures/Valinor/ApplicationMapperConfigurator.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\.\\.\\.\\$constructors of method CuyZ\\\\Valinor\\\\MapperBuilder\\:\\:registerConstructor\\(\\) expects \\(pure\\-callable\\(\\)\\: mixed\\)\\|class\\-string, Closure\\(string, string, string, DateTimeImmutable, int\\)\\: Auth\\\\Domain\\\\Models\\\\Token\\\\RefreshToken\\\\RefreshToken given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/packages/Support/Infrastructures/Valinor/ApplicationMapperConfigurator.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\.\\.\\.\\$constructors of method CuyZ\\\\Valinor\\\\MapperBuilder\\:\\:registerConstructor\\(\\) expects \\(pure\\-callable\\(\\)\\: mixed\\)\\|class\\-string, Closure\\(string, string, string, DateTimeImmutable, int, list\\<string\\>\\)\\: AdminUser\\\\Domain\\\\Models\\\\AdminUser given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/packages/Support/Infrastructures/Valinor/ApplicationMapperConfigurator.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\.\\.\\.\\$constructors of method CuyZ\\\\Valinor\\\\MapperBuilder\\:\\:registerConstructor\\(\\) expects \\(pure\\-callable\\(\\)\\: mixed\\)\\|class\\-string, Closure\\(string, string, string, string\\|null, int, bool, int, list\\<array\\{songTagId\\: string\\}\\>, list\\<array\\{personId\\: string, role\\: int, orderNo\\: int\\}\\>, list\\<array\\{mediaId\\: string, orderNo\\: int\\}\\>\\)\\: Song\\\\Domain\\\\Models\\\\Song given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/packages/Support/Infrastructures/Valinor/ApplicationMapperConfigurator.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
