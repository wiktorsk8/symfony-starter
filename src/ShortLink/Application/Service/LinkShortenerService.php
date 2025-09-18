<?php

declare(strict_types=1);

namespace App\ShortLink\Application\Service;

class LinkShortenerService
{
    private const string CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function generateSlug(int $seqNumber): string
    {
        return $this->toBase62($seqNumber);
    }

    private function toBase62(int $number): string
    {
        $size = strlen(self::CHARS);
        $result = '';

        while ($number > 0) {
            $reminder = $number % $size;
            $result .= self::CHARS[$reminder];
            $number = intval($number / $size);
        }

        return $result;
    }
}
