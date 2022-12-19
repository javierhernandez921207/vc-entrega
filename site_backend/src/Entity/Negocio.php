<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NegocioRepository")
 */
class Negocio
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
     * @ORM\OneToMany(targetEntity="App\Entity\Producto", mappedBy="negocio")
     */
    private $productos;

    /**
     * @ORM\OneToMany(targetEntity=Cuadre::class, mappedBy="negocio")
     */
    private $cuadres;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="negocios")
     */
    private $trabajadores;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cadena;

    public function __construct()
    {
        $this->productos = new ArrayCollection();
        $this->cuadres = new ArrayCollection();
        $this->trabajadores = new ArrayCollection();
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

    /**
     * @return Collection|Producto[]
     */
    public function getProductos(): Collection
    {
        return $this->productos;
    }

    public function addProducto(Producto $producto): self
    {
        if (!$this->productos->contains($producto)) {
            $this->productos[] = $producto;
            $producto->setNegocio($this);
        }

        return $this;
    }

    public function removeProducto(Producto $producto): self
    {
        if ($this->productos->contains($producto)) {
            $this->productos->removeElement($producto);
            // set the owning side to null (unless already changed)
            if ($producto->getNegocio() === $this) {
                $producto->setNegocio(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Cuadre[]
     */
    public function getCuadres(): Collection
    {
        return $this->cuadres;
    }

    public function addCuadre(Cuadre $cuadre): self
    {
        if (!$this->cuadres->contains($cuadre)) {
            $this->cuadres[] = $cuadre;
            $cuadre->setNegocio($this);
        }

        return $this;
    }

    public function removeCuadre(Cuadre $cuadre): self
    {
        if ($this->cuadres->removeElement($cuadre)) {
            // set the owning side to null (unless already changed)
            if ($cuadre->getNegocio() === $this) {
                $cuadre->setNegocio(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getTrabajadores(): Collection
    {
        return $this->trabajadores;
    }

    public function addTrabajadore(User $trabajadore): self
    {
        if (!$this->trabajadores->contains($trabajadore)) {
            $this->trabajadores[] = $trabajadore;
        }

        return $this;
    }

    public function removeTrabajadore(User $trabajadore): self
    {
        $this->trabajadores->removeElement($trabajadore);

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

    public function __toString()
    {
        return $this->nombre;
    }

    public function getCadena(): ?string
    {
        return $this->cadena;
    }

    public function setCadena(?string $cadena): self
    {
        $this->cadena = $cadena;

        return $this;
    }

    public function getResultInversionCadena(): ?string
    {
        $elementos = explode(" ",$this->cadena);
        $total = $this->getTotalInvertido();
        $sig = "";
        foreach($elementos as $e)
        {
            if ($e == '+' || $e == '-' || $e == '*' || $e == '/') {
                $sig = $e;
            }
            else{
                if($sig == "" || !is_numeric($e)){
                    $total = "Error en cadena.";
                    break;
                }
                if ($sig == '+') {
                    $total += $e;
                } else if ($sig == '-') {
                    $total -= $e;
                }else if ($sig == '*') {
                    $total *= $e;
                } else if ($sig == '/') {
                    $total /= $e;
                }
            }
        }
        return $total;
    }

}
