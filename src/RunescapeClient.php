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
     * The complete list of OSRS minigames and bosses in order
     *
     *  !! WARNING: When Jagex is adding a new boss or minigame,
     *      they will add it in the middle to keep everything alphabetic
     *      this is when things go wrong and need to add in the new value here !!
     *
     * @var array[]
     */
    private static $osrsMiniGames = [
        24 => '',
        25 => 'Bounty Hunter - Hunter',
        26 => 'Bounty Hunter - Rogue',
        27 => 'Clue Scrolls (all)',
        28 => 'Clue Scrolls (beginner)',
        29 => 'Clue Scrolls (easy)',
        30 => 'Clue Scrolls (medium)',
        31 => 'Clue Scrolls (hard)',
        32 => 'Clue Scrolls (elite)',
        33 => 'Clue Scrolls (master)',
        34 => 'LMS Rank',
        35 => 'Abyssal Sire',
        36 => 'Alchemical Hydra',
        37 => 'Barrows Chests',
        38 => 'Bryophyta',
        39 => 'Callisto',
        40 => 'Cerberus',
        41 => 'Chambers Of Xeric',
        42 => 'Chambers Of Xeric Challenge Mode',
        43 => 'Chaos Elemental',
        44 => 'Chaos Fanatic',
        45 => 'Commander Zilyana',
        46 => 'Corporeal Beast',
        47 => 'Crazy Archaeologist',
        48 => 'Dagannoth Prime',
        49 => 'Dagannoth Rex',
        50 => 'Dagannoth Supreme',
        51 => 'Deranged Archaeologist',
        52 => 'General Graardor',
        53 => 'Giant Mole',
        54 => 'Grotesque Guardians',
        55 => 'Hespori',
        56 => 'Kalphite Queen',
        57 => 'King Black Dragon',
        58 => 'Kraken',
        59 => 'Kreearra',
        60 => 'Kril Tsutsaroth',
        61 => 'Mimic',
        62 => 'Nightmare',
        63 => 'Obor',
        64 => 'Sarachnis',
        65 => 'Scorpia',
        66 => 'Skotizo',
        67 => 'The Gauntlet',
        68 => 'The Corrupted Gauntlet',
        69 => 'Theater Of Blood',
        70 => 'Thermonuclear Smoke Devil',
        71 => 'Tzkal-Zuk',
        72 => 'Tztok-Jad',
        73 => 'Venenatis',
        74 => 'Vetion',
        75 => 'Vorkath',
        76 => 'Wintertodt',
        77 => 'Zalcano',
        78 => 'Zulrah',
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

        $skillCount = count($skills);
        for ($i = 0; $i < $skillCount; $i++) {
            [$rank, $level, $experience] = explode(',', $body[$i]);

            $stats[$skills[$i]] = compact('rank', 'level', 'experience');
        }

        $miniGames = $this->miniGames();
        $miniGamesStats = [];

        $miniGamesCount = count($miniGames);
        for ($i = $skillCount; $i < $skillCount + $miniGamesCount; $i++) {
            [$rank, $count] = explode(',', $body[$i]);

            $miniGamesStats[$miniGames[$i]] = compact('rank', 'count');
        }

        return new Player($rsn, $stats, $miniGamesStats);
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
     * Get the mini games based on hiscores type.
     * @todo Add Runescape 3 minigames
     *
     * @return array|string[]
     */
    private function miniGames()
    {
        $miniGames = [
            self::TYPE_OLDSCHOOL => self::$osrsMiniGames,
            self::TYPE_RS3 => [],
        ];

        return $miniGames[$this->hiscoreType];
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

        if (! $rsn = trim($rsn)) {
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
