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
     * @param  array  $skills
     *
     * @return array|array[]
     */
    public function stats(array $skills = [])
    {
        if ($skills) {
            return array_intersect_key($this->stats, array_flip($this->formatSkills($skills)));
        }

        return $this->stats;
    }

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

    private function formatSkills($skills)
    {
        $skills = array_map([$this, 'formatSkill'], $skills);

        return array_filter($skills, function ($skill) {
            return array_key_exists($skill, $this->stats);
        });
    }

    private function formatSkill($skill)
    {
        return ucfirst(strtolower(preg_replace('/\s/', '', $skill)));
    }
}
