<?php

namespace App\Services;

use App\CoursesGetter\Contracts\CoursesGetterFactory;

class GetCoursesService
{
    private $courses_getter;

    private $getter_factory;

    public function __construct($dto)
    {
        $this->getter_factory = new CoursesGetterFactory($dto);
    }

    public function getCourses()
    {
        $this->courses_getter = $this->getter_factory->getByRole();

        return $this->courses_getter->getCourses();
    }

    public function getMessage()
    {
        return $this->courses_getter->getMessage();
    }
}
