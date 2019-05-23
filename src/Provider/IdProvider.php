<?php

namespace App\Provider;

use App\Provider;

class IdProvider extends Provider
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
        return is_numeric($shortCode) && 0 < $shortCode;
    }

    public function getUrlByShortCode(string $shortCode): ?string
    {
        $stmt = $this->app->getDb()->prepare('select url from url_id_provider where id = ? limit 1');

        if ($stmt->execute([$shortCode])) {
            return $stmt->fetchColumn() ?: null;
        }

        return null;
    }

    private function select(string $url): ?int
    {
        $stmt = $this->app->getDb()->prepare('select id from url_id_provider where url = ? limit 1');

        if ($stmt->execute([$url])) {
            return $stmt->fetchColumn() ?: null;
        }

        return null;
    }

    private function insert(string $url): ?int
    {
        $stmt = $this->app->getDb()->prepare('insert into url_id_provider(url) values(?)');

        if ($stmt->execute([$url])) {
            return $this->app->getDb()->lastInsertId();
        }

        return null;
    }
}