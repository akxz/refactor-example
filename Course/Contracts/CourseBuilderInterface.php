<?php

namespace App\Course\Contracts;

interface CourseBuilderInterface
{
    public function withModules();

    public function withPartners();

    public function withDirection();

    public function whereId();

    public function whereLang();

    public function getResults();

    public function resetBuilder();
}
