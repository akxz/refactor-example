<?php

class DefaultCourseService
{
    private $user;
    private $lang;
    private $defaultCourseId = false;

    public function __construct($user, $lang)
    {
        $this->lang = $lang;
        $this->user = $user;
    }

    public function tryToAddDefaultCourseToUser()
    {
        if (! $this->defaultCourseId = $this->getDefaultCourseId()) {
            return false;
        }

        if ($this->hasTransactionsWithPendingStatus()) {
            return false;
        }

        if ($this->addDefaultCourseToUser()) {
            return $this->defaultCourseId;
        }

        return false;
    }

    public function getDefaultCourseId()
    {
        return (new DictionaryService($this->lang)->getDefaultCourseId());
    }

    private function hasTransactionsWithPendingStatus()
    {
        return (new TransactionService($this->user->id))
            ->checkPendingTransactionsByCourse($this->defaultCourseId);
    }

    private function addDefaultCourseToUser()
    {
        if (! $this->addCourseToUser($this->defaultCourseId)) {
            throw new \Exception('Ошибка сохранения курса для пользователя');
        }

        return true;
    }

    private function addCourseToUser($courseId)
    {
        $teacherId = $this->appointedTeacher($courseId);
        $linkService = new LinkUsersCourseService($this->currentUser->id);

        return $linkService->store($teacherId, $courseId);
    }
}
