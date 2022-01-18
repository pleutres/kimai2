<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\UserInvoice;

use App\Model\UserVacation\AbstractMonth;

/**
 * Monthly statistics
 */
class Month extends AbstractMonth
{

    /**
     * @var User[]
     */
    protected $users = [];



    public function setUsers(User $user): Month
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

    public function getOrAddUser(string $user): ?User
    {
        if (isset($this->users[$user])) {
            return $this->users[$user];
        }
        else {
            $userObject = new User($user);
            $this->users[$user] = $userObject;
            return $userObject;
        }
    }




    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return array_values($this->users);
    }
}
