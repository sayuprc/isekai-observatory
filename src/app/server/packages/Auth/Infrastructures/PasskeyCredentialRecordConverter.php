<?php

declare(strict_types=1);

namespace Auth\Infrastructures;

use Auth\Domain\Models\AdminUserPasskey;
use Symfony\Component\Uid\Uuid;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\TrustPath\EmptyTrustPath;

readonly class PasskeyCredentialRecordConverter
{
    public function toCredentialRecord(AdminUserPasskey $passkey): CredentialRecord
    {
        return CredentialRecord::create(
            $passkey->credentialId,
            PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
            $passkey->transports,
            'none',
            EmptyTrustPath::create(),
            Uuid::fromString($passkey->aaguid),
            $passkey->publicKey,
            $passkey->userHandle,
            $passkey->signCount,
            backupEligible: $passkey->backupEligible,
            backupStatus: $passkey->backupState,
        );
    }
}
