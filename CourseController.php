<?php

    public function index(GetCoursesRequest $request) : JsonResponse
    {
        try {
            $dto = UserDto::makeFromRequest($request);
            $service = new GetCoursesService($dto);

            return $this->jsonResponse([
                'status' => true,
                'data' => new UsersCourseCollection($service->getCourses()),
                'control' => $service->getMessage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка сохранения курса для пользователя',
                'error' => $e->getMessage();
            ], 401);
        }
    }
