<?php

namespace App\DefaultCourse\Contracts;

use App\DefaultCourse\Contracts\DefaultCourseInterface;
use App\Message\Contracts\MessageInterface;

class DefaultCourse implements DefaultCourseInterface, MessageInterface
{
    private $course_id;

    private $dto;

    private $message = '';

    public function __construct($dto)
    {
        $this->dto = $dto;
    }

    public function saveCourseGetId()
    {
        $this->setCourseId();

        if (! $this->course_id) {
            $this->setMessage('default course not exist');

            return false;
        }

        if (! $this->hasNotPendingTransactions()) {
            $this->setMessage('user has pending transactions');

            return false;
        }

        if (! $this->saveCourse()) {
            $this->setMessage('course not saved');

            return false;
        }

        $this->setMessage('course saved');

        return $this->course_id;
    }

    private function setCourseId()
    {
        $course = Dictionary::where([
            ['key', '=', 'defaultCourseForUser'],
            ['lang', '=', $this->dto->lang]
        ])->first();

        $this->course_id = ($course) ? $course->value : false;
    }

    private function hasNotPendingTransactions()
    {
        return Transaction::where([
            ['user_id', '=', $this->dto->user->id],
            ['paymentStatus', '=', 'pending'],
        ])
            ->leftJoin('transactionsHistory as tH', 'tH.idTransaction', '=', 'transactions.id')
            ->where('tH.idCourse', $this->course_id)
            ->select(['tH.idCourse as idcourse', 'transactions.user_id as user_id'])
            ->doesntExist();
    }

    private function saveCourse()
    {
        $teacherId = $this->appointedTeacher($this->course_id);

        $linkUserCourses = new LinkUsersCourse();
        $linkUserCourses->teacher_id = $teacherId;
        $linkUserCourses->appointment_date = now();
        $linkUserCourses->idUser = $this->dto->user->id;
        $linkUserCourses->idCourse = $this->course_id;

        return $linkUserCourses->save();
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
