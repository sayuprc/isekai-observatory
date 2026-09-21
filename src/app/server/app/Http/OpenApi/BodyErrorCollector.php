<?php

declare(strict_types=1);

namespace App\Http\OpenApi;

use InvalidArgumentException;
use League\OpenAPIValidation\PSR7\OperationAddress;
use LogicException;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\SchemaException;
use Opis\JsonSchema\Validator;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * 契約スキーマに対して request body の違反を全件収集する (ADR-0014)
 *
 * League validator は fail-fast で 1 件しか報告できないため、invalid と判定された
 * リクエストのエラー詳細化にのみ使う。通過判定そのものは League に委ねる
 *
 * @phpstan-import-type leaf from BodyErrorFormatter
 */
final class BodyErrorCollector
{
    private const string SCHEMA_ID = 'schema:openapi';

    private const int MAX_ERRORS = 100;

    /** @var array<string, Validator> yaml パスごとに初期化した validator */
    private static array $validators = [];

    public function __construct(
        private readonly string $yamlPath,
        private readonly BodyErrorFormatter $formatter = new BodyErrorFormatter(),
    ) {
    }

    /**
     * @return array<string, array<string>> field パス => メッセージ列。収集できない場合は空
     */
    public function collect(OperationAddress $address, string $rawBody): array
    {
        $body = json_decode($rawBody);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        // paths のキーは JSON pointer のトークンとして ~ / を退避した上で、
        // {param} の波括弧が URI fragment として不正にならないよう percent-encode する
        $pointer = sprintf(
            '%s#/paths/%s/%s/requestBody/content/application~1json/schema',
            self::SCHEMA_ID,
            rawurlencode(str_replace(['~', '/'], ['~0', '~1'], $address->path())),
            strtolower($address->method()),
        );

        try {
            $result = $this->validator()->validate($body, $pointer);
        } catch (InvalidArgumentException|RuntimeException|SchemaException) {
            // 対象 operation に JSON body スキーマがない (GET / multipart 等)
            // opis は pointer 未解決を素の RuntimeException で報告するため合わせて握る
            return [];
        }

        $error = $result->error();

        if (is_null($error)) {
            return [];
        }

        /** @var array<leaf> $leaves */
        $leaves = [];
        $this->flatten($error, $leaves);

        return $this->formatter->format($leaves);
    }

    private function validator(): Validator
    {
        $validator = self::$validators[$this->yamlPath] ?? null;

        if (is_null($validator)) {
            $document = json_decode(json_encode(Yaml::parseFile($this->yamlPath), JSON_THROW_ON_ERROR), flags: JSON_THROW_ON_ERROR);

            // collect の catch (RuntimeException) に握られないよう、設定バグは LogicException で区別する
            if (! is_object($document)) {
                throw new LogicException("OpenAPI ドキュメントを object として解釈できません: {$this->yamlPath}");
            }

            $validator = new Validator();
            $validator->setMaxErrors(self::MAX_ERRORS);
            $validator->resolver()?->registerRaw($document, self::SCHEMA_ID);

            self::$validators[$this->yamlPath] = $validator;
        }

        return $validator;
    }

    /**
     * エラーツリーを葉の (field パス, keyword, args) へ潰す
     *
     * @param array<leaf> $leaves
     */
    private function flatten(ValidationError $error, array &$leaves): void
    {
        /** @var array<ValidationError> $subErrors */
        $subErrors = $error->subErrors();

        if ($subErrors !== []) {
            foreach ($subErrors as $subError) {
                $this->flatten($subError, $leaves);
            }

            return;
        }

        /** @var array<string, mixed> $args */
        $args = $error->args();

        $leaves[] = [
            'path' => implode('/', $error->data()->fullPath()),
            'keyword' => $error->keyword(),
            'args' => $args,
        ];
    }
}
