<?php

namespace Framework\System\Routing;

interface Sluggable
{
    public function toUrl(Route $route);
}