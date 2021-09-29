<?php

class CourseService
{
    private $currentUser;
    private $lang;
    private $message = '';

    public function __construct(array $data)
    {
        $this->lang = $data['lang'] ?? 'ru';
        $this->currentUser = Auth::user();
    }

    public function getOrCreateCourses()
    {
        $courses = $this->checkUserTypeAndGetCourses();

        return $this->makeResponseData($courses);
    }

    private function checkUserTypeAndGetCourses()
    {
        $isTeacher = (new UserService($this->currentUser))->isTeacher();

        if ($isTeacher) {
            return $this->getTeacherCourses();
        }

        return $this->getOrCreateUserCourses();
    }

    private function getTeacherCourses()
    {
        $teacherService = new TeacherService();
        $teacher = $teacherService->getTeacherByUserId($this->currentUser->id);
        $courseIds = $this->getCourseIdsByTeacher($teacher);

        return $this->getCoursesByIds($courseIds);
    }

    private function getCoursesByIds(array $ids)
    {
        return Course::with(['partners' => function ($query) {
                $query->select('pathToLogo', 'link', 'isPublish')
                    ->where('isPublish', true);
            }])
            ->whereIn('courses.id', $ids)
            ->where([['courses.lang', '=', $this->lang]])
            ->get();
    }

    private function getOrCreateUserCourses()
    {
        $courses = $this->getUserCourses();

        if (count($courses) > 0) {
            return $courses;
        }

        if ($defaultCourse = $this->tryToAddDefaultCourse()) {
            return $defaultCourse;
        }

        return $courses;
    }

    private function getUserCourses()
    {
        $courseIds = $this->getUserCourseIds();

        return $this->getCoursesByIds($courseIds);
    }

    private function getUserCourseIds($user)
    {
        return array_column(
            $this->currentUser->link_users_courses->toArray(),
            'idCourse'
        );
    }

    private function tryToAddDefaultCourse()
    {
        $defaultCourseService = new DefaultCourseService($this->currentUser, $this->lang);
        $defaultCourseId = $defaultCourseService->tryToAddDefaultCourseToUser();

        if (! $defaultCourseId) {
            return false;
        }

        $this->setMessage('У пользователя курсов не было, добавили');

        return $this->getCourseById($defaultCourseId);
    }

    private function getCourseById($courseId)
    {
        return Course::with(['partners' => function ($query) {
                $query->select('pathToLogo', 'link', 'isPublish')
                    ->where('isPublish', true);
            }])
            ->with(['direction:id,title', 'modules'])
            ->where([
                ['id', '=', $courseId],
                ['lang', '=', $this->lang]
            ])
            ->get();
    }

    private function makeResponseData($courses)
    {
        $this->updateMessageIfEmpty($cources);

        return [
            'status' => true,
            'data' => new UsersCourseCollection($courses),
            'control' => $this->message,
        ];
    }

    private function updateMessageIfEmpty($courses)
    {
        if ($this->message == '' && count($courses) > 0) {
            $this->setMessage('У пользователя есть курсы');
        }
    }

    private function setMessage($str)
    {
        $this->message = $str;
    }
}
