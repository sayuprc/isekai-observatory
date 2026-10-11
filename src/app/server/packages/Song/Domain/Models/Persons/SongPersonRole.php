<?php

declare(strict_types=1);

namespace Song\Domain\Models\Persons;

enum SongPersonRole: int
{
    case Lyricist = 1;

    case Composer = 2;

    case Arranger = 3;
}
