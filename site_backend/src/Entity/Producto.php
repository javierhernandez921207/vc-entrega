<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use mysql_xdevapi\Exception;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductoRepository")
 */
class Producto
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
     * @ORM\Column(type="float")
     */
    private $precio;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descr;

    /**
     * @ORM\Column(type="integer")
     */
    private $cantidad;

    /**
     * @var Categoria
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Categoria", inversedBy="productos")
     * @ORM\JoinColumn(
     *     nullable=true,
     *      onDelete="CASCADE")
     */
    private $categoria;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registro;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $img;

    /**
     * @ORM\Column(type="float")
     */
    private $costo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cantMin;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Negocio", inversedBy="productos")
     * @ORM\JoinColumn(nullable = true, onDelete="CASCADE")
     */
    private $negocio;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cantidad_cuadre;

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

    public function getPrecio(): ?float
    {
        return $this->precio;
    }

    public function setPrecio(float $precio): self
    {
        $this->precio = $precio;

        return $this;
    }

    public function getDescr(): ?string
    {
        return $this->descr;
    }

    public function setDescr(?string $descr): self
    {
        $this->descr = $descr;

        return $this;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getRegistro(): ?\DateTimeInterface
    {
        return $this->registro;
    }

    public function setRegistro(\DateTimeInterface $registro): self
    {
        $this->registro = $registro;

        return $this;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): self
    {
        $this->img = $img;

        return $this;
    }

    /**
     * @return Categoria
     */
    public function getCategoria(): Categoria
    {
        return $this->categoria;
    }

    /**
     * @param Categoria $categoria
     */
    public function setCategoria(Categoria $categoria): void
    {
        $this->categoria = $categoria;
    }

    public function getCosto(): ?float
    {
        return $this->costo;
    }

    public function setCosto(float $costo): self
    {
        $this->costo = $costo;

        return $this;
    }

    public function getCantMin(): ?int
    {
        return $this->cantMin;
    }

    public function setCantMin(?int $cantMin): self
    {
        $this->cantMin = $cantMin;
        return $this;
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

    public function getCantidadCuadre(): ?int
    {
        return $this->cantidad_cuadre;
    }

    public function setCantidadCuadre(?int $cantidad_cuadre): self
    {
        $this->cantidad_cuadre = $cantidad_cuadre;

        return $this;
    }

    public function descontar(int $cantidad): void
    {
        if ($cantidad > $this->cantidad)
            throw new Exception('Producto ' . $this->nombre . ' agotado.');
        else
            $this->cantidad = $this->getCantidad() - $cantidad;
    }


}
