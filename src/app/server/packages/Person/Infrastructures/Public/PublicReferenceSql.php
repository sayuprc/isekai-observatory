<?php

declare(strict_types=1);

namespace Person\Infrastructures\Public;

/**
 * 公開記録から参照されている人物・人物グループを絞り込むサブクエリ
 */
final class PublicReferenceSql
{
    /**
     * 公開イベントの共演者の出演グループとして参照されている人物グループ ID
     */
    public const string PERSON_GROUP_IDS = <<<'SQL'
        SELECT song_performance_persons.person_group_id
        FROM song_performance_persons
        INNER JOIN song_performances ON song_performances.performance_id = song_performance_persons.performance_id
        INNER JOIN events ON events.event_id = song_performances.event_id
        WHERE events.is_display = TRUE
          AND song_performance_persons.person_group_id IS NOT NULL
        SQL;

    /**
     * 公開楽曲のクレジット、公開イベントの共演者、公開人物グループのメンバーとして参照されている人物 ID
     */
    public const string PERSON_IDS = <<<'SQL'
        SELECT song_persons.person_id
        FROM song_persons
        INNER JOIN songs ON songs.song_id = song_persons.song_id
        WHERE songs.is_display = TRUE
        UNION
        SELECT song_performance_persons.person_id
        FROM song_performance_persons
        INNER JOIN song_performances ON song_performances.performance_id = song_performance_persons.performance_id
        INNER JOIN events ON events.event_id = song_performances.event_id
        WHERE events.is_display = TRUE
        UNION
        SELECT person_group_members.person_id
        FROM person_group_members
        WHERE person_group_members.person_group_id IN (
        SQL . self::PERSON_GROUP_IDS . ')';
}
