<?php

namespace App\CoursesGetter;

use App\Course\CourseBuilder;
use App\CoursesGetter\Contracts\CoursesGetterInterface;
use App\CoursesGetter\Contracts\CourseBuilderInterface;
use App\Message\Contracts\MessageInterface;

class StudentCoursesGetter implements CoursesGetterInterface, MessageInterface
{
    private $course_builder;

    private $dto;

    private $message;

    public function __construct($dto)
    {
        $this->course_builder = new CourseBuilder();
        $this->dto = $dto;
    }

    public function getCourses()
    {
        $ids = $this->getStudentCoursesIds();
        $this->course_builder->withPartners();
        $this->course_builder->whereId($ids);
        $this->course_builder->whereLang($this->dto->lang);
        $courses = $this->course_builder->getResults();

        if (count($courses) > 0) {
            $this->setMessage('У пользователя есть курсы');

            return $courses;
        }

        if ($default_courses = $this->getDefaultCourses()) {
            $courses = $default_courses;
        }

        // $message = $this->getMessage();

        return $courses;
    }

    private function getStudentCoursesIds()
    {
        return $this->dto->link_users_courses->toArray();
    }

    private function setMessage($str)
    {
        $this->message = $str;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getDefaultCourses()
    {
        $default_course = new DefaultCourse($this->dto);
        $course_id = $default_course->saveCourseGetId();
        $this->setMessage($default_course->getMessage());

        if (! $course_id) {
            return false;
        }

        $this->course_builder->resetBuilder();
        $this->course_builder->withPartners();
        $this->course_builder->withDirection();
        $this->course_builder->withModules();
        $this->course_builder->whereId($course_id);
        $this->course_builder->whereLang($this->dto->lang);

        return $this->course_builder->getResults();
    }
}
