<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Email;

readonly class PasskeyCeremonyState
{
    public function __construct(
        public string $authCeremonyId,
        public PasskeyCeremonyType $type,
        public string $email,
        public ?string $name,
        public string $adminUserId,
        public string $optionsJson,
        // Recovery ceremony で start 時に検証成功したコードの id を束縛する
        // Recovery 以外の ceremony では null
        public ?string $recoveryCodeId = null,
    ) {
        new AuthCeremonyId($this->authCeremonyId);
        new Email($this->email);
        new AdminUserId($this->adminUserId);
    }

    /**
     * @return array{
     *   auth_ceremony_id: string,
     *   type: string,
     *   email: string,
     *   name: string|null,
     *   admin_user_id: string,
     *   options_json: string,
     *   recovery_code_id: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'auth_ceremony_id' => $this->authCeremonyId,
            'type' => $this->type->value,
            'email' => $this->email,
            'name' => $this->name,
            'admin_user_id' => $this->adminUserId,
            'options_json' => $this->optionsJson,
            'recovery_code_id' => $this->recoveryCodeId,
        ];
    }

    /**
     * @param array{
     *   auth_ceremony_id: string,
     *   type: string,
     *   email: string,
     *   name: string|null,
     *   admin_user_id: string,
     *   options_json: string,
     *   recovery_code_id?: string|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['auth_ceremony_id'],
            PasskeyCeremonyType::from($data['type']),
            $data['email'],
            $data['name'],
            $data['admin_user_id'],
            $data['options_json'],
            $data['recovery_code_id'] ?? null,
        );
    }
}
