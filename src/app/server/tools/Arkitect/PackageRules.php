<?php

declare(strict_types=1);

namespace Tools\Arkitect;

use Arkitect\Expression\Expression;
use Arkitect\Expression\ForClasses\DependsOnlyOnTheseNamespaces;
use Arkitect\Expression\ForClasses\NotDependsOnTheseNamespaces;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\Rules\DSL\ArchRule;
use Arkitect\Rules\Rule;

/**
 * パッケージの階層表から PHPArkitect の規則を作る
 */
final readonly class PackageRules
{
    private const string KERNEL = 'Support';

    private const string APP = 'App';

    private const string BECAUSE = 'of package layering (ADR-0044)';

    /**
     * @param list<list<list<string>>>    $columns     列ごとに下の段から並べたパッケージ
     * @param array<string, list<string>> $kernel      Support の区分ごとに、自分のほかに依存してよい名前空間
     * @param list<string>                $domain      Domain が依存してよい名前空間
     * @param list<string>                $application Application が依存してよい名前空間
     * @param list<string>                $query       Application の Query が依存してよい名前空間
     */
    public function __construct(
        private array $columns,
        private array $kernel,
        private array $domain,
        private array $application,
        private array $query,
    ) {
    }

    /**
     * @return list<ArchRule>
     */
    public function rules(): array
    {
        $rules = $this->kernelRules();

        foreach ($this->columns as $tiers) {
            foreach ($tiers as $level => $packages) {
                $lowerDomains = array_map(
                    static fn (string $package): string => "{$package}\\Domain",
                    array_merge(...array_slice($tiers, 0, $level)),
                );

                foreach ($packages as $package) {
                    $rules = [...$rules, ...$this->packageRules($package, $lowerDomains)];
                }
            }
        }

        return $rules;
    }

    /**
     * @param list<string> $lowerDomains
     *
     * @return list<ArchRule>
     */
    private function packageRules(string $package, array $lowerDomains): array
    {
        $others = array_values(array_diff($this->packages(), [$package]));
        $query = "{$package}\\Application\\*\\Query";

        return [
            $this->rule(
                $package,
                new NotDependsOnTheseNamespaces([...$others, self::APP], $lowerDomains),
            ),
            $this->rule(
                "{$package}\\Domain",
                new DependsOnlyOnTheseNamespaces(["{$package}\\Domain", ...$lowerDomains, ...$this->domain]),
            ),
            $this->rule(
                "{$package}\\Application",
                new DependsOnlyOnTheseNamespaces([
                    "{$package}\\Domain",
                    "{$package}\\Application",
                    ...$lowerDomains,
                    ...$this->application,
                ]),
            ),
            $this->rule(
                $query,
                new DependsOnlyOnTheseNamespaces(["{$package}\\Domain", $query, ...$lowerDomains, ...$this->query]),
            ),
            $this->rule(
                "{$package}\\Infrastructures",
                new NotDependsOnTheseNamespaces(["{$package}\\Application"], [$query]),
            ),
        ];
    }

    /**
     * @return list<ArchRule>
     */
    private function kernelRules(): array
    {
        $rules = [$this->rule(self::KERNEL, new NotDependsOnTheseNamespaces([...$this->packages(), self::APP]))];

        foreach ($this->kernel as $namespace => $dependencies) {
            $rules[] = $this->rule($namespace, new DependsOnlyOnTheseNamespaces([$namespace, ...$dependencies]));
        }

        return $rules;
    }

    /**
     * @return list<string>
     */
    private function packages(): array
    {
        return array_merge(...array_merge(...$this->columns));
    }

    private function rule(string $namespace, Expression $expression): ArchRule
    {
        return Rule::allClasses()
            ->that(new ResideInOneOfTheseNamespaces($namespace))
            ->should($expression)
            ->because(self::BECAUSE);
    }
}
