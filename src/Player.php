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
     * @var array[]
     */
    private $miniGames;

    /**
     * Player constructor.
     *
     * @param  string  $rsn
     * @param  array  $stats
     */
    public function __construct(string $rsn, array $stats, array $miniGames)
    {
        $this->rsn = $rsn;
        $this->stats = $stats;
        $this->miniGames = $miniGames;
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
     * Get all data (rank, level and experience) for the given skills (default all).
     *
     * @param  array|string  $skills
     *
     * @return array|array[]
     */
    public function stats($skills = [])
    {
        if (is_string($skills)) {
            return $this->stats[$this->formatSkill($skills)] ?? null;
        }

        if ($skills) {
            return array_intersect_key($this->stats, array_flip($this->formatSkills($skills)));
        }

        return $this->stats;
    }

    /**
     * Get all data (rank and count) for the given mini games or bosses (default all).
     *
     * @param  array|string  $miniGames
     * @return array|mixed
     */
    public function miniGames($miniGames = [])
    {
        if (is_string($miniGames)) {
            return $this->miniGames[$this->formatSkill($miniGames)] ?? null;
        }

        if ($miniGames) {
            return array_intersect_key($this->miniGames, array_flip($this->formatSkills($miniGames)));
        }

        return $this->miniGames;
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

        $skills = $this->formatSkills($skills);

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
    private function formatSkills(array $skills): array
    {
        $skills = array_map([$this, 'formatSkill'], $skills);

        return array_filter($skills, function ($skill) {
            return array_key_exists($skill, $this->stats);
        });
    }

    /**
     * Formats a skill name to match correct format.
     * E.g.:
     * "Hit Points" -> "Hitpoints"
     * " attack " -> "Attack"
     *
     * @param  string  $skill
     *
     * @return string
     */
    private function formatSkill(string $skill): string
    {
        // First, remove all whitespace from the skill name.
        // Then, lowercase the entire skill.
        // Lastly, uppercase the first letter, to match the expected Skill key.
        return ucfirst(
            strtolower(
                preg_replace('/\s/', '', $skill)
            )
        );
    }
}
