<?php

namespace Src\Services\Routing\Selectors;

use Src\Services\Routing\Collections\RouteCollection;

interface RouteSelector
{
    public function select(RouteCollection $routes): RouteCollection;
}