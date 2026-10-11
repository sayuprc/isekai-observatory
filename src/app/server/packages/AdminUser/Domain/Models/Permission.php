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
}
