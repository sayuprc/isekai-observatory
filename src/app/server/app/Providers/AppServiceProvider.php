<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $url = config()->string('app.url');

        URL::forceScheme(str_starts_with($url, 'https') ? 'https' : 'http');

        $this->registerPasskeyRateLimiters();
    }

    /**
     * 認証系エンドポイントのレート制限
     *
     * BFF 経由だと送信元 IP が単一に集約されるため、IP ではなく攻撃者が
     * 差し替えられない「標的の資源キー」で絞る
     *
     * - start 系は標的アカウントの email をキーにする (攻撃者は被害者の email を
     *   使わざるを得ないため有効)
     * - refresh は標的トークンの refreshTokenId をキーにする (secret 総当たりは
     *   id を固定して試すしかなく、失敗時はトークン未消費なので上限が効く)
     *
     * finish 系は ceremony が単回消費 (pull で削除) のため同一 id で叩き直せず、
     * 認証自体も公開鍵暗号で守られるため throttle を持たない。finish への大量
     * リクエスト (DoS) は公開境界である BFF 側の実 IP 単位制限で抑制する
     *
     * email / authCeremonyId / refreshTokenId は OpenAPI スキーマで必須かつ
     * 形式検証されるため、この limiter に到達する時点で空文字にはならない
     */
    private function registerPasskeyRateLimiters(): void
    {
        RateLimiter::for(
            'passkey-login-start',
            fn (Request $request): Limit => $this->limit('login')->by('login-start:' . $this->emailKey($request)),
        );

        RateLimiter::for(
            'passkey-register-start',
            fn (Request $request): Limit => $this->limit('register')->by('register-start:' . $this->emailKey($request)),
        );

        RateLimiter::for(
            'passkey-recovery-start',
            fn (Request $request): Limit => $this->limit('recovery')->by('recovery-start:' . $this->emailKey($request)),
        );

        RateLimiter::for(
            'passkey-refresh',
            fn (Request $request): Limit => $this->limit('refresh')->by('refresh:' . $request->string('refreshTokenId')->toString()),
        );
    }

    private function limit(string $name): Limit
    {
        return Limit::perMinute(config()->integer('auth.passkey.rate_limit.' . $name));
    }

    private function emailKey(Request $request): string
    {
        return $request->string('email')->lower()->trim()->toString();
    }
}
