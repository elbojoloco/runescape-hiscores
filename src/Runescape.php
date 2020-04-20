<?php

namespace Elbojoloco\RunescapeHiscores;

class Runescape {
    /**
     * @param  string  $rsn  The runescape name to look up.
     *
     * @return \Elbojoloco\RunescapeHiscores\Player
     */
    static function rs3(string $rsn)
    {
        return (new RunescapeClient())->rs3($rsn);
    }

    /**
     * @param  string  $rsn  The runescape name to look up.
     *
     * @return \Elbojoloco\RunescapeHiscores\Player
     */
    static function oldschool(string $rsn)
    {
        return (new RunescapeClient())->oldschool($rsn);
    }

    /**
     * @param  string  $type  The hiscores to use. Accepted: "rs3" or "oldschool".
     * @param  string  $rsn  The runescape name to look up.
     *
     * @return \Elbojoloco\RunescapeHiscores\Player
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RsnMissingException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\UnknownHiscoresTypeException
     */
    static function get(string $type, string $rsn): Player
    {
        return (new RunescapeClient())->get($type, $rsn);
    }
}
