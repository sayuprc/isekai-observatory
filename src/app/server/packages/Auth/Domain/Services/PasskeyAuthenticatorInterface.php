<?php

declare(strict_types=1);

namespace Auth\Domain\Services;

use Auth\Domain\Models\AdminUserPasskey;

interface PasskeyAuthenticatorInterface
{
    /**
     * @param array<int, AdminUserPasskey> $excludePasskeys
     */
    public function startRegistration(
        string $userHandle,
        string $userName,
        string $displayName,
        array $excludePasskeys = [],
    ): PasskeyStartResult;

    /**
     * @param string $credentialJson ブラウザが返した PublicKeyCredential の JSON
     */
    public function finishRegistration(string $credentialJson, string $optionsJson): PasskeyRegistrationResult;

    /**
     * ユーザー列挙を防ぐため allowCredentials は空配列で返し資格情報を列挙しない
     * (discoverable credential を利用)。入力メールの実在有無に依らず同一形状の
     * オプションを返す
     */
    public function startAuthentication(): PasskeyStartResult;

    /**
     * @param string $credentialJson ブラウザが返した PublicKeyCredential の JSON
     */
    public function credentialId(string $credentialJson): ?string;

    /**
     * @param string $credentialJson ブラウザが返した PublicKeyCredential の JSON
     */
    public function finishAuthentication(
        string $credentialJson,
        string $optionsJson,
        AdminUserPasskey $passkey,
        string $userHandle,
    ): PasskeyAuthenticationResult;
}
