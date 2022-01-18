<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\UserInvoice;

class User
{
    /**
     * @var int
     */
    protected $totalDuration = 0;
    /**
     * @var float
     */
    protected $totalRate = 0.00;
    /**
     * @var string
     */
    protected $userId;

    /**
     * @var float
     */
    protected $fees = 0.00;


    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getTotalDuration(): int
    {
        return $this->totalDuration;
    }

    public function setTotalDuration(int $seconds): Day
    {
        $this->totalDuration = $seconds;

        return $this;
    }

    public function getTotalRate(): float
    {
        return $this->totalRate;
    }

    public function setTotalRate(float $totalRate): Day
    {
        $this->totalRate = $totalRate;

        return $this;
    }

    /**
     * @return float
     */
    public function getFees(): float
    {
        return $this->fees;
    }




    public function addStats(float $totalRate, int $totalDuration, float $fees)
    {
        $this->totalDuration = $totalDuration;
        $this->totalRate = $totalRate;
        $this->fees = $fees;

    }
}
