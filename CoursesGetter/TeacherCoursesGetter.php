<?php

namespace App\CoursesGetter;

use App\Course\CourseBuilder;
use App\CoursesGetter\Contracts\CoursesGetterInterface;
use App\CoursesGetter\Contracts\CourseBuilderInterface;
use App\Message\Contracts\MessageInterface;
use App\Teacher\TeacherData;

class TeacherCoursesGetter implements CoursesGetterInterface, MessageInterface
{
    private $course_builder;

    private $dto;

    private $teacher_data;

    private $message = '';

    public function __construct($dto)
    {
        $this->course_builder = new CourseBuilder();
        $this->dto = $dto;
        $this->teacher_data = new TeacherData();
    }

    public function getCourses()
    {
        $ids = $this->getTeacherCoursesIds();
        $this->course_builder->withPartners();
        $this->course_builder->whereId($ids);
        $this->course_builder->whereLang($this->dto->lang);
        $courses = $this->course_builder->getResults();

        if (count($courses) > 0) {
            $this->setMessage('У пользователя есть курсы');
        }

        return $courses;
    }

    private function getTeacherCoursesIds()
    {
        return $this->teacher_data->getCoursesIdsByUser($this->dto->user->id);
    }

    private function setMessage($str)
    {
        $this->message = $str;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
