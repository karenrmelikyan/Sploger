<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use Exception;

final class Server
{
    private ?int $id;
    private string $name;
    private ?string $provider;
    private string $ipAddress;
    private ?string $os;
    private ?string $osVersion;
    private ?bool $connected;
    private ?bool $online;
    private ?int $webApplicationsCount = null;
    private ?string $country = null;
    private ?DateTimeImmutable $createdAt;

    public function __construct(string $name, string $ipAddress)
    {
        $this->name = $name;
        $this->ipAddress = $ipAddress;
    }

    /**
     * @param array $data
     * @return static
     * @throws Exception
     */
    public static function fromApiResponse(array $data): self
    {
        $self = new self($data['name'], $data['ipAddress']);
        $self->setId($data['id']);
        $self->setProvider($data['provider']);
        $self->setOs($data['os']);
        $self->setOsVersion($data['osVersion']);
        $self->setConnected($data['connected']);
        $self->setOnline($data['online']);
        $self->setCreatedAt(new DateTimeImmutable($data['created_at']));

        return $self;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getProvider(): ?string
    {
        return $this->provider;
    }

    /**
     * @param string|null $provider
     */
    public function setProvider(?string $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     */
    public function setIpAddress(string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * @return string|null
     */
    public function getOs(): ?string
    {
        return $this->os;
    }

    /**
     * @param string|null $os
     */
    public function setOs(?string $os): void
    {
        $this->os = $os;
    }

    /**
     * @return string|null
     */
    public function getOsVersion(): ?string
    {
        return $this->osVersion;
    }

    /**
     * @param string|null $osVersion
     */
    public function setOsVersion(?string $osVersion): void
    {
        $this->osVersion = $osVersion;
    }

    /**
     * @return bool|null
     */
    public function getConnected(): ?bool
    {
        return $this->connected;
    }

    /**
     * @param bool|null $connected
     */
    public function setConnected(?bool $connected): void
    {
        $this->connected = $connected;
    }

    /**
     * @return bool|null
     */
    public function getOnline(): ?bool
    {
        return $this->online;
    }

    /**
     * @param bool|null $online
     */
    public function setOnline(?bool $online): void
    {
        $this->online = $online;
    }

    /**
     * @param bool $formatted
     * @return DateTimeImmutable|string|null
     */
    public function getCreatedAt(bool $formatted = false): DateTimeImmutable|string|null
    {
        return ($formatted && $this->createdAt !== null) ? $this->createdAt->format('d F Y') : $this->createdAt;
    }

    /**
     * @param DateTimeImmutable|null $createdAt
     */
    public function setCreatedAt(?DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setWebApplicationsCount(?int $count): void
    {
        $this->webApplicationsCount = $count;
    }

    public function getWebApplicationsCount(): ?int
    {
        return $this->webApplicationsCount;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }
}
