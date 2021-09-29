<?php

    public function index(GetCoursesRequest $request) : JsonResponse
    {
        try {
            $validated = $request->validated();
            $response = (new CourseService($validated))->getOrCreateCourses();

            return $this->jsonResponse($response);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка сохранения курса для пользователя',
                'error' => $e->getMessage();
            ], 401);
        }
    }
