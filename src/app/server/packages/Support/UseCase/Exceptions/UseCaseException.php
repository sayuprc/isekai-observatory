<?php

declare(strict_types=1);

namespace Support\UseCase\Exceptions;

use RuntimeException;

/**
 * 期待される業務エラーを表す UseCase 層の例外の基底
 *
 * app 層の ApiExceptionRenderer が具象クラスごとに HTTP へ変換する
 */
abstract class UseCaseException extends RuntimeException
{
}
