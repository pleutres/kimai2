<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\UserVacation;

/**
 * Monthly statistics
 */
class VacationMonth extends AbstractMonth
{

    /**
     * @var string
     */
    protected $days = [];

    /**
     * @var User[]
     */
    protected $users = [];

    public function setDays(VacationDay $day): VacationMonth
    {
        $this->days[(int) $day->getDay()] = $day;
        return $this;
    }

    public function getDay(int $dayId): ?User
    {
        if (isset($this->days[$dayId])) {
            return $this->days[$dayId];
        }

        return null;
    }

    public function getOrAddDay(string $dayId, $couldadd): ?VacationDay
    {
        if (isset($this->days[$dayId])) {
            return $this->days[$dayId];
        }
        else if ($couldadd) {
            $dayObject = new VacationDay($dayId);
            $this->days[$dayId] = $dayObject;
            return $dayObject;
        }
        return null;
    }

    /**
     * @return User[]
     */
    public function getDays(): array
    {
        return array_values($this->days);
    }

    public function sumVacationForUser($userId, $vacationDuration)
    {
        if (!isset($this->users[$userId])) {
            $curUser = new VacationUser($userId);
            $this->users[$userId] = $curUser;
        }
        else {
            $curUser = $this->users[$userId];
        }
        $curUser->sumVacation($vacationDuration);
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }


}
