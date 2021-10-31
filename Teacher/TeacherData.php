<?php

namespace App\Teacher;

use App\Teacher\Contracts\TeacherDataInterface;

class TeacherData implements TeacherDataInterface
{
    private $model;

    public function __construct()
    {
        $this->model = new Teacher();
    }

    public function getCoursesIdsByUser($id)
    {
        $teacher = $this->findTeacherById($id);

        return array_column($teacher->link_teacher_courses->toArray(), 'idCourse');
    }

    private function findTeacherById($id)
    {
        return $this->model->findTeacherById($id);
    }
}
