<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog;

enum AuditTargetType: string
{
    case AdminUser = 'AdminUser';

    case Media = 'Media';

    case Person = 'Person';

    case Release = 'Release';

    case ReleaseGroup = 'ReleaseGroup';

    case Song = 'Song';

    case SongTag = 'SongTag';

    case Venue = 'Venue';

    case Event = 'Event';
}
