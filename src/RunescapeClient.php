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
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeHiscoresFailedException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeNameNotFoundException
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
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeHiscoresFailedException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeNameNotFoundException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\UnknownHiscoresTypeException
     */
    public function oldschool(string $rsn): Player
    {
        $this->rsn = $rsn;
        $this->oldschool = true;
        $this->rs3 = false;

        return $this->get();
    }

    /**
     * Get the player's stats. Pass a type ("rs3" or "oldschool") and a runescape name when calling directly.
     *
     * @param  string  $type
     * @param  string  $rsn
     *
     * @return \Elbojoloco\RunescapeHiscores\Player
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RsnMissingException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeHiscoresFailedException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeNameNotFoundException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\UnknownHiscoresTypeException
     */
    public function get(string $type = '', string $rsn = ''): Player
    {
        if ([$type, $rsn] = $this->formatTypeAndRsn($type, $rsn)) {
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

    /**
     * Sends the request to the hiscores endpoint and handles HTTP errors.
     *
     * @return false|string[]
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeHiscoresFailedException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeNameNotFoundException
     */
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

    /**
     * Get the hiscore endpoint to use.
     *
     * @return string
     */
    private function hiscore(): string
    {
        return $this->oldschool ? 'hiscore_oldschool' : 'hiscore';
    }

    /**
     * Get the full request URL with filled parameters.
     *
     * @return string
     */
    private function requestUrl(): string
    {
        return vsprintf('http://services.runescape.com/m=%s/index_lite.ws?player=%s', [$this->hiscore(), $this->rsn]);
    }

    /**
     * Get the skills array based on hiscores type.
     *
     * @return array|string[]
     */
    private function skills()
    {
        return $this->oldschool ? $this->osrsSkills : array_replace($this->osrsSkills, $this->rs3Skills);
    }

    /**
     * Get the hiscore type. Returns false when no type has been set.
     *
     * @return bool|string
     */
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

    /**
     * Validate and format the hiscore type. If successful, returns the formatted type and given rsn.
     *
     * @param  string  $type
     * @param  string  $rsn
     *
     * @return array|bool
     */
    private function formatTypeAndRsn(string $type, string $rsn)
    {
        if (! $type || ! $rsn) {
            return false;
        }

        $type = strtolower(
            preg_replace('/\s/', '', $type)
        );

        if (! in_array($type, ['rs3', 'oldschool'])) {
            return false;
        }

        return [$type, $rsn];
    }
}
