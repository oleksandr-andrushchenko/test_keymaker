<?php

namespace App\Provider;

use App\Provider;

class Md5Provider extends Provider
{
    public function getShortCodeByUrl(string $url): ?string
    {
        if (!$shortCode = $this->select($url)) {
            $shortCode = $this->insert($url);
        }

        return $shortCode;
    }

    public function matchShortCode(string $shortCode): bool
    {
        return strlen($shortCode) == 32 && ctype_xdigit($shortCode);
    }

    public function getUrlByShortCode(string $shortCode): ?string
    {
        $stmt = $this->app->getDb()->prepare('select url from url_md5_provider where md5 = ? limit 1');

        if ($stmt->execute([$shortCode])) {
            return $stmt->fetchColumn() ?: null;
        }

        return null;
    }

    private function select(string $url): ?string
    {
        $stmt = $this->app->getDb()->prepare('select md5 from url_md5_provider where url = ? limit 1');

        if ($stmt->execute([$url])) {
            return $stmt->fetchColumn() ?: null;
        }

        return null;
    }

    private function insert(string $url): ?string
    {
        $stmt = $this->app->getDb()->prepare('insert into url_md5_provider values(?, ?)');

        $shortCode = md5($url);

        if ($stmt->execute([$shortCode, $url])) {
            return $shortCode;
        }

        return null;
    }
}