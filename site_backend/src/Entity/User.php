<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"username"}, message="Ya este usuario está registrado")
 * @UniqueEntity(fields={"correo"}, message="Ya este correo está registrado")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $apellidos;

    /**
     * @ORM\Column(type="float")
     */
    private $saldo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    private $correo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $dir;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telf;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registro;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $idTelegram;

    /**
     * @var Pedido[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="Pedido",
     *      mappedBy="cliente",
     *      orphanRemoval=true,
     *      cascade={"persist"}
     *     )
     */
    private $pedidos;

    /**
     * @var Pedido[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="Pedido",
     *      mappedBy="trabajador",
     *      orphanRemoval=true,
     *      cascade={"persist"}
     *     )
     */
    private $pedidosAceptados;

    /**
     * @ORM\Column(type="float")
     */
    private $deuda;

    /**
     * @ORM\OneToMany(targetEntity=Cuadre::class, mappedBy="trabajador_saliente")
     */
    private $cuadres;

    /**
     * @ORM\OneToMany(targetEntity=Cuadre::class, mappedBy="trabajador_entrante", orphanRemoval=true)
     */
    private $cuadres_entrante;

    /**
     * @ORM\ManyToMany(targetEntity=Negocio::class, mappedBy="trabajadores")
     */
    private $negocios;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $estado;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    public function __construct()
    {
        $this->cuadres = new ArrayCollection();
        $this->cuadres_entrante = new ArrayCollection();
        $this->negocios = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): self
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    public function getSaldo(): ?float
    {
        return $this->saldo;
    }

    public function setSaldo(float $saldo): self
    {
        $this->saldo = $saldo;

        return $this;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(?string $correo): self
    {
        $this->correo = $correo;

        return $this;
    }

    public function getDir(): ?string
    {
        return $this->dir;
    }

    public function setDir(?string $dir): self
    {
        $this->dir = $dir;

        return $this;
    }

    public function getTelf(): ?string
    {
        return $this->telf;
    }

    public function setTelf(?string $telf): self
    {
        $this->telf = $telf;

        return $this;
    }

    public function getRegistro(): ?\DateTimeInterface
    {
        return $this->registro;
    }

    public function setRegistro(?\DateTimeInterface $registro): self
    {
        $this->registro = $registro;

        return $this;
    }

    public function getRolPadre(): ?string
    {
        if (count($this->roles) == 0) {
            return "ROLE_USER";
        }
        return $this->roles[0];
    }

    public function getIdTelegram(): ?string
    {
        return $this->idTelegram;
    }

    public function setIdTelegram(?string $idTelegram): self
    {
        $this->idTelegram = $idTelegram;

        return $this;
    }

    /**
     * @return Producto[]|ArrayCollection
     */
    public function getPedidos()
    {
        return $this->pedidos;
    }

    /**
     * @param Producto[]|ArrayCollection $pedidos
     */
    public function setPedidos($pedidos): void
    {
        $this->pedidos = $pedidos;
    }

    public function getDeuda(): ?float
    {
        return $this->deuda;
    }

    public function setDeuda(float $deuda): self
    {
        $this->deuda = $deuda;

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
            $cuadre->setTrabajadorSaliente($this);
        }

        return $this;
    }

    public function removeCuadre(Cuadre $cuadre): self
    {
        if ($this->cuadres->removeElement($cuadre)) {
            // set the owning side to null (unless already changed)
            if ($cuadre->getTrabajadorSaliente() === $this) {
                $cuadre->setTrabajadorSaliente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Cuadre[]
     */
    public function getCuadresEntrante(): Collection
    {
        return $this->cuadres_entrante;
    }

    public function addCuadresEntrante(Cuadre $cuadresEntrante): self
    {
        if (!$this->cuadres_entrante->contains($cuadresEntrante)) {
            $this->cuadres_entrante[] = $cuadresEntrante;
            $cuadresEntrante->setTrabajadorEntrante($this);
        }

        return $this;
    }

    public function removeCuadresEntrante(Cuadre $cuadresEntrante): self
    {
        if ($this->cuadres_entrante->removeElement($cuadresEntrante)) {
            // set the owning side to null (unless already changed)
            if ($cuadresEntrante->getTrabajadorEntrante() === $this) {
                $cuadresEntrante->setTrabajadorEntrante(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->nombre . ' ' . $this->apellidos;
    }

    /**
     * @return Collection|Negocio[]
     */
    public function getNegocios(): Collection
    {
        return $this->negocios;
    }

    public function addNegocio(Negocio $negocio): self
    {
        if (!$this->negocios->contains($negocio)) {
            $this->negocios[] = $negocio;
            $negocio->addTrabajadore($this);
        }

        return $this;
    }

    public function removeNegocio(Negocio $negocio): self
    {
        if ($this->negocios->removeElement($negocio)) {
            $negocio->removeTrabajadore($this);
        }

        return $this;
    }

    public function permisoNegocio(Negocio $negocio): ?bool
    {
        return $this->getNegocios()->contains($negocio);
    }

    public function descontar(float $cantidad)
    {
        if ($cantidad > $this->getSaldo()) {
            throw new \Exception("Saldo insuficiente.");
        } else
            $this->saldo = $this->getSaldo() - $cantidad;
    }

    public function acreditar(float $cantidad)
    {
        $this->saldo = $this->getSaldo() + $cantidad;
    }

    public function getPedidoConf()
    {
        for ($i = 0; $i < $this->pedidos->count(); $i++) {
            if ($this->pedidos[$i]->getEstado() == 'confección')
                return $this->pedidos[$i];
        }
        return null;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }


}
