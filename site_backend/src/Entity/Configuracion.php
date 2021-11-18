<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConfiguracionRepository")
 */
class Configuracion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $ganaciaMinPedido;

    /**
     * @ORM\Column(type="boolean")
     */
    private $pagoSaldo;

    /**
     * @ORM\Column(type="boolean")
     */
    private $pagoCash;

    /**
     * @ORM\Column(type="boolean")
     */
    private $pagoPaypal;

    /**
     * @ORM\Column(type="float")
     */
    private $cambiocup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGanaciaMinPedido(): ?float
    {
        return $this->ganaciaMinPedido;
    }

    public function setGanaciaMinPedido(float $ganaciaMinPedido): self
    {
        $this->ganaciaMinPedido = $ganaciaMinPedido;

        return $this;
    }

    public function getPagoSaldo(): ?bool
    {
        return $this->pagoSaldo;
    }

    public function setPagoSaldo(bool $pagoSaldo): self
    {
        $this->pagoSaldo = $pagoSaldo;

        return $this;
    }

    public function getPagoCash(): ?bool
    {
        return $this->pagoCash;
    }

    public function setPagoCash(bool $pagoCash): self
    {
        $this->pagoCash = $pagoCash;

        return $this;
    }

    public function getPagoPaypal(): ?bool
    {
        return $this->pagoPaypal;
    }

    public function setPagoPaypal(bool $pagoPaypal): self
    {
        $this->pagoPaypal = $pagoPaypal;

        return $this;
    }

    public function getCambiocup(): ?float
    {
        return $this->cambiocup;
    }

    public function setCambiocup(float $cambiocup): self
    {
        $this->cambiocup = $cambiocup;

        return $this;
    }
}
