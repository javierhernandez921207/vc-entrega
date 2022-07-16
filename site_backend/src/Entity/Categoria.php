<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoriaRepository")
 */
class Categoria
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icono;

    /**
     * @var Producto[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="Producto",
     *      mappedBy="categoria",
     *      orphanRemoval=true,
     *      cascade={"persist"}
     * )
     * @ORM\OrderBy()
     */
    private $productos;

    /**
     * @return Producto[]|ArrayCollection
     *
     */
    public function getProductos()
    {
        return $this->productos;
    }

    /**
     * @param Producto[]|ArrayCollection $productos
     */
    public function setProductos($productos): void
    {
        $this->productos = $productos;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getIcono(): ?string
    {
        return $this->icono;
    }

    public function setIcono(?string $icono): self
    {
        $this->icono = $icono;

        return $this;
    }

    public function getTotalInvertido(): ?float
    {
        $cant = 0;
        for ($i = 0; $i < count($this->getProductos()); $i++) {
            $cant += $this->getProductos()->get($i)->getCosto() * $this->getProductos()->get($i)->getCantidad();
        }
        return $cant;
    }

    public function getTotalVenta(): ?float
    {
        $cant = 0;
        for ($i = 0; $i < count($this->getProductos()); $i++) {
            $cant += $this->getProductos()->get($i)->getPrecio() * $this->getProductos()->get($i)->getCantidad();
        }
        return $cant;
    }

    public function getTotalGanacia(): ?float
    {
        return $this->getTotalVenta() - $this->getTotalInvertido();
    }

    public function depVacio(): ?bool
    {
        for ($i = 0; $i < count($this->getProductos()); $i++) {
            if ($this->getProductos()->get($i)->getCantidad() > 0)
                return false;
        }
        return true;
    }

    public function __toString()
    {
        return $this->nombre;
    }

}
