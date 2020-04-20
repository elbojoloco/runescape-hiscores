<?php

namespace Elbojoloco\RunescapeHiscores;

use Elbojoloco\RunescapeHiscores\Exceptions\RsnMissingException;
use Elbojoloco\RunescapeHiscores\Exceptions\RunescapeHiscoresFailedException;
use Elbojoloco\RunescapeHiscores\Exceptions\RunescapeNameNotFoundException;
use Elbojoloco\RunescapeHiscores\Exceptions\UnknownHiscoresTypeException;
use Zttp\Zttp;

class RunescapeClient
{
    /**
     * @var bool
     */
    private $oldschool = false;

    /**
     * @var bool
     */
    private $rs3 = false;

    /**
     * @var string
     */
    private $rsn;

    /**
     * @var string[]
     */
    private $osrsSkills = [
        0 => 'Overall',
        1 => 'Attack',
        2 => 'Defence',
        3 => 'Strength',
        4 => 'Hitpoints',
        5 => 'Ranged',
        6 => 'Prayer',
        7 => 'Magic',
        8 => 'Cooking',
        9 => 'Woodcutting',
        10 => 'Fletching',
        11 => 'Fishing',
        12 => 'Firemaking',
        13 => 'Crafting',
        14 => 'Smithing',
        15 => 'Mining',
        16 => 'Herblore',
        17 => 'Agility',
        18 => 'Thieving',
        19 => 'Slayer',
        20 => 'Farming',
        21 => 'Runecrafting',
        22 => 'Hunter',
        23 => 'Construction',
    ];

    /**
     * @var string[]
     */
    private $rs3Skills = [
        4 => 'Constitution',
        24 => 'Summoning',
        25 => 'Dungeoneering',
        26 => 'Divination',
        27 => 'Invention',
    ];

    /**
     * Get a player's stats using the RS3 hiscores.
     *
     * @param  string  $rsn
     *
     * @return \Elbojoloco\RunescapeHiscores\Player
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RsnMissingException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\UnknownHiscoresTypeException
     */
    public function rs3(string $rsn): Player
    {
        $this->rsn = $rsn;
        $this->oldschool = false;
        $this->rs3 = true;

        return $this->get();
    }

    /**
     * Get a player's stats using the Old School hiscores.
     *
     * @param  string  $rsn
     *
     * @return \Elbojoloco\RunescapeHiscores\Player
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RsnMissingException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\UnknownHiscoresTypeException
     */
    public function oldschool(string $rsn)
    {
        $this->rsn = $rsn;
        $this->oldschool = true;
        $this->rs3 = false;

        return $this->get();
    }

    /**
     * @param  string  $type
     * @param  string  $rsn
     *
     * @return \Elbojoloco\RunescapeHiscores\Player
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RsnMissingException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeHiscoresFailedException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeNameNotFoundException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\UnknownHiscoresTypeException
     */
    public function get(string $type = '', string $rsn = '')
    {
        if ($type && in_array($type = strtolower(preg_replace('/\s/', '', $type)), ['rs3', 'oldschool']) && $rsn) {
            return $this->{$type}($rsn);
        }

        if (! $this->type()) {
            throw new UnknownHiscoresTypeException(
                'You may not call "get()" directly without specifying a Hiscore type of either "rs3" or "oldschool" and a valid RSN'
            );
        }

        if (! $this->rsn) {
            throw new RsnMissingException('RSN is required and must be at least 1 character long');
        }

        $body = $this->sendRequest();

        $skills = $this->skills();
        $stats = [];

        for ($i = 0; $i < count($skills); $i++) {
            [$rank, $level, $experience] = explode(',', $body[$i]);

            $stats[$skills[$i]] = compact('rank', 'level', 'experience');
        }

        return new Player($this->rsn, $stats);
    }

    private function sendRequest()
    {
        $response = Zttp::get($this->requestUrl());

        if (($status = $response->status()) !== 200) {
            if ($status === 404) {
                throw new RunescapeNameNotFoundException("{$this->type()} hiscores lookup failed for RSN \"{$this->rsn}\"");
            }

            throw new RunescapeHiscoresFailedException("{$this->type()} hiscores request failed with status code: {$status}");
        }

        return explode("\n", $response->body());
    }

    private function hiscore()
    {
        return $this->oldschool ? 'hiscore_oldschool' : 'hiscore';
    }

    private function requestUrl()
    {
        return vsprintf('http://services.runescape.com/m=%s/index_lite.ws?player=%s', [$this->hiscore(), $this->rsn]);
    }

    private function skills()
    {
        return $this->oldschool ? $this->osrsSkills : array_replace($this->osrsSkills, $this->rs3Skills);
    }

    private function type()
    {
        if ($this->rs3) {
            return 'rs3';
        }

        if ($this->oldschool) {
            return 'oldschool';
        }

        return false;
    }
}
