<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\UserVacation;

/**
 * Yearly statistics
 */
class VacationYear
{
    /**
     * @var string
     */
    protected $year;
    /**
     * @var Month[]
     */
    protected $months = [];
    /**
     * @var Month[]
     */
    protected $users = [];

    /**
     * @param string $year
     */
    public function __construct($year)
    {
        $this->year = $year;
    }

    /**
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    public function setMonth(VacationMonth $month): VacationYear
    {
        $this->months[(int) $month->getMonth()] = $month;

        return $this;
    }

    public function getOrAddMonth(int $month, $couldAdd): ?VacationMonth
    {
        if (isset($this->months[$month])) {
            return $this->months[$month];
        }
        else if ($couldAdd) {
            $monthObject = new Month($month);
            $this->months[$month] = $monthObject;
            return $monthObject;
        }
    }

    public function sumVacationForUser($userId, $vacation)
    {
        if (!isset($this->users[$userId])) {
            $curUser = new VacationUser($userId);
            $this->users[$userId] = $curUser;
        }
        else {
            $curUser = $this->users[$userId];
        }
        $curUser->sumVacation($vacation);
    }


    /**
     * @return Month[]
     */
    public function getMonths(): array
    {
        return array_values($this->months);
    }

    /**
     * @return Month[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }


}
