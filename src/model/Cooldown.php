<?php

namespace Pingumask\Plectrum\Model;

use DateTime;
use PDO;
use Pingumask\Plectrum\App;

class Cooldown
{
    public static function getEnd(int $guild, int $user, string $command, int $interval = 300): ?int
    {
        $database = App::getDB();
        $sql = <<<SQL
            SELECT
                `time` + INTERVAL :cd SECOND AS `end`
            FROM plectrum_cooldown
            WHERE
                `guild` = :guild
                AND `user` = :user
                AND command = :command
            HAVING NOW() < `end`
            ORDER BY `time` DESC
            LIMIT 1;
        SQL;
        $pdo = $database->prepare($sql);
        $pdo->execute([':guild' => $guild, ':user' => $user, ':command' => $command, ':cd' => $interval]);
        /** @var string|false $cd */
        $cd = $pdo->fetch(PDO::FETCH_COLUMN);
        if (!$cd) {
            return null;
        }
        $dt = new DateTime($cd);
        return $dt->getTimestamp();
    }

    public static function set(int $guild, int $user, string $command): void
    {
        $database = App::getDB();
        $sql = <<<SQL
            INSERT INTO
                plectrum_cooldown
                    (`guild`, `user`, `command`)
                VALUES
                    (:guild, :user, :command);
        SQL;
        $pdo = $database->prepare($sql);
        $pdo->execute([':guild' => $guild, ':user' => $user, ':command' => $command]);
    }
}
