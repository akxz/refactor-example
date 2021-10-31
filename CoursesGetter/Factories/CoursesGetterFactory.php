<?php

namespace App\CoursesGetter\Factories;

use App\CoursesGetter\Contracts\CoursesGetterFactoryInterface;

class CoursesGetterFactory implements CoursesGetterFactoryInterface
{
    private $dto;

    public function __construct($dto)
    {
        $this->dto = $dto;
    }

    public function getByRole()
    {
        if ($this->isTeacher()) {
            return new TeacherCoursesGetter($dto);
        }

        return new StudentCoursesGetter($dto);
    }

    private function isTeacher()
    {
        return $this->dto->role == 'teacher';
    }
}
