<?php

class UserService
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function isTeacher()
    {
        $role = $this->getUserRole();

        return $role == 'teacher';
    }

    private function getUserRole()
    {
        return $this->user->roles[0]->name;
    }
}
