<?php

namespace App;

abstract class Provider
{
    protected $app;

    public function __construct(SimpleApp $app)
    {
        $this->app = $app;
    }

    abstract public function getShortCodeByUrl(string $url): ?string;

    abstract public function matchShortCode(string $shortCode): bool;

    abstract public function getUrlByShortCode(string $shortCode): ?string;
}