<?php

class LinkUsersCourseService
{
    private $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function store($teacherId, $courseId)
    {
        $linkUserCourses = new LinkUsersCourse();
        $linkUserCourses->teacher_id = $teacherId;
        $linkUserCourses->appointment_date = now();
        $linkUserCourses->idUser = $this->userId;
        $linkUserCourses->idCourse = $courseId;

        return $linkUserCourses->save();
    }
}
