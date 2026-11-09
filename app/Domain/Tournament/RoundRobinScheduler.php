<?php

namespace App\Domain\Tournament;

use InvalidArgumentException;

/**
 * Builds an all-play-all schedule.
 *
 * ## Terminology
 *
 * A **leg** is one full pass in which every pair of teams meets exactly once.
 * `Championship::$rounds` holds the number of legs, and `Fixture::$round_number`
 * stores which leg a match belongs to. (The database column is named
 * `round_number` for historical reasons; it is a leg index, not a matchday.)
 *
 * Home advantage alternates by leg: odd legs use the drawn orientation, even
 * legs invert it, so a two-leg season gives every pair one home game each.
 *
 * ## Withheld final leg
 *
 * With an odd number of legs greater than one, the last leg is *not* generated
 * here. Its home/away assignments depend on results from the earlier legs, so
 * GenerateFinalRound produces it once every prior leg has been played. Callers
 * must therefore not assume `build()` returns `legs()` entries.
 */
final class RoundRobinScheduler
{
    /** @var list<int> */
    private array $teams = [];

    private int $legs = 1;

    private bool $shuffle = false;

    /**
     * @param  list<int>  $teams
     */
    public function setTeams(array $teams): self
    {
        $unique = array_values(array_unique(array_map('intval', $teams)));

        if (count($unique) < 2) {
            throw new InvalidArgumentException('A schedule needs at least two distinct teams.');
        }

        $this->teams = $unique;

        return $this;
    }

    public function setLegs(int $legs): self
    {
        if ($legs < 1) {
            throw new InvalidArgumentException('A schedule needs at least one leg.');
        }

        $this->legs = $legs;

        return $this;
    }

    /** Randomise pairing order, match order and initial home advantage. */
    public function shuffle(bool $shuffle = true): self
    {
        $this->shuffle = $shuffle;

        return $this;
    }

    /** How many legs `build()` will actually emit. */
    public function generatedLegs(): int
    {
        return $this->withholdsFinalLeg() ? $this->legs - 1 : $this->legs;
    }

    public function withholdsFinalLeg(): bool
    {
        return $this->legs > 1 && $this->legs % 2 === 1;
    }

    /**
     * @return array<int, list<array{0: int, 1: int}>> Keyed by leg number (1-based).
     */
    public function build(): array
    {
        $pairings = $this->pairings();

        $schedule = [];

        for ($leg = 1; $leg <= $this->generatedLegs(); $leg++) {
            $invert = $leg % 2 === 0;

            $matches = array_map(
                static fn (array $pair): array => $invert ? [$pair[1], $pair[0]] : $pair,
                $pairings,
            );

            if ($this->shuffle) {
                shuffle($matches);
            }

            $schedule[$leg] = $matches;
        }

        return $schedule;
    }

    /**
     * Every unordered pair, oriented for leg 1.
     *
     * @return list<array{0: int, 1: int}>
     */
    private function pairings(): array
    {
        $teams = $this->teams;
        $count = count($teams);

        $pairings = [];

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $pairings[] = [$teams[$i], $teams[$j]];
            }
        }

        if (! $this->shuffle) {
            return $pairings;
        }

        shuffle($pairings);

        return array_map(
            static fn (array $pair): array => random_int(0, 1) === 1 ? [$pair[1], $pair[0]] : $pair,
            $pairings,
        );
    }
}
