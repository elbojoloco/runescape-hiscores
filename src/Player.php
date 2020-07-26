<?php

namespace Elbojoloco\RunescapeHiscores;

class Player
{
    /**
     * @var string
     */
    private $rsn;

    /**
     * @var array[]
     */
    private $stats;

    /**
     * Player constructor.
     *
     * @param  string  $rsn
     * @param  array  $stats
     */
    public function __construct(string $rsn, array $stats)
    {
        $this->rsn = $rsn;
        $this->stats = $stats;
    }

    /**
     * Get the Player's RSN.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->rsn;
    }

    /**
     * Get the player's rank of the given stat. Or an array of ranks per stat.
     *
     * @param  string|string[]  $stats
     *
     * @return array|string
     */
    public function rank($stats)
    {
        return $this->getStat('rank', $stats);
    }

    /**
     * Get the player's level of the given stat. Or an array of levels per stat.
     *
     * @param  string|string[]  $stats
     *
     * @return array|string
     */
    public function level($stats)
    {
        return $this->getStat('level', $stats);
    }

    /**
     * Get the player's experience of the given stat. Or an array of eperience per stat.
     *
     * @param  string|string[]  $stats
     *
     * @return array|string
     */
    public function experience($stats)
    {
        return $this->getStat('experience', $stats);
    }

    /**
     * Get the player's kill count for the given boss(es).
     *
     * @param  string|string[]  $bosses
     *
     * @return array|string
     */
    public function count($bosses)
    {
        return $this->getStat('count', $bosses);
    }

    /**
     * Get all data (rank, level and experience) for the given stats (default all).
     *
     * @param  array|string  $stats
     *
     * @return array|array[]
     */
    public function stats($stats = [])
    {
        if (is_string($stats)) {
            return $this->stats[$this->formatStat($stats)] ?? null;
        }

        if ($stats) {
            return array_intersect_key($this->stats, array_flip($this->formatStats($stats)));
        }

        return $this->stats;
    }

    /**
     * Get all data (rank and count) for the given mini games or bosses (default all).
     *
     * @param  array|string  $keys
     *
     * @return array|mixed
     */
    public function miniGames($keys = [])
    {
        $miniGames = array_filter($this->stats, function ($stat) {
            return count($stat) == 2;
        });

        if (is_string($keys)) {
            return $miniGames[$this->formatStat($keys)] ?? null;
        }

        if ($keys) {
            return array_intersect_key($miniGames, array_flip($this->formatStats($keys)));
        }

        return $miniGames;
    }

    public function bosses($bosses = [])
    {
        return $this->miniGames($bosses);
    }

    /**
     * Get a stat or multiple stats from the stats array. Is flexible with stat names and spacing, but won't cover spelling mistakes.
     *
     * @param  string  $metric
     * @param  array|string  $stats
     *
     * @return array|string
     */
    private function getStat(string $metric, $stats)
    {
        if (! is_array($stats)) {
            $stats = [$stats];
        }

        $stats = $this->formatStats($stats);

        $keys = array_flip($stats);
        $stats = array_intersect_key($this->stats, $keys);

        $result = array_combine($stats, array_column($stats, $metric));

        return count($result) === 1 ? array_shift($result) : $result;
    }

    /**
     * Formats every passed stat's name.
     *
     * @param  array  $stats
     *
     * @return array
     */
    private function formatStats(array $stats): array
    {
        $stats = array_map([$this, 'formatStat'], $stats);

        return array_filter($stats, function ($stat) {
            return array_key_exists($stat, $this->stats);
        });
    }

    /**
     * Formats a stat name to match correct format.
     *
     * @param  string  $stat
     *
     * @return string
     */
    private function formatStat(string $stat): string
    {
        // First, trim all whitespace from the stat name.
        // Then, lowercase the entire stat.
        // Finally, uppercase the first letter of every word to match the expected Stat key.
        return ucwords(
            strtolower(
                trim($stat)
            )
        );
    }
}
