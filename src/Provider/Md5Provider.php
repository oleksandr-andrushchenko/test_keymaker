<?php

namespace App\Provider;

use App\Provider;

class Md5Provider extends Provider
{
    public function getShortCodeByUrl(string $url): ?string
    {
        $shortCode = md5($url);

        if (!$this->getUrlByShortCode($shortCode)) {
            $this->insert($url, $shortCode);
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

    private function insert(string $url, string $shortCode): bool
    {
        $stmt = $this->app->getDb()->prepare('insert into url_md5_provider values(?, ?)');

        return $stmt->execute([$shortCode, $url]);
    }
}