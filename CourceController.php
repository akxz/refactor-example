<?php

/**
* ЗАДАЧА СОИСКАТЕЛЮ
*
* Применяя принципы SOLID и заветы "чистого" кода
* 1) Отрефакторить метод index в рамках Laravel
*
* Полученный результат должен соответствовать DRY, KISS
* Очевидно что рефакторинг абстрактный и как-то запускаться/тестироваться не должнен.
* Важно понимание "грязного" кода и правил написания "чистого" кода.
*
* 2) Рассказать о проблемах данного метода
*/

    //Данный кусок кода запрашивает формированный массив данных для построения страницы купленных курсов в личном кабинете пользователя//
    public function index(Request $request) : JsonResponse
    {
        $currentUser = Auth::user();
        $message = 'У пользователя есть курсы';
        if ($currentUser->roles[0]->name == 'teacher') {
            // xz if teacher
            $TeacherObject = new Teacher();
            $currentUser['id'] = $TeacherObject->findTeacherById($currentUser['id']);
            $courses = Course::with(['partners' => function ($query) {
                $query->select('pathToLogo', 'link', 'isPublish')
                    ->where('isPublish', true);
            }])
                ->whereIn('courses.id', array_column($currentUser->link_teacher_courses->toArray(), 'idCourse'))
                ->where([['courses.lang', '=', $request->query('lang')]])
                ->get();
            return $this->jsonResponse([
                'status' => true,
                'data' => new UsersCourseCollection($courses),
                'control' => $message,
            ]);
        } else {
            $courses = Course::with(['partners' => function ($query) {
                $query->select('pathToLogo', 'link', 'isPublish')
                    ->where('isPublish', true);
            }])
                ->whereIn('courses.id', array_column($currentUser->link_users_courses->toArray(), 'idCourse'))
                ->where([['courses.lang', '=', $request->query('lang')]])
                ->get();

            $message = 'У пользователя есть курсы';

            $courseId = Dictionary::where([
                ['key', '=', 'defaultCourseForUser'],
                ['lang', '=', $request->query('lang')]
            ])->first();
            // если есть дефолтный курс
            if ($courseId->value) {
                // узнаем есть ли у пользователя транзакции в статусе pending для этого курса
                $userTransactions = Transaction::where([
                    ['user_id', '=', $currentUser->id],
                    ['paymentStatus', '=', 'pending'],
                ])
                    ->leftJoin('transactionsHistory as tH', 'tH.idTransaction', '=', 'transactions.id')
                    ->where('tH.idCourse', $courseId->value)
                    ->select(['tH.idCourse as idcourse', 'transactions.user_id as user_id'])
                    ->exists();
            }
            // если есть дефолтный курс и у пользователя нет ни одного курса (новый пользователь),
            // и нет такого же курса со статусом Ожидает оплаты, записываем ему дефолтный
            if ($courseId->value && (count($courses) < 1) && !$userTransactions) {
                $teacherId = $this->appointedTeacher($courseId->value);

                try {
                    $linkUserCourses = new LinkUsersCourse();

                    $linkUserCourses->teacher_id = $teacherId;
                    $linkUserCourses->appointment_date = now();
                    $linkUserCourses->idUser = Auth::user()->id;
                    $linkUserCourses->idCourse = $courseId->value;

                    if ($linkUserCourses->save()) {

                        $courses = Course::with(['partners' => function ($query) {
                            $query->select('pathToLogo', 'link', 'isPublish')
                                ->where('isPublish', true);
                        }])->with(['direction:id,title'])->with(['modules'])
                            ->where([
                                ['id', '=', $courseId->value],
                                ['lang', '=', $request->query('lang', 'ru')]
                            ])
                            ->get();
                    }

                    $message = 'У пользователя курсов не было, добавили';

                } catch (\Exception $ex) {
                    return response()->json([
                        'message' => 'Ошибка сохранения курса для пользователя',
                        'error' => $ex
                    ], 401);
                }
            }
            return $this->jsonResponse([
                'status' => true,
                'data' => new UsersCourseCollection($courses),
                'control' => $message,
            ]);
        }
    }
