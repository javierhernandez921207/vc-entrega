<?php

namespace App\Controller;

use App\Entity\Configuracion;
use App\Entity\Log;
use App\Entity\Pedido;
use App\Entity\Producto;
use App\Entity\ProductoPedido;
use App\Entity\Transporte;
use App\Entity\TransportePedido;
use App\Entity\User;
use App\Form\CantProdType;
use App\Form\PedidoType;
use App\Form\UbicacionPedidoType;
use App\Repository\CategoriaRepository;
use App\Repository\ConfiguracionRepository;
use App\Repository\PedidoRepository;
use App\Repository\UserRepository;
use App\Telegram;
use Knp\Component\Pager\PaginatorInterface;
use mysql_xdevapi\Exception;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Transport;
use Symfony\Component\Routing\Annotation\Route;

date_default_timezone_set("America/Havana");

/**
 * @Route("/pedido")
 */
class PedidoController extends AbstractController
{
    /**
     * @Route("/", name="pedido_index", methods={"GET"})
     */
    public function index(PedidoRepository $pedidoRepository): Response
    {
        return $this->render('pedido/index.html.twig', [
            'pedidos' => $pedidoRepository->findAll(),
        ]);
    }

    /**
     * @Route("/add/{prod}/{cant}", name="pedido_new")
     */
    public function new($prod, $cant, Request $request)
    {
        try {
            $session = $request->getSession();
            $entityManager = $this->getDoctrine()->getManager();
            $producto = $entityManager->getRepository(Producto::class)->findOneById($prod);
            if ($cant > $producto->getCantidad())
                throw new \Exception('Esta cantidad excede a la registrada en el almacén.');
            if (!$session->get('carrito')) {
                $prodPed = array(
                    'id' => $producto->getId(),
                    'nombre' => $producto->getNombre(),
                    'cantidad' => $cant,
                    'precio' => $producto->getPrecio()
                );
                $session->set('carrito', [$prodPed]);
            } else {
                $carrito = $session->get('carrito');
                $esta = false;
                for ($i = 0; $i < count($carrito); $i++) {
                    if ($carrito[$i]['id'] == $producto->getId()) {
                        $carrito[$i]['cantidad'] = $carrito[$i]['cantidad'] + $cant;
                        if ($carrito[$i]['cantidad'] > $producto->getCantidad())
                            throw new \Exception('Esta cantidad excede a la registrada en el almacén.');
                        $esta = true;
                    }
                }
                $session->set('carrito', $carrito);
                if (!$esta) {
                    $prodPed = array(
                        'id' => $producto->getId(),
                        'nombre' => $producto->getNombre(),
                        'cantidad' => $cant,
                        'precio' => $producto->getPrecio()
                    );
                    $productosPed = array_merge($session->get('carrito'), [$prodPed]);
                    $session->set('carrito', $productosPed);
                }
            }
            $this->addFlash('success', "Producto añadido correctamente.");
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }
        return $this->redirectToRoute('categoria_show', ['id' => $producto->getCategoria()->getId()]);
    }

