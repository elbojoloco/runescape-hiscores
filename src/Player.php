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
     * Get the player's rank of the given skill. Or an array of ranks per skill.
     *
     * @param  string|string[]  $skills
     *
     * @return array|string
     */
    public function rank($skills)
    {
        return $this->getStat('rank', $skills);
    }

    /**
     * Get the player's level of the given skill. Or an array of levels per skill.
     *
     * @param  string|string[]  $skills
     *
     * @return array|string
     */
    public function level($skills)
    {
        return $this->getStat('level', $skills);
    }

    /**
     * Get the player's experience of the given skill. Or an array of eperience per skill.
     *
     * @param  string|string[]  $skills
     *
     * @return array|string
     */
    public function experience($skills)
    {
        return $this->getStat('experience', $skills);
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
     * Get all data (rank, level and experience) for the given skills (default all).
     *
     * @param  array|string  $skills
     *
     * @return array|array[]
     */
    public function stats($skills = [])
    {
        if (is_string($skills)) {
            return $this->stats[$this->formatStat($skills)] ?? null;
        }

        if ($skills) {
            return array_intersect_key($this->stats, array_flip($this->formatStats($skills)));
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
     * Get a stat or multiple stats from the stats array. Is flexible with skill names and spacing, but won't cover spelling mistakes.
     *
     * @param  string  $metric
     * @param  array|string  $skills
     *
     * @return array|string
     */
    private function getStat(string $metric, $skills)
    {
        if (! is_array($skills)) {
            $skills = [$skills];
        }

        $skills = $this->formatStats($skills);

        $keys = array_flip($skills);
        $stats = array_intersect_key($this->stats, $keys);

        $result = array_combine($skills, array_column($stats, $metric));

        return count($result) === 1 ? array_shift($result) : $result;
    }

    /**
     * Formats every passed skill's name.
     *
     * @param  array  $skills
     *
     * @return array
     */
    private function formatStats(array $skills): array
    {
        $skills = array_map([$this, 'formatStat'], $skills);

        return array_filter($skills, function ($skill) {
            return array_key_exists($skill, $this->stats);
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
