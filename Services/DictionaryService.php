<?php

class DictionaryService
{
    private $lang;

    public function __construct($lang)
    {
        $this->lang = $lang;
    }

    public function getDefaultCourseId()
    {
        $course = Dictionary::where([
                ['key', '=', 'defaultCourseForUser'],
                ['lang', '=', $this->lang]
            ])
            ->first();

        return (is_null($course)) ? false : $course->value;
    }
}
