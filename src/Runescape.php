<?php

namespace Elbojoloco\RunescapeHiscores;

class Runescape
{
    /**
     * Retrieves the given player (RSN) from the RS3 hiscores.
     *
     * @param  string  $rsn  The runescape name to look up.
     *
     * @return \Elbojoloco\RunescapeHiscores\Player
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\InvalidRsnException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeHiscoresFailedException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeNameNotFoundException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\InvalidHiscoreTypeException
     */
    static function rs3(string $rsn): Player
    {
        return (new RunescapeClient())->hiscore(RunescapeClient::TYPE_RS3, $rsn);
    }

    /**
     * Retrieves the given player (RSN) from the Old School hiscores.
     *
     * @param  string  $rsn  The runescape name to look up.
     *
     * @return \Elbojoloco\RunescapeHiscores\Player
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\InvalidRsnException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeHiscoresFailedException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeNameNotFoundException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\InvalidHiscoreTypeException
     */
    static function oldschool(string $rsn): Player
    {
        return (new RunescapeClient())->hiscore(RunescapeClient::TYPE_OLDSCHOOL, $rsn);
    }

    /**
     * Retrieves the given player (RSN) from the given hiscore.
     *
     * @param  string  $type  The hiscores to use. Accepted: "rs3" or "oldschool".
     * @param  string  $rsn  The runescape name to look up.
     *
     * @return \Elbojoloco\RunescapeHiscores\Player
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\InvalidRsnException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeHiscoresFailedException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\RunescapeNameNotFoundException
     * @throws \Elbojoloco\RunescapeHiscores\Exceptions\InvalidHiscoreTypeException
     */
    static function get(string $type, string $rsn): Player
    {
        return (new RunescapeClient())->hiscore($type, $rsn);
    }
}
