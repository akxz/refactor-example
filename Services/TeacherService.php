<?php

class TeacherService
{
    public function getTeacherByUserId($userId)
    {
        return (new Teacher())->findTeacherById($userId);
    }

    public function getCourseIdsByTeacher($teacher)
    {
        return array_column($teacher->link_teacher_courses->toArray(), 'idCourse');
    }
}
