<?php

namespace App\Entity;

use App\Repository\CuadreRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CuadreRepository::class)
 */
class Cuadre
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Negocio::class, inversedBy="cuadres")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $negocio;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fecha;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="cuadres")
     */
    private $trabajador_saliente;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="cuadres_entrante")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trabajador_entrante;

    /**
     * @ORM\Column(type="float")
     */
    private $total;

    /**
     * @ORM\Column(type="float")
     */
    private $ganacia;

    /**
     * @ORM\Column(type="float")
     */
    private $fondo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNegocio(): ?Negocio
    {
        return $this->negocio;
    }

    public function setNegocio(?Negocio $negocio): self
    {
        $this->negocio = $negocio;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getTrabajadorSaliente(): ?User
    {
        return $this->trabajador_saliente;
    }

    public function setTrabajadorSaliente(?User $trabajador_saliente): self
    {
        $this->trabajador_saliente = $trabajador_saliente;

        return $this;
    }

    public function getTrabajadorEntrante(): ?User
    {
        return $this->trabajador_entrante;
    }

    public function setTrabajadorEntrante(?User $trabajador_entrante): self
    {
        $this->trabajador_entrante = $trabajador_entrante;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getGanacia(): ?float
    {
        return $this->ganacia;
    }

    public function setGanacia(float $ganacia): self
    {
        $this->ganacia = $ganacia;

        return $this;
    }

    public function getFondo(): ?float
    {
        return $this->fondo;
    }

    public function setFondo(float $fondo): self
    {
        $this->fondo = $fondo;

        return $this;
    }
}