    /**
     * @Route("/addcarrito", options={"expose"=true}, name="pedido_new_ajax")
     */
    public function addcarrito(Request $request, ConfiguracionRepository $configuracionRepository)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $session = $request->getSession();
                $entityManager = $this->getDoctrine()->getManager();
                $p = $request->request->get('prod');
                $cant = $request->request->get('cant');
                $prod = $entityManager->getRepository(Producto::class)->findOneById($p);
                $msg = 0;
                $producto = $entityManager->getRepository(Producto::class)->findOneById($prod);
                if ($cant > $producto->getCantidad() || $cant == 0) {
                    $msg = 1;
                    throw new \Exception('Esta cantidad excede a la registrada en el almacén.');
                }
                if (!$session->get('carrito')) {
                    $prodPed = array(
                        'id' => $producto->getId(),
                        'nombre' => $producto->getNombre(),
                        'cantidad' => $cant,
                        'precio' => $producto->getPrecio()
                    );
                    $session->set('carrito', [$prodPed]);
                } else {
                    $carrito = $session->get('carrito');
                    $esta = false;
                    for ($i = 0; $i < count($carrito); $i++) {
                        if ($carrito[$i]['id'] == $producto->getId()) {
                            $carrito[$i]['cantidad'] = $carrito[$i]['cantidad'] + $cant;
                            if ($carrito[$i]['cantidad'] > $producto->getCantidad()) {
                                $msg = 1;
                                throw new \Exception('Esta cantidad excede a la registrada en el almacén.');
                            }
                            $esta = true;
                        }
                    }
                    $session->set('carrito', $carrito);
                    if (!$esta) {
                        $prodPed = array(
                            'id' => $producto->getId(),
                            'nombre' => $producto->getNombre(),
                            'cantidad' => $cant,
                            'precio' => $producto->getPrecio()
                        );
                        $productosPed = array_merge($session->get('carrito'), [$prodPed]);
                        $session->set('carrito', $productosPed);
                    }
                }
                //$this->addFlash('success', "Producto añadido correctamente.");

            } catch (\Exception $exception) {
                //$this->addFlash('error', $exception->getMessage());
            }
        }
        return new JsonResponse(['carrito' => $session->get('carrito'), 'msg' => $msg, 'cambio' => $configuracionRepository->findOneById(1)->getCambiocup()]);
    }

    /**
     * @Route("/del/prod/{id}", options={"expose"=true}, name="pedido_delete_prod")
     */
    public function deleteProdPed($id, Request $request)
    {
        $carrito = $request->getSession()->get('carrito');
        for ($i = 0; $i < count($carrito); $i++) {
            if ($carrito[$i]['id'] == $id) {
                array_splice($carrito, $i, 1);
            }
        }
        $request->getSession()->set('carrito', $carrito);
        $this->addFlash('success', "Producto retirado del pedido.");
        return $this->redirectToRoute('pedido_list');
    }

    /**
     * @Route("/list/", name="pedido_list")
     */
    public function list(Request $request, CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository, PaginatorInterface $paginator): Response
    {
        $request->getSession()->remove('metpago');
        if ($this->getUser()->getPedidoConf() != null)
            return $this->redirectToRoute('pagar_paso3');
        $entityManager = $this->getDoctrine()->getManager();
        $pedAll = $entityManager->getRepository(Pedido::class)->findAllByCliente($this->getUser()->getId());
        $pagination = $paginator->paginate($pedAll, $request->query->getInt('page', 1), 10);
        return $this->render('pedido/list.html.twig', [
            'categorias' => $categoriaRepository->findAll(),
            'pedAll' => $pagination,
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("/pagarSaldo", name="pagar_saldo")
     */
    public function pagarConSaldo(Request $request)
    {
        try {
            $session = $request->getSession();
            $session->set('metpago', 'saldo');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }
        return $this->redirectToRoute('pagar_paso1');
    }

    /**
     * @Route("/pagarCash", name="pagar_cash")
     */
    public function pagarCash(Request $request)
    {
        try {
            $session = $request->getSession();
            $session->set('metpago', 'cash');

        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }
        return $this->redirectToRoute('pagar_paso1');
    }

    /**
     * @Route("/pagarPaypal", name="pagar_paypal")
     */
    public function pagarPaypal(Request $request)
    {
        try {
            $session = $request->getSession();
            $session->set('metpago', 'paypal');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }
        return $this->redirectToRoute('pagar_paso1');
    }

    /**
     * @Route("/pago/p1", name="pagar_paso1")
     */
    public function pagarP1(Request $request, Telegram $telegram, ConfiguracionRepository $configuracionRepository, CategoriaRepository $categoriaRepository, UserRepository $userRepository, PaginatorInterface $paginator)
    {
        try {
            if ($this->getUser()->getPedidoConf() != null)
                return $this->redirectToRoute('pagar_paso3');
            $entityManager = $this->getDoctrine()->getManager();
            $pedAll = $entityManager->getRepository(Pedido::class)->findAllByCliente($this->getUser()->getId());
            $pagination = $paginator->paginate($pedAll, $request->query->getInt('page', 1), 10);
            $form = $this->createFormBuilder()
                ->add('transporte', EntityType::class, [
                    'class' => Transporte::class,
                    'label' => 'Municipio'
                ])
                ->add('entrega', ChoiceType::class, [
                    'choices' => [
                        'Mi dirección.' => 0,
                        'La dirección de un familiar o amigo.' => 1
                    ],
                    'label' => "¿Donde se hará la entrega?",
                ])
                ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($form->get('entrega')->getData() == 1) {
                    $trans = array(
                        'municipio' => $form->get('transporte')->getData()->getMunicipio(),
                        'tarifa' => $form->get('transporte')->getData()->getTarifa()
                    );
                    $request->getSession()->set('trans', $trans);
                    return $this->redirectToRoute('pagar_paso2');
                } else {
                    if ($this->getUser()->getDir() == null)
                        throw new \Exception('Debes registrar tu dirección en tu perfil para continuar con el pedido.');
                    if ($this->getUser()->getTelf() == null)
                        throw new \Exception('Debes registrar un número de teléfono en tu perfil para continuar con el pedido.');

                    $carrito = $request->getSession()->get('carrito');
                    $pedido = new Pedido();
                    for ($i = 0; $i < count($carrito); $i++) {
                        $producto = $entityManager->getRepository(Producto::class)->findOneById($carrito[$i]['id']);
                        $pedido->addProductoPedido($producto, $carrito[$i]['cantidad']);
                        $producto->descontar($carrito[$i]['cantidad']);
                    }
                    $pedido->setTransporte(new TransportePedido($form->get('transporte')->getData()->getMunicipio(), $form->get('transporte')->getData()->getTarifa()));
                    $pedido->setCliente($this->getUser());
                    $metpago = $request->getSession()->get('metpago');
                    if ($metpago == 'cash') {
                        $pedido->setEstado('pendiente');
                        $pedido->setMetpago('cash');
                        $this->addFlash('success', "Pedido creado correctamente.");
                        $entityManager->persist($pedido);
                        $entityManager->flush();
                        $request->getSession()->remove('carrito');
                        if ($pedido->getCliente()->getIdTelegram()) {
                            $mensaje = "Hola " . $pedido->getCliente()->getNombre() . " tu pedido id:" . $pedido->getId() . " ya está pendiente, espera que sea aceptado por uno de nuestros trabajadores.";
                            $telegram->notifTelegramUsuario($pedido->getCliente(), $mensaje);
                        }
                        $telegram->notifTelegramGrupo($userRepository->findAllTrab(), "Hay un nuevo pedido por el valor de: " . $pedido->getTotal() . ' a pagar: ' . $pedido->getMetpago());
                        return $this->redirectToRoute('pedido_list', ['pedido' => $pedido]);
                    } else if ($metpago == 'saldo') {
                        $pedido->setEstado('pendiente');
                        $pedido->setMetpago('saldo');
                        $pedido->getCliente()->descontar($pedido->getTotal() + $pedido->getTransporte()->getTarifa());
                        $this->addFlash('success', "Pedido creado correctamente.");
                        $entityManager->persist($pedido);
                        $entityManager->flush();
                        $request->getSession()->remove('carrito');
                        if ($pedido->getCliente()->getIdTelegram()) {
                            $mensaje = "Hola " . $pedido->getCliente()->getNombre() . " tu pedido id:" . $pedido->getId() . " ya está pendiente, espera que sea aceptado por uno de nuestros trabajadores.";
                            $telegram->notifTelegramUsuario($pedido->getCliente(), $mensaje);
                        }
                        $telegram->notifTelegramGrupo($userRepository->findAllTrab(), "Hay un nuevo pedido por el valor de: " . $pedido->getTotal() . ' a pagar: ' . $pedido->getMetpago());
                        return $this->redirectToRoute('pedido_list', ['pedido' => $pedido]);
                    } else {
                        $pedido->setEstado('confección');
                        $pedido->setMetpago('paypal');
                        $entityManager->persist($pedido);
                        $entityManager->flush();
                        $request->getSession()->remove('carrito');
                        return $this->redirectToRoute('pagar_paso3', ['pedido' => $pedido]);
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }
        return $this->render('pedido/pago/paso1.html.twig', [
            'categorias' => $categoriaRepository->findAll(),
            'pedAll' => $pagination,
            'formentrega' => $form->createView(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("/pago/p2", name="pagar_paso2")
     */
    public function pagarP2(Request $request, Telegram $telegram, ConfiguracionRepository $configuracionRepository, UserRepository $userRepository, CategoriaRepository $categoriaRepository, PaginatorInterface $paginator)
    {
        try {
            if ($this->getUser()->getPedidoConf() != null)
                return $this->redirectToRoute('pagar_paso3');
            $entityManager = $this->getDoctrine()->getManager();
            $pedAll = $entityManager->getRepository(Pedido::class)->findAllByCliente($this->getUser()->getId());
            $pagination = $paginator->paginate($pedAll, $request->query->getInt('page', 1), 10);
            $form = $this->createForm(UbicacionPedidoType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $session = $request->getSession();
                $carrito = $session->get('carrito');
                $pedido = new Pedido();
                for ($i = 0; $i < count($carrito); $i++) {
                    $producto = $entityManager->getRepository(Producto::class)->findOneById($carrito[$i]['id']);
                    $pedido->addProductoPedido($producto, $carrito[$i]['cantidad']);
                    $producto->descontar($carrito[$i]['cantidad']);
                }
                $trans = $session->get('trans');
                $pedido->setTransporte(new TransportePedido($trans['municipio'], $trans['tarifa']));
                $pedido->setCliente($this->getUser());
                $pedido->setNombPer($form->get('nombre')->getData());
                $pedido->setCiPer($form->get('ci')->getData());
                $pedido->setTelPer($form->get('telefono')->getData());
                $pedido->setDirPer($form->get('direccion')->getData());
                $metpago = $request->getSession()->get('metpago');
                if ($metpago == 'cash') {
                    $pedido->setEstado('pendiente');
                    $pedido->setMetpago('cash');
                    $this->addFlash('success', "Pedido creado correctamente.");
                    $entityManager->persist($pedido);
                    $entityManager->flush();
                    $request->getSession()->remove('carrito');
                    if ($pedido->getCliente()->getIdTelegram()) {
                        $mensaje = "Hola " . $pedido->getCliente()->getNombre() . " tu pedido id:" . $pedido->getId() . " ya está pendiente, espera que sea aceptado por uno de nuestros trabajadores.";
                        $telegram->notifTelegramUsuario($pedido->getCliente(), $mensaje);
                    }
                    $telegram->notifTelegramGrupo($userRepository->findAllTrab(), "Hay un nuevo pedido por el valor de: " . $pedido->getTotal() . ' a pagar: ' . $pedido->getMetpago());
                    return $this->redirectToRoute('pedido_list', ['pedido' => $pedido]);
                } else if ($metpago == 'saldo') {
                    $pedido->setEstado('pendiente');
                    $pedido->setMetpago('saldo');
                    $pedido->getCliente()->descontar($pedido->getTotal() + $pedido->getTransporte()->getTarifa());
                    $this->addFlash('success', "Pedido creado correctamente.");
                    $entityManager->persist($pedido);
                    $entityManager->flush();
                    $request->getSession()->remove('carrito');
                    if ($pedido->getCliente()->getIdTelegram()) {
                        $mensaje = "Hola " . $pedido->getCliente()->getNombre() . " tu pedido id:" . $pedido->getId() . " ya está pendiente, espera que sea aceptado por uno de nuestros trabajadores.";
                        $telegram->notifTelegramUsuario($pedido->getCliente(), $mensaje);
                    }
                    $telegram->notifTelegramGrupo($userRepository->findAllTrab(), "Hay un nuevo pedido por el valor de: " . $pedido->getTotal() . ' a pagar: ' . $pedido->getMetpago());
                    return $this->redirectToRoute('pedido_list', ['pedido' => $pedido]);
                } else {
                    $pedido->setEstado('confección');
                    $pedido->setMetpago('paypal');
                    $entityManager->persist($pedido);
                    $entityManager->flush();
                    $request->getSession()->remove('carrito');
                    return $this->redirectToRoute('pagar_paso3', ['pedido' => $pedido]);
                }
            }

        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }
        return $this->render('pedido/pago/paso2.html.twig', [
            'categorias' => $categoriaRepository->findAll(),
            'pedAll' => $pagination,
            'formentrega' => $form->createView(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("/pago/p3", name="pagar_paso3")
     */
    public function pagarP3(Request $request, ConfiguracionRepository $configuracionRepository, CategoriaRepository $categoriaRepository, PaginatorInterface $paginator)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $pedAll = $entityManager->getRepository(Pedido::class)->findAllByCliente($this->getUser()->getId());
        $pagination = $paginator->paginate($pedAll, $request->query->getInt('page', 1), 10);
        return $this->render('pedido/pago/paso3.html.twig', [
            'categorias' => $categoriaRepository->findAll(),
            'pedAll' => $pagination,
            'pedido' => $this->getUser()->getPedidoConf(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("/pagarPayPal/checkot/{id}", name="confirmar_pago")
     */
    public function pagarPaypalP4(Pedido $pedido, UserRepository $userRepository, Telegram $telegram, $cuenta)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $pedido->setEstado('pendiente');
        $entityManager->persist($pedido);
        $entityManager->flush();
        $this->addFlash('success', "Pedido pagado correctamente.");
        if ($pedido->getCliente()->getIdTelegram()) {
            $mensaje = "Hola " . $pedido->getCliente()->getNombre() . " tu pedido id:" . $pedido->getId() . " ha sido pagado en paypal.";
            $telegram->notifTelegramUsuario($pedido->getCliente(), $mensaje);
        }
        $telegram->notifTelegramGrupo($userRepository->findAllTrab(), "Hay un nuevo pedido por el valor de: " . $pedido->getTotal() . ' a pagar: ' . $pedido->getMetpago());
        return $this->redirectToRoute('pedido_list');
    }

    /**
     * @Route("/cancelar/{id}", name="pedido_delete", methods={"GET"})
     */
    public function delete(Request $request, Pedido $pedido): Response
    {
        $cliente = $pedido->getCliente();
        $entityManager = $this->getDoctrine()->getManager();
        //AUMENTO EL ALMACEN
        foreach ($pedido->getProductosPedido() as $prod) {
            $prodAlmacen = $entityManager->getRepository(Producto::class)->findOneById($prod->getIdProducto());
            $prodAlmacen->setCantidad($prodAlmacen->getCantidad() + $prod->getCantidad());
        }
        //AUMENTO EL SALDO CLIENTE
        if ($pedido->getMetpago() == 'saldo') {
            $pedido->getCliente()->acreditar($pedido->getTotal() + $pedido->getTransporte()->getTarifa());
        }

        $log = new Log(new \DateTime('now'), 'PEDIDO', 'Pedido cancelado id: ' . $pedido->getId() . ' , valor: $' . $pedido->getTotal(), $cliente);
        $entityManager->persist($log);
        $entityManager->remove($pedido);
        $entityManager->flush();

        return $this->redirectToRoute('pedido_list', ['cli' => $cliente->getId()]);
    }

    /**
     * @Route("/trab/pendientes/list/{trab}", name="pedido_pendiente",  methods={"GET"})
     */
    public function pedidosPendiente(Request $request, Request $request2, CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository, PaginatorInterface $paginator, User $trab): Response
    {
        $em = $this->getDoctrine()->getManager();
        $pedidosPend = $em->getRepository(Pedido::class)->findAllPendientes();
        $pedidosAcep = $em->getRepository(Pedido::class)->findAllAceptados($trab->getId());
        $pagination = $paginator->paginate($pedidosPend, $request->query->get('pendientes', 1), 100, array('pageParameterName' => 'pendientes'));
        $pagination2 = $paginator->paginate($pedidosAcep, $request2->query->get('aceptados', 1), 100, array('pageParameterName' => 'aceptados'));
        return $this->render('pedido/pendientes.html.twig', [
            'categorias' => $categoriaRepository->findAll(),
            'pedPend' => $pagination,
            'pedAcep' => $pagination2,
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("/trab/pendientes/acept/{id}/{trab}", name="aceptar_pedido_pendiente",  methods={"GET"})
     */
    public function aceptarPedido(Pedido $pedido, Telegram $telegram, User $trab): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $pedido->setEstado('aceptado');
        $pedido->setTrabajador($trab);
        $entityManager->flush();
        $this->addFlash('success', "Pedido aceptado correctamente.");
        $log = new Log(new \DateTime('now'), 'PEDIDO', 'Pedido id: ' . $pedido->getId() . ' aceptado por ' . $trab->getNombre() . ' ' . $trab->getApellidos() . ', valor: $' . $pedido->getTotal(), $pedido->getCliente());
        $log2 = new Log(new \DateTime('now'), 'PEDIDO', 'Pedido id: ' . $pedido->getId() . ' de ' . $pedido->getCliente()->getNombre() . ' ' . $pedido->getCliente()->getApellidos() . ' aceptado, valor: $' . $pedido->getTotal(), $trab);

        if ($pedido->getCliente()->getIdTelegram()) {
            $mensaje = "Hola " . $pedido->getCliente()->getNombre() . " tu pedido id:" . $pedido->getId() . " ha sido aceptado por uno de nuestros trabajadores, no demoramos en llevarlo hasta ti.";
            $telegram->notifTelegramUsuario($pedido->getCliente(), $mensaje);
        }
        $entityManager->persist($log);
        $entityManager->persist($log2);
        $entityManager->flush();
        return $this->redirectToRoute('pedido_pendiente', ['trab' => $trab->getId()]);
    }

    /**
     * @Route("/trab/pendientes/finalizar/{id}/{trab}", name="finalizar_pedido_pendiente",  methods={"GET"})
     */
    public function finalizarPedido(Request $request, Pedido $pedido, User $trab, CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        return $this->render('pedido/finalizar.html.twig', [
            'categorias' => $categoriaRepository->findAll(),
            'pedido' => $pedido,
            'trabajador' => $trab,
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("/trab/pendientes/finalizar/eliminar/{pedido}/{trab}/{producto}", name="eliminar_prod_ped",  methods={"GET"})
     */
    public function eliminarProdPed(Pedido $pedido, User $trab, ProductoPedido $productoPedido): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        if ($pedido->getMetpago() == 'saldo')
            $pedido->getCliente()->acreditar($productoPedido->getPrecio());

        $pedido->elimProductoPedido($productoPedido);
        $producto = $entityManager->getRepository(Producto::class)->findOneById($productoPedido->getIdProducto());
        $producto->setCantidad($producto->getcantidad() + $productoPedido->getCantidad());
        $entityManager->flush();
        $this->addFlash('success', "Producto eliminado");
        return $this->redirectToRoute('finalizar_pedido_pendiente', ['id' => $pedido->getId(), 'trab' => $trab->getId()]);
    }

    /**
     * @Route("/trab/pendientes/finalizar/completado/{id}/{trab}", name="completar_pedido",  methods={"GET"})
     */
    public function finalizarPedidoOk(Pedido $pedido, User $trab): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        if ($pedido->getProductosPedido()->count() == 0) {
            $pedido->setEstado('cancelado');
            if ($pedido->getPagoViaje() > 0)
                $pedido->getCliente()->setSaldo($pedido->getCliente()->getSaldo() + $pedido->getPagoViaje());
        } else
            $pedido->setEstado('completado');
        $entityManager->persist($pedido);
        $log = new Log(new \DateTime('now'), 'PEDIDO', 'Pedido id: ' . $pedido->getId() . ' de ' . $pedido->getCliente()->getNombre() . ' ' . $pedido->getCliente()->getApellidos() . ' completado, valor: $' . $pedido->getTotal(), $trab);
        $entityManager->persist($log);
        $entityManager->flush();
        $this->addFlash('success', "Pedido completado correctamente.");
        return $this->redirectToRoute('pedido_pendiente', ['trab' => $trab->getId()]);
    }

    /**
     * @Route("/trab/pendientes/modificar/{id}/{trab}/{productoPedido}", name="modificar_pedido_pendiente")
     */
    public function modificarProdPed(Request $request, Pedido $pedido, User $trab, ProductoPedido $productoPedido, CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $formCant = $this->createForm(CantProdType::class);
        $formCant->get('cantidad')->setData($productoPedido->getCantidad());
        $formCant->handleRequest($request);

        if ($formCant->isSubmitted() && $formCant->isValid()) {
            $cant = $formCant->get('cantidad')->getData();
            $producto = $entityManager->getRepository(Producto::class)->findOneById($productoPedido->getIdProducto());
            if ($producto->getCantidad() < $cant) {
                $this->addFlash('error', "La cantidad sobrepasa la existente de este producto.");
            } else {
                //ACTUALIZO CANTIDADES PRECIO Y ALMACENES
                if ($productoPedido->getCantidad() > $cant) {
                    $diferencia = $productoPedido->getCantidad() - $cant;
                    $producto->setCantidad($producto->getCantidad() + $diferencia);
                    $pedido->setTotal($pedido->getTotal() - $productoPedido->getPrecio() * ($diferencia));
                    $productoPedido->setCantidad($cant);
                    $entityManager->flush();
                    $this->addFlash('success', "Producto guardado correctamente.");
                } elseif ($productoPedido->getCantidad() < $cant) {
                    $diferencia = $cant - $productoPedido->getCantidad();
                    $producto->setCantidad($producto->getCantidad() - $diferencia);
                    $pedido->setTotal($pedido->getTotal() + $productoPedido->getPrecio() * ($diferencia));
                    $productoPedido->setCantidad($cant);
                    $entityManager->flush();
                    $this->addFlash('success', "Producto guardado correctamente.");
                }
                return $this->redirectToRoute('finalizar_pedido_pendiente', ['id' => $pedido->getId(), 'trab' => $trab->getId()]);
            }
        }
        return $this->render('pedido/finalizar2.html.twig', [
            'categorias' => $categoriaRepository->findAll(),
            'pedido' => $pedido,
            'trabajador' => $trab,
            'prod' => $productoPedido,
            'form' => $formCant->createView(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

}
