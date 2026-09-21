<?php

declare(strict_types=1);

namespace App\Http\OpenApi;

/**
 * 収集した違反の葉を 422 details の field パス => メッセージ列へ整形する (ADR-0014)
 *
 * @phpstan-type leaf array{path: string, keyword: string, args: array<string, mixed>}
 */
final class BodyErrorFormatter
{
    /**
     * @param array<leaf> $leaves
     *
     * @return array<string, array<string>>
     */
    public function format(array $leaves): array
    {
        $errors = [];

        foreach ($leaves as $leaf) {
            // required は親のパスに missing の field 名を連結して報告する
            if ($leaf['keyword'] === 'required') {
                foreach ($this->missingFields($leaf) as $path) {
                    $errors[$path][] = SchemaErrorMessages::translate('required', []);
                }

                continue;
            }

            // nullable (anyOf + type: null) の null 側違反は、実型の違反があるときはノイズなので落とす
            if ($this->isNullBranchMismatch($leaf) && $this->hasOtherLeafAt($leaves, $leaf['path'])) {
                continue;
            }

            $errors[$leaf['path']][] = SchemaErrorMessages::translate($leaf['keyword'], $leaf['args']);
        }

        return array_map(static fn (array $messages): array => array_values(array_unique($messages)), $errors);
    }

    /**
     * @param leaf $leaf
     *
     * @return array<string>
     */
    private function missingFields(array $leaf): array
    {
        /** @var array<string> $missing */
        $missing = $leaf['args']['missing'] ?? [];

        return array_map(
            static fn (string $field): string => $leaf['path'] === '' ? $field : "{$leaf['path']}/{$field}",
            $missing,
        );
    }

    /**
     * @param leaf $leaf
     */
    private function isNullBranchMismatch(array $leaf): bool
    {
        return $leaf['keyword'] === 'type' && ($leaf['args']['expected'] ?? null) === 'null';
    }

    /**
     * @param array<leaf> $leaves
     */
    private function hasOtherLeafAt(array $leaves, string $path): bool
    {
        foreach ($leaves as $leaf) {
            if ($leaf['path'] === $path && ! $this->isNullBranchMismatch($leaf)) {
                return true;
            }
        }

        return false;
    }
}
