<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Invoice;

/**
 * Yearly statistics
 */
class Year
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

    public function setMonth(Month $month): Year
    {
        $this->months[(int) $month->getMonth()] = $month;

        return $this;
    }

    public function getOrAddMonth(int $month): ?Month
    {
        if (isset($this->months[$month])) {
            return $this->months[$month];
        }
        else {
            $monthObject = new Month($month);
            $this->months[$month] = $monthObject;
            return $monthObject;
        }
    }



    /**
     * @return Month[]
     */
    public function getMonths(): array
    {
        return array_values($this->months);
    }
}
