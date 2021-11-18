<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Entity\Configuracion;
use App\Entity\Pedido;
use App\Entity\Producto;
use App\Entity\Transporte;
use App\Form\CategoriaType;
use App\Form\OpcionesType;
use App\Form\ProductoType;
use App\Form\TransporteType;
use App\ImageOptimizer;
use App\Repository\CategoriaRepository;
use App\Repository\ConfiguracionRepository;
use App\Repository\PedidoRepository;
use App\Repository\ProductoPedidoRepository;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

date_default_timezone_set("America/Havana");

class CategoriaController extends AbstractController
{
    /**
     * @Route("/admin/departamento", name="categoria_index", methods={"GET","POST"})
     */
    public function index(CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository, Request $request): Response
    {
        $categorium = new Categoria();
        $form = $this->createForm(CategoriaType::class, $categorium);
        $form->handleRequest($request);

        $entityManager = $this->getDoctrine()->getManager();

        $hoy = new \DateTime('today');
        $pedMes = $entityManager->getRepository(Pedido::class)->findPedMes($hoy->format('Y-m'));


        $pedHoy = $entityManager->getRepository(Pedido::class)->findPedDia($hoy->format("Y-m-d"));
        $glun = 0;
        $gmar = 0;
        $gmie = 0;
        $gjue = 0;
        $gvie = 0;
        $gsab = 0;
        $gdom = 0;
        switch ($hoy->format('N')) {
            case 7:
                $lunes = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 6 days"));
                $martes = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 5 days"));
                $miercoles = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 4 days"));
                $jueves = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 3 days"));
                $viernes = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 2 days"));
                $sabado = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 1 days"));
                $glun = $this->calcularGanancia($lunes);
                $gmar = $this->calcularGanancia($martes);
                $gmie = $this->calcularGanancia($miercoles);
                $gjue = $this->calcularGanancia($jueves);
                $gvie = $this->calcularGanancia($viernes);
                $gsab = $this->calcularGanancia($sabado);
                $gdom = $this->calcularGanancia($hoy->format("Y-m-d"));
                break;
            case 6:
                $lunes = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 5 days"));
                $martes = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 4 days"));
                $miercoles = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 3 days"));
                $jueves = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 2 days"));
                $viernes = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 1 days"));
                $glun = $this->calcularGanancia($lunes);
                $gmar = $this->calcularGanancia($martes);
                $gmie = $this->calcularGanancia($miercoles);
                $gjue = $this->calcularGanancia($jueves);
                $gvie = $this->calcularGanancia($viernes);
                $gsab = $this->calcularGanancia($hoy->format("Y-m-d"));
                break;
            case 5:
                $lunes = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 4 days"));
                $martes = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 3 days"));
                $miercoles = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 2 days"));
                $jueves = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 1 days"));
                $glun = $this->calcularGanancia($lunes);
                $gmar = $this->calcularGanancia($martes);
                $gmie = $this->calcularGanancia($miercoles);
                $gjue = $this->calcularGanancia($jueves);
                $gvie = $this->calcularGanancia($hoy->format("Y-m-d"));
                break;
            case 4:
                $lunes = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 3 days"));
                $martes = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 2 days"));
                $miercoles = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 1 days"));
                $glun = $this->calcularGanancia($lunes);
                $gmar = $this->calcularGanancia($martes);
                $gmie = $this->calcularGanancia($miercoles);
                $gjue = $this->calcularGanancia($hoy->format("Y-m-d"));
                break;
            case 3:
                $lunes = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 2 days"));
                $martes = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 1 days"));
                $glun = $this->calcularGanancia($lunes);
                $gmar = $this->calcularGanancia($martes);
                $gmie = $this->calcularGanancia($hoy->format("Y-m-d"));
                break;
            case 2:
                $lunes = date("Y-m-d", strtotime($hoy->format("Y-m-d") . "- 1 days"));
                $glun = $this->calcularGanancia($lunes);
                $gmar = $this->calcularGanancia($hoy->format("Y-m-d"));
                break;
            default:
                $glun = $this->calcularGanancia($hoy->format("Y-m-d"));
                break;
        }

        $pedCompl = $entityManager->getRepository(Pedido::class)->findAllCompletados();

        $categorias = $categoriaRepository->findAll();
        $invTotal = 0;
        for ($i = 0; $i <= count($categorias) - 1; $i++) {
            $invTotal += $categorias[$i]->getTotalInvertido();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($categorium);
            $entityManager->flush();
            return $this->redirectToRoute('categoria_index');
        }

        //OPCIONES GENERALES
        $opciones = $this->getDoctrine()->getRepository(Configuracion::class)->findOneById(1);
        $formOpciones = $this->createForm(OpcionesType::class, $opciones);
        $formOpciones->handleRequest($request);
        if ($formOpciones->isSubmitted() && $formOpciones->isValid()) {
            $entityManager->persist($opciones);
            $entityManager->flush();
            $this->addFlash('success', "Opciones generales cambiadas correctamente.");
            return $this->redirectToRoute('categoria_index');
        }

        //TRANSPORTE
        $transporte = $this->getDoctrine()->getRepository(Transporte::class)->findAll();
        $transpNuevo = new Transporte();
        $formTrans = $this->createForm(TransporteType::class, $transpNuevo);
        $formTrans->handleRequest($request);
        if ($formTrans->isSubmitted() && $formTrans->isValid()) {
            $entityManager->persist($transpNuevo);
            $entityManager->flush();
            $this->addFlash('success', "Opciones generales cambiadas correctamente.");
            return $this->redirectToRoute('categoria_index');
        }
        return $this->render('categoria/index.html.twig', [
            'categorias' => $categoriaRepository->findAll(),
            'glun' => $glun,
            'gmar' => $gmar,
            'gmie' => $gmie,
            'gjue' => $gjue,
            'gvie' => $gvie,
            'gsab' => $gsab,
            'gdom' => $gdom,
            'pedCompl' => $pedCompl,
            'invTotal' => $invTotal,
            'ganMes' => $this->calcularGananciaMes(),
            'formCat' => $form->createView(),
            'config' => $configuracionRepository->findOneById(1),
            'transporte' => $transporte,
            'formTrans' => $formTrans->createView(),
            'formOpciones' => $formOpciones->createView()
        ]);
    }

