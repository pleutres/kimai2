<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\UserVacation;

class VacationUser
{
    /**
     * @var int
     */
    protected $vacation = 0;

    /**
     * @var int
     */
    protected $nonpaid = 0;

    protected $total = 0;

    /**
     * @var string
     */
    protected $userId;



    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getVacation(): int
    {
        return $this->vacation;
    }

    /**
     * @param int $vacation
     */
    public function setVacation(int $vacation): void
    {
        $this->vacation = $vacation;
    }

    /**
     * @return int
     */
    public function getNonpaid(): int
    {
        return $this->nonpaid;
    }

    /**
     * @param int $nonpaid
     */
    public function setNonpaid(int $nonpaid): void
    {
        $this->nonpaid = $nonpaid;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getWorkedDay($monthWorkday) {
        return $monthWorkday - $this->vacation - $this->nonpaid;
    }

    public function sumVacation($vacation)
    {
        $this->vacation += $vacation;
    }


}

