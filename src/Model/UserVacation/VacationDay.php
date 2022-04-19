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
class VacationDay
{
    /**
     * @var string
     */
    protected $day;
    /**
     * @var User[]
     */
    protected $users = [];

    public function __construct($day)
    {
        $this->day = $day;
    }

    public function setUsers(VacationUser $user): VacationMonth
    {
        $this->users[(int) $user->getUserId()] = $user;

        return $this;
    }

    public function getUser(int $userId): ?User
    {
        if (isset($this->users[$userId])) {
            return $this->users[$userId];
        }

        return null;
    }

    public function getOrAddUser(string $user, $couldadd): ?VacationUser
    {
        if (isset($this->users[$user])) {
            return $this->users[$user];
        }
        else if ($couldadd) {
            $userObject = new VacationUser($user);
            $this->users[$user] = $userObject;
            return $userObject;
        }
        return null;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return array_values($this->users);
    }

    /**
     * @return string
     */
    public function getDay(): string
    {
        return $this->day;
    }


}
