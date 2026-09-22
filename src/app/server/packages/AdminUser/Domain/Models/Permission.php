<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

enum Permission: string
{
    case ReadAdminUser = 'read_admin_user';

    case WriteAdminUser = 'write_admin_user';

    case ReadPerson = 'read_person';

    case WritePerson = 'write_person';

    case ReadSong = 'read_song';

    case WriteSong = 'write_song';

    case ReadAuditLog = 'read_audit_log';

    case ReadMedia = 'read_media';

    case WriteMedia = 'write_media';

    case ReadRelease = 'read_release';

    case WriteRelease = 'write_release';

    case ReadVenue = 'read_venue';

    case WriteVenue = 'write_venue';

    case ReadEvent = 'read_event';

    case WriteEvent = 'write_event';

    public function getName(): string
    {
        return match ($this) {
            self::ReadAdminUser => '管理ユーザー閲覧',
            self::WriteAdminUser => '管理ユーザー編集',
            self::ReadPerson => '人物閲覧',
            self::WritePerson => '人物編集',
            self::ReadSong => '楽曲閲覧',
            self::WriteSong => '楽曲編集',
            self::ReadAuditLog => '監査ログ閲覧',
            self::ReadMedia => 'メディア閲覧',
            self::WriteMedia => 'メディア編集',
            self::ReadRelease => 'リリース閲覧',
            self::WriteRelease => 'リリース編集',
            self::ReadVenue => '開催先閲覧',
            self::WriteVenue => '開催先編集',
            self::ReadEvent => 'イベント閲覧',
            self::WriteEvent => 'イベント編集',
        };
    }
}
