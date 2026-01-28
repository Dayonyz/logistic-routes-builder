<?php

namespace Src\Services\Routing\Builders;

use Src\Services\Routing\Collections\RouteCollection;

interface RouteBuilder
{
    public function getCollectionWithAllPossibleRoutes(): RouteCollection;
}