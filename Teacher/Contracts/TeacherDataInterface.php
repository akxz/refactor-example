<?php

namespace App\Teacher\Contracts;

interface TeacherDataInterface
{
    public function getCoursesIdsByUser($id);

    public function findTeacherById($id);
}
