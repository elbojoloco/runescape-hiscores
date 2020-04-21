<?php

namespace Elbojoloco\RunescapeHiscores;

use Elbojoloco\RunescapeHiscores\Exceptions\InvalidRsnException;
use Elbojoloco\RunescapeHiscores\Exceptions\RunescapeHiscoresFailedException;
use Elbojoloco\RunescapeHiscores\Exceptions\RunescapeNameNotFoundException;
use Elbojoloco\RunescapeHiscores\Exceptions\InvalidHiscoreTypeException;
use Zttp\Zttp;

class RunescapeClient
{
    const RS3_HISCORE = 'hiscore';
    const OLDSCHOOL_HISCORE = 'hiscore_oldschool';
    const TYPE_RS3 = 'rs3';
    const TYPE_OLDSCHOOL = 'oldschool';

    /**
     * The hiscores type to call. Either "rs3" or "oldschool"
     *
     * @var string $hiscoreType
     */
    private $hiscoreType;

    /**
     * The complete list of OSRS skills in order
     *
     * @var string[]
     */
    private static $osrsSkills = [
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
     * The list of RS3 skills that should be merged into the OSRS skills for the RS3 hiscores
     *
     * @var string[]
     */
    private static $rs3Skills = [
        4 => 'Constitution',
        24 => 'Summoning',
        25 => 'Dungeoneering',
        26 => 'Divination',
        27 => 'Invention',
    ];

    /**
     * Validate the inputs and call the API.
     *
     * @param  string  $type
     * @param  string  $rsn
     *
     * @return \Elbojoloco\RunescapeHiscores\Player
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\InvalidRsnException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeHiscoresFailedException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeNameNotFoundException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\InvalidHiscoreTypeException
     */
    public function hiscore(string $type, string $rsn): Player
    {
        [$type, $rsn] = $this->formatTypeAndRsn($type, $rsn);

        $this->hiscoreType = $type;

        return $this->get($rsn);
    }

    /**
     * Call the API and return a Player instance that contains the RSN and the player's stats.
     *
     * @param  string  $rsn
     *
     * @return \Elbojoloco\RunescapeHiscores\Player
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeHiscoresFailedException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeNameNotFoundException
     */
    private function get(string $rsn): Player
    {
        $body = $this->sendRequest($rsn);

        $skills = $this->skills();
        $stats = [];

        for ($i = 0; $i < count($skills); $i++) {
            [$rank, $level, $experience] = explode(',', $body[$i]);

            $stats[$skills[$i]] = compact('rank', 'level', 'experience');
        }

        return new Player($rsn, $stats);
    }

    /**
     * Sends the request to the hiscores endpoint and handles HTTP errors.
     *
     * @param  string  $rsn
     *
     * @return false|string[]
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeHiscoresFailedException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeNameNotFoundException
     */
    private function sendRequest(string $rsn)
    {
        $response = Zttp::get(
            $this->requestUrl($this->hiscoreEndpoint(), $rsn)
        );

        $status = $response->status();

        if ($status === 404) {
            throw new RunescapeNameNotFoundException("{$this->hiscoreType} hiscores lookup failed for RSN \"{$rsn}\"");
        }

        if ($status !== 200) {
            throw new RunescapeHiscoresFailedException("{$this->hiscoreType} hiscores request failed with status code: {$status}");
        }

        return explode("\n", $response->body());
    }

    /**
     * Get the hiscore endpoint to use.
     *
     * @return string
     */
    private function hiscoreEndpoint(): string
    {
        $endpoints = [
            self::TYPE_RS3 => self::RS3_HISCORE,
            self::TYPE_OLDSCHOOL => self::OLDSCHOOL_HISCORE,
        ];

        return $endpoints[$this->hiscoreType];
    }

    /**
     * Get the full request URL with filled parameters.
     *
     * @param  string  $endpoint
     * @param  string  $rsn
     *
     * @return string
     */
    private function requestUrl(string $endpoint, string $rsn): string
    {
        return vsprintf('http://services.runescape.com/m=%s/index_lite.ws?player=%s', [$endpoint, $rsn]);
    }

    /**
     * Get the skills array based on hiscores type.
     *
     * @return array|string[]
     */
    private function skills()
    {
        $skills = [
            self::TYPE_OLDSCHOOL => self::$osrsSkills,
            self::TYPE_RS3 => array_replace(self::$osrsSkills, self::$rs3Skills),
        ];

        return $skills[$this->hiscoreType];
    }

    /**
     * Validate and format the hiscore type. If successful, returns the formatted type and given rsn.
     *
     * @param  string  $type
     * @param  string  $rsn
     *
     * @return array
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\InvalidHiscoreTypeException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\InvalidRsnException
     */
    private function formatTypeAndRsn(string $type, string $rsn): array
    {
        if (! $type) {
            throw new InvalidHiscoreTypeException("The hiscore type is required.");
        }

        if (! $rsn) {
            throw new InvalidRsnException('The Runescape name is required.');
        }

        $type = strtolower(
            preg_replace('/\s/', '', $type)
        );

        if (! in_array($type, [self::TYPE_RS3, self::TYPE_OLDSCHOOL])) {
            throw new InvalidHiscoreTypeException("The given hiscore \"{$type}\" is invalid, must be either \"rs3\" or \"oldschool\"");
        }

        return [$type, $rsn];
    }
}
