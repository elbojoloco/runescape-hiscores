
# Requirements
- PHP 7.1 or up

# Installation
- `composer require elbojoloco/runescape-hiscores`

# Basic usage
It's very easy to start getting data from the RS3 / OSRS hiscores. All you need to get all stats for a player is this line: (Make sure to `use Elbojoloco\RunescapeHiscores\Runescape;`)
- OSRS: `$player = Runescape::oldschool('Lynx Titan');`
- RS3: `$player = Runescape::rs3('le me');`

To dynamically pass a hiscore to use you should use the `get($type, $rsn)` method instead:
```
$hiscoreType = $_GET['hiscore_type'];
$rsn = $_GET['rsn'];

$player = Runescape::get($hiscoreType, $rsn);
```
`$type` will only accept the values `rs3` or `oldschool`, case-insensitive. The `$rsn` also must be at least 1 character, to prevent meaningless API calls.

You may also use the predefined constants on the `Elbojoloco\RunescapeHiscores\RunescapeClient` class:
```
Runescape::get(RunescapeClient::TYPE_OLDSCHOOL, 'Lynx Titan');
Runescape::get(RunescapeClient::TYPE_RS3, 'le me');
```

# The Player object
All 3 of the static methods `rs3()`, `oldschool()` and `get()` will return an instance of `Elbojoloco\RunescapeHiscores\Player`.
This Player class provides some handy methods to interact with the hiscores data as show in these examples:
#### Retrieving the RSN
- `$player->name()`

#### Retrieving the level of a skill, or multiple skills
- `$player->level('Runecrafting')` // Returns level as a string
- `$player->level(['Runecrafting', 'Mining', 'Construction'])` // Returns an associative array of "skill" => "level"

#### Retrieving the experience of a skill, or multiple skills
- `$player->experience('Overall')` // Returns experience as a string
- `$player->experience(['Runecrafting', 'Mining', 'Construction'])` // Returns an associative array of "skill" => "experience"

#### Retrieving the rank for a skill, boss or minigame
- `$player->rank('Runecrafting')` // Returns rank as a string
- `$player->rank(['Runecrafting', 'Zulrah', 'Construction'])` // Returns an associative array of "stat" => "rank"

#### Retrieving all metrics of a skill, boss or minigame
- `$player->stats('Overall')` // Returns a skill entry that contains "rank", "level" and "experience"
- `$player->stats('Zulrah')` // Returns a boss/minigame entry that contains "rank" and "count"
- `$player->stats(['Hitpoints', 'Strength'])` // Returns an array of skill entries
- `$player->miniGames(['Clue scrolls (beginner)', 'Vorkath'])` // Returns an array of bosses/minigames. For a full list of accepted keys please refer to [RunescapeClient.php](https://github.com/elbojoloco/runescape-hiscores/blob/master/src/RunescapeClient.php#L57).
- `$player->bosses()` is an alias for `$player->miniGames()`
- `$player->stats()` // Returns an array of all stat entries

# Contributing

### Pull requests
Fork the repository and submit a pull request to the "master" branch. Please make sure to follow coding conventions displayed in the existing source.

### Issues
Try to describe the issue in as much detail as possible and provide examples. General feedback is welcomed and much appreciated too :)
