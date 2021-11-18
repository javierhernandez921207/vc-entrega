<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exception;


/**
 * @ORM\Entity(repositoryClass="App\Repository\PedidoRepository")
 */
class Pedido
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
    private $estado;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fecha;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User",  inversedBy="pedidos")
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $cliente;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User",  inversedBy="pedidosAceptados")
     * @ORM\JoinColumn(nullable=true)
     *
     */
    private $trabajador;

    /**
     * @var ProductoPedido[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="ProductoPedido",
     *      mappedBy="pedido",
     *      orphanRemoval=true,
     *      cascade={"persist"}
     * )
     */
    private $productosPedido;

    /**
     * @ORM\Column(type="float")
     */
    private $total;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $metpago;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nombPer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ciPer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telPer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dirPer;

    /**
     * @ORM\OneToOne(targetEntity=TransportePedido::class, cascade={"persist", "remove"})
     */
    private $transporte;


    /**
     * Pedido constructor.
     */
    public function __construct()
    {
        $this->productosPedido = new ArrayCollection();
        $this->fecha = new \DateTime('now');
        $this->estado = "confección";
        $this->total = 0;
    }

    /**
     * @return User
     */
    public function getTrabajador(): User
    {
        return $this->trabajador;
    }

    /**
     * @param User $trabajador
     */
    public function setTrabajador(User $trabajador): void
    {
        $this->trabajador = $trabajador;
    }


    /**
     * @return User
     */
    public function getCliente(): User
    {
        return $this->cliente;
    }

    /**
     * @param User $cliente
     */
    public function setCliente(User $cliente): void
    {
        $this->cliente = $cliente;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getEstado(): ?string
    {
        return $this->estado;
    }

    /**
     * @param string $estado
     * @return $this
     */
    public function setEstado(string $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    /**
     * @param \DateTimeInterface $fecha
     * @return $this
     */
    public function setFecha(\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * @return ProductoPedido[]|ArrayCollection
     */
    public function getProductosPedido()
    {
        return $this->productosPedido;
    }


    /**
     * @param Producto $p
     * @param int $cant
     * @throws Exception
     */
    public function addProductoPedido(Producto $p, int $cant): void
    {

        if ($cant > $p->getCantidad())
            throw new Exception("La cantidad pedida excede a la existencia de este producto.");
        else {
            $nuevo = new ProductoPedido($p->getId(), $cant, $p->getPrecio(), $p->getCosto(), $this, $p->getNombre());
            $esta = false;
            foreach ($this->productosPedido as $item) {
                if ($item->getIdProducto() == $nuevo->getIdProducto()) {
                    if ($item->getCantidad() + $nuevo->getCantidad() > $p->getCantidad())
                        throw new Exception("La cantidad pedida excede a la existencia de este producto.");
                    else {
                        $esta = true;
                        $item->setCantidad($item->getCantidad() + $nuevo->getCantidad());
                        $this->setTotal($this->getTotal() + $item->getPrecio() * $cant);
                        break;
                    }
                }
            }
            if (!$esta) {
                $this->productosPedido->add($nuevo);
                $this->setTotal($this->getTotal() + $nuevo->getPrecio() * $cant);
            }
        }
    }


    /**
     * @param ProductoPedido $p
     * Elimina un producto del pedido
     */
    public function elimProductoPedido(ProductoPedido $p): void
    {
        $this->setTotal($this->getTotal() - $p->getPrecio() * $p->getCantidad());
        $this->productosPedido->removeElement($p);
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

    public function getGanaciaPedido()
    {
        $ganaciaPedido = 0;
        foreach ($this->productosPedido as $prod) {
            $ganaciaPedido += ($prod->getPrecio() - $prod->getCosto()) * $prod->getCantidad();
        }
        return $ganaciaPedido;
    }

    public function getMetpago(): ?string
    {
        return $this->metpago;
    }

    public function setMetpago(?string $metpago): self
    {
        $this->metpago = $metpago;

        return $this;
    }

    public function getNombPer(): ?string
    {
        return $this->nombPer;
    }

    public function setNombPer(?string $nombPer): self
    {
        $this->nombPer = $nombPer;

        return $this;
    }

    public function getCiPer(): ?string
    {
        return $this->ciPer;
    }

    public function setCiPer(?string $ciPer): self
    {
        $this->ciPer = $ciPer;

        return $this;
    }

    public function getTelPer(): ?string
    {
        return $this->telPer;
    }

    public function setTelPer(?string $telPer): self
    {
        $this->telPer = $telPer;

        return $this;
    }

    public function getDirPer(): ?string
    {
        return $this->dirPer;
    }

    public function setDirPer(?string $dirPer): self
    {
        $this->dirPer = $dirPer;

        return $this;
    }

    public function getTransporte(): ?TransportePedido
    {
        return $this->transporte;
    }

    public function setTransporte(?TransportePedido $transporte): self
    {
        $this->transporte = $transporte;

        return $this;
    }


}
