<?php

namespace App\Course;

use App\Course\Contracts\CourseBuilderInterface;

class CourseBuilder implements CourseBuilderInterface
{
    private $query;

    public function __construct()
    {
        $this->query = new Course();
    }

    public function withModules()
    {
        $this->query->with(['modules']);
    }

    public function withPartners()
    {
        $this->query->with(['partners' => function ($query) {
            $query->select('pathToLogo', 'link', 'isPublish')
                ->where('isPublish', true);
        }]);
    }

    public function withDirection()
    {
        $this->query->with(['direction:id,title']);
    }

    public function whereId($id)
    {
        if (is_array($id)) {
            $this->query->whereIn('id', $id);
        } else {
            $this->query->where('id', $id);
        }
    }

    public function whereLang($lang)
    {
        $this->query->where('lang', $lang);
    }

    public function getResults()
    {
        return $this->query->get();
    }

    public function resetBuilder()
    {
        $this->query = new Course();
    }
}
