<?php

declare(strict_types=1);

namespace Support\Notification\Contracts;

/**
 * Discord embed の color
 * Info / Success / Error / Default は notifier の status 既定色と揃える
 */
enum Color: int
{
    case Info = 0x34_98DB;

    case Success = 0x57_F287;

    case Warning = 0xFE_E75C;

    case Error = 0xED_4245;

    case Default = 0x95_A5A6;
}
