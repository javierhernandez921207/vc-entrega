<?php

namespace App\Entity;

use App\Repository\TransportePedidoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransportePedidoRepository::class)
 */
class TransportePedido
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $municipio;

    /**
     * @ORM\Column(type="float")
     */
    private $tarifa;

    /**
     * TransportePedido constructor.
     * @param $municipio
     * @param $tarifa
     */

    public function __construct($municipio, $tarifa)
    {
        $this->municipio = $municipio;
        $this->tarifa = $tarifa;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMunicipio(): ?string
    {
        return $this->municipio;
    }

    public function setMunicipio(string $municipio): self
    {
        $this->municipio = $municipio;

        return $this;
    }

    public function getTarifa(): ?float
    {
        return $this->tarifa;
    }

    public function setTarifa(float $tarifa): self
    {
        $this->tarifa = $tarifa;

        return $this;
    }
}