    /**
     * @Route("/admin/cuadre", name="cuadre_productos", methods={"GET"})
     */
    public function cuadre(CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $productos = $entityManager->getRepository(Categoria::class)->findProdCuadre();
        $pagCategoria = $paginator->paginate($productos, $request->query->getInt('page', 1), 200);
        return $this->render('categoria/cuadre.html.twig', [
            'productos' => $pagCategoria,
            'categorias' => $categoriaRepository->findAll(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("/admin/pedidos/recientes", name="pedidos_recientes", methods={"GET"})
     */
    public function pedidos(CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $pedidos = $entityManager->getRepository(Pedido::class)->findPedidoRecientes();
        $pagpedidos = $paginator->paginate($pedidos, $request->query->getInt('page', 1), 25);
        return $this->render('pedido/recientes.html.twig', [
            'pedidos' => $pagpedidos,
            'categorias' => $categoriaRepository->findAll(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    public function calcularGanancia($dia): float
    {
        $entityManager = $this->getDoctrine()->getManager();
        $pedComp = $entityManager->getRepository(Pedido::class)->findPedDia($dia);
        $ganacncia = 0;
        for ($i = 0; $i < count($pedComp); $i++) {
            for ($j = 0; $j < count($pedComp[$i]->getProductosPedido()); $j++) {
                $ganacncia = $ganacncia + ($pedComp[$i]->getProductosPedido()[$j]->getPrecio() - $pedComp[$i]->getProductosPedido()[$j]->getCosto()) * $pedComp[$i]->getProductosPedido()[$j]->getCantidad();
            }
        }
        return $ganacncia;
    }

    public function calcularGananciaMes(): float
    {
        $hoy = new \DateTime('today');
        $entityManager = $this->getDoctrine()->getManager();
        $pedMes = $entityManager->getRepository(Pedido::class)->findPedMes($hoy->format('Y-m'));
        $ganacncia = 0;
        for ($i = 0; $i < count($pedMes); $i++) {
            for ($j = 0; $j < count($pedMes[$i]->getProductosPedido()); $j++) {
                $ganacncia = $ganacncia + ($pedMes[$i]->getProductosPedido()[$j]->getPrecio() - $pedMes[$i]->getProductosPedido()[$j]->getCosto()) * $pedMes[$i]->getProductosPedido()[$j]->getCantidad();
            }
        }
        return $ganacncia;
    }

    /**
     * @Route("/departamento/{id}", name="categoria_show", methods={"GET"})
     */
    public function show(CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository, Categoria $categorium, PaginatorInterface $paginator, Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $productos = $entityManager->getRepository(Categoria::class)->findProdDep($categorium->getId());
        $pagCategoria = $paginator->paginate($productos, $request->query->getInt('page', 1), 200);


        return $this->render('categoria/show.html.twig', [
            'categorium' => $categorium,
            'productos' => $pagCategoria,
            'categorias' => $categoriaRepository->findAll(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("/admin/departamento/{id}/edit", name="categoria_edit", methods={"GET","POST"})
     */
    public function edit(CategoriaRepository $categoriaRepository, ImageOptimizer $imageOptimizer, ConfiguracionRepository $configuracionRepository, Request $request, Categoria $categorium, PaginatorInterface $paginator): Response
    {
        $formCat = $this->createForm(CategoriaType::class, $categorium);
        $entityManager = $this->getDoctrine()->getManager();
        $prod = $entityManager->getRepository(Categoria::class)->findProdDep($categorium->getId());
        $pagProd = $paginator->paginate($prod, $request->query->getInt('page', 1), 50);
        $formCat->handleRequest($request);

        if ($formCat->isSubmitted() && $formCat->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('categoria_edit', ['id' => $categorium->getId()]);
        }

        $producto = new Producto();
        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->getDoctrine()->getManager();
                if ($imgFile = $form->get('imagen')->getData()) {
                    $fileName = $this->generateUniqueFileName() . '.' . $imgFile->guessExtension();
                    $micarpeta = 'uploads/productos/' . $categorium->getId();
                    if (!file_exists($micarpeta)) {
                        mkdir($micarpeta, 0777, true);
                    }
                    $imgFile->move(
                        $micarpeta,
                        $fileName
                    );
                    $producto->setImg($fileName);
                    $imageOptimizer->resize($micarpeta . '/' . $fileName);
                }
                $producto->setRegistro(new \DateTime('now'));
                $producto->setCategoria($categorium);
                $entityManager->persist($producto);
                $entityManager->flush();
                $this->addFlash('success', 'Producto registado correctamente');
            } catch (FileException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
            return $this->redirectToRoute('categoria_edit', ['id' => $categorium->getId()]);
        }

        return $this->render('categoria/edit.html.twig', [
            'productos' => $pagProd,
            'categorium' => $categorium,
            'formCat' => $formCat->createView(),
            'form' => $form->createView(),
            'categorias' => $categoriaRepository->findAll(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("/admin/departamento/{id}/delete", name="categoria_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Categoria $categorium): Response
    {
        if ($this->isCsrfTokenValid('delete' . $categorium->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($categorium);
            $entityManager->flush();
        }

        return $this->redirectToRoute('categoria_index');
    }

    /**
     * @Route("/admin/transp/{id}/delete", name="transp_delete",methods={"GET","POST"})
     */
    public function deleteTransp(Request $request, Transporte $transporte): Response
    {
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($transporte);
            $entityManager->flush();
            $this->addFlash('success', 'Tarifa de transporte eliminada');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }
        return $this->redirectToRoute('categoria_index');
    }

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}
