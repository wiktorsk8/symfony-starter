<?php

declare(strict_types=1);

namespace App\ShortLink\Infrastructure\Doctrine\Entity;

use App\ShortLink\Infrastructure\Doctrine\Repository\ShortLinkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'short_links')]
#[ORM\Entity(repositoryClass: ShortLinkRepository::class)]

class ShortLink
{
    #[ORM\Id]
    #[ORM\Column(type: "string", unique: true)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $slug;

    #[ORM\Column(type: 'string', length: 255)]
    private string $url;

    #[ORM\Column(type: 'integer')]
    private int $count;


    public function getId(): string
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
