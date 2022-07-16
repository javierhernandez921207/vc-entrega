<?php

namespace App\Controller;
use App\Telegram;
use App\Entity\Cuadre;
use App\Entity\Log;
use App\Entity\Negocio;
use App\Entity\Producto;
use App\Entity\User;
use App\Form\CantProdActualType;
use App\Form\DineroCajaType;
use App\Form\CantProdMoverType;
use App\Form\NegocioType;
use App\Form\ProductoType;
use App\ImageOptimizer;
use App\Repository\CategoriaRepository;
use App\Repository\ConfiguracionRepository;
use App\Repository\NegocioRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \Doctrine\ORM\EntityRepository;

date_default_timezone_set("America/Havana");
/**
 * Class NegocioController
 * @package App\Controller
 */
class NegocioController extends AbstractController
{
    private $prod;

    /**
     * @Route ("/trab/negocios", name="negocio_index")
     */
    public function index(CategoriaRepository $categoriaRepository, NegocioRepository $negocioRepository, ConfiguracionRepository $configuracionRepository, Request $request)
    {
        $newNegocio = new Negocio();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(NegocioType::class, $newNegocio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($newNegocio);
            $log = new Log(new \DateTime('now'), 'CREAR NEGOCIO', 'Creó negocio ' . $newNegocio->getNombre(), $this->getUser());
            $em->persist($log);
            $em->flush();
            return $this->redirectToRoute('negocio_index');
        }

        return $this->render('negocio/index.html.twig', [
            'controller_name' => 'NegocioController',
            'negocios' => $negocioRepository->findAll(),
            'formn' => $form->createView(),
            'categorias' => $categoriaRepository->findAll(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route ("/trab/negocio/{id}", name="negocio_show")
     */
    public function show(UserRepository $userRepository,Telegram $telegram, CategoriaRepository $categoriaRepository, ImageOptimizer $imageOptimizer, NegocioRepository $negocioRepository, ConfiguracionRepository $configuracionRepository, Negocio $negocio, Request $request, PaginatorInterface $paginator)
    {
        if (!$this->getUser()->permisoNegocio($negocio) && $this->getUser()->getRolPadre() != "ROLE_ADMIN") {
            $this->addFlash('error', "No tiene permisos para entrar a ese negocio.");
            return $this->redirectToRoute('negocio_index');
        } else {
            $em = $this->getDoctrine()->getManager();
            $form = $this->createForm(NegocioType::class, $negocio);
            $form->handleRequest($request);
            $prod = $em->getRepository(Producto::class)->findByNegocio($negocio);
            $cuadre = $em->getRepository(Cuadre::class)->findByNegocioFecha($negocio);
            $pagProd = $paginator->paginate($prod, $request->query->getInt('productos', 1), 50, array('pageParameterName' => 'productos'));
            $pagCuadre = $paginator->paginate($cuadre, $request->query->getInt('cuadres', 1), 30, array('pageParameterName' => 'cuadres'));

            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($negocio);
                $log = new Log(new \DateTime('now'), 'NEGOCIO', 'Modificó negocio' . $negocio->getNombre(), $this->getUser());
                $em->persist($log);
                $em->flush();
                return $this->redirectToRoute('negocio_show', ['id' => $negocio->getId()]);
            }

            $producto = new Producto();
            $formProd = $this->createForm(ProductoType::class, $producto);
            $formProd->handleRequest($request);

            if ($formProd->isSubmitted() && $formProd->isValid()) {
                try {
                    if ($imgFile = $formProd->get('imagen')->getData()) {
                        $fileName = $this->generateUniqueFileName() . '.' . $imgFile->guessExtension();
                        $micarpeta = 'uploads/productos/neg_' . $negocio->getId();
                        if (!file_exists($micarpeta)) {
                            mkdir($micarpeta, 0777, true);
                        }
                        $imgFile->move(
                            $micarpeta,
                            $fileName
                        );
                        $imageOptimizer->resize($micarpeta . '/' . $fileName);
                        //Borro la imagen anterior.
                        if ($producto->getImg()) {
                            chdir($micarpeta);
                            unlink($producto->getImg());
                        }
                        $producto->setImg($fileName);
                    }
                    $producto->setCantidadCuadre($producto->getCantidad());
                    $producto->setRegistro(new \DateTime('now'));
                    $producto->setNegocio($negocio);
                    $em->persist($producto);
                    $mensaje = "Producto registrado por ". $this->getUser() ." : " . $producto->getNombre() ." negocio: ".$producto->getNegocio(). " cantiadad: ". $producto->getCantidad();
                    $log = new Log(new \DateTime('now'), 'PRODUCTO NEGOCIO', $mensaje, $this->getUser());                    
                    $em->persist($log);
                    $em->flush();
                    $telegram->notifTelegramGrupo($userRepository->findAllAdmin(), $mensaje);  
                    $this->addFlash('success', 'Producto registado correctamente');
                } catch (FileException $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
                return $this->redirectToRoute('negocio_show', ['id' => $negocio->getId()]);
            }

            $formProdEdit = $this->createForm(ProductoType::class, $this->prod);
            $formProdEdit->handleRequest($request);

            $formUserAdd = $this->createFormBuilder()
                ->add('nuevo_trabajador', EntityType::class, [
                    'class' => User::class,
                    'query_builder' => function (UserRepository $er) use ($negocio) {
                        return $er->createQueryBuilder('x')
                            ->where('x.roles LIKE ?1')
                            ->orWhere('x.roles LIKE ?2')
                            ->orderBy('x.nombre', 'Asc')
                            ->setParameter(1,'%TRAB%')
                            ->setParameter(2,'%ADMIN%');
                    },])
                ->getForm();
            $formUserAdd->handleRequest($request);
            if ($formUserAdd->isSubmitted() && $formUserAdd->isValid()) {
                if (!$negocio->getTrabajadores()->contains($formUserAdd->get('nuevo_trabajador')->getData())) {
                    $negocio->addTrabajadore($formUserAdd->get('nuevo_trabajador')->getData());
                    $em->persist($negocio);
                    $log = new Log(new \DateTime('now'), 'NEGOCIO TRABAJADORES', 'Add trabajador ' . $formUserAdd->get('nuevo_trabajador')->getData() . ' negocio ' . $negocio->getNombre(), $this->getUser());
                    $em->persist($log);
                    $em->flush();
                    $this->addFlash('success', 'Tabajador registado correctamente');
                    $this->redirectToRoute('negocio_show', ['id' => $negocio->getId()]);
                } else
                    $this->addFlash('warning', 'Ya el tabajador está registado.');
            }

            return $this->render('negocio/show.html.twig', [
                    'negocio' => $negocio,
                    'formu' => $formUserAdd->createView(),
                    'formn' => $form->createView(),
                    'forme' => $formProdEdit->createView(),
                    'form' => $formProd->createView(),
                    'categorias' => $categoriaRepository->findAll(),
                    'productos' => $pagProd,
                    'cuadres' => $pagCuadre,
                    'config' => $configuracionRepository->findOneById(1)
                ]
            );
        }
    }

    /**
     * @Route ("/admin/negocio/{id_neg}/trab/{id_trab}/delete", name="quitarTrabNeg")
     */
    public function quitarTrabajadorNegocio($id_neg, $id_trab)
    {
        $em = $this->getDoctrine()->getManager();
        $negocio = $em->getRepository(Negocio::class)->findOneById($id_neg);
        $user = $em->getRepository(User::class)->findOneById($id_trab);
        $negocio->removeTrabajadore($user);
        $em->persist($negocio);
        $log = new Log(new \DateTime('now'), 'NEGOCIO TRABAJADORES', 'Eliminar trabajador ' . $user . ' negocio ' . $negocio->getNombre(), $this->getUser());
        $em->persist($log);
        $em->flush();
        return $this->redirectToRoute('negocio_show', ['id' => $negocio->getId()]);
    }

    /**
     * @Route ("/trab/negocio/{id}/editarCantidad/", name="editCantCuadre")
     */
    public function editarCantidadCuadre(ConfiguracionRepository $configuracionRepository, CategoriaRepository $categoriaRepository, Request $request, Producto $producto)
    {
        $em = $this->getDoctrine()->getManager();
        $formCantUnidAct = $this->createForm(CantProdActualType::class, $producto);        
        $formCantUnidAct->handleRequest($request);

        if ($formCantUnidAct->isSubmitted() && $formCantUnidAct->isValid()) {
            if ($formCantUnidAct->get('cantidad_cuadre')->getData() > $producto->getCantidad()) {
                $this->addFlash('error', "La cantidad de " . $producto->getNombre() . " durante el cuadre no puede ser mayor que " . $producto->getCantidad());
            } 
            elseif($formCantUnidAct->get('cantidad_cuadre')->getData()<0) {
                $this->addFlash('error', "La cantidad de " . $producto->getNombre() . " durante el cuadre no puede ser menor que 0.");
            }
            else{
                $producto->setCantidadCuadre($formCantUnidAct->get('cantidad_cuadre')->getData());
                $em->persist($producto);
                $em->flush();
                return $this->redirectToRoute('negocio_show', ['id' => $producto->getNegocio()->getId()]);
            }
        }
        return $this->render(
            'negocio/editCuadre.html.twig',
            [
                'producto' => $producto,
                'negocio' => $producto->getNegocio(),
                'formCuadre' => $formCantUnidAct->createView(),
                'categorias' => $categoriaRepository->findAll(),
                'config' => $configuracionRepository->findOneById(1),
            ]
        );
    }

    /**
     * @Route ("/trab/negocio/{id}/moverProducto/", name="moverProducto")
     */
    public function moverProductoNegocio(UserRepository $userRepository,Telegram $telegram, ConfiguracionRepository $configuracionRepository, CategoriaRepository $categoriaRepository, Request $request, Producto $producto)
    {
        $em = $this->getDoctrine()->getManager();
        $formMoverProd = $this->createForm(CantProdMoverType::class);        
        $formMoverProd->handleRequest($request);

        if ($formMoverProd->isSubmitted() && $formMoverProd->isValid()) {
            if ($formMoverProd->get('cantidad_mover')->getData() > $producto->getCantidad()) {
                $this->addFlash('error', "La cantidad a mover de " . $producto->getNombre() . " no puede ser mayor que " . $producto->getCantidad());
            } 
            elseif($formMoverProd->get('cantidad_mover')->getData()< 1) {
                $this->addFlash('error', "La cantidad de " . $producto->getNombre() . " a mover debe ser mayor que 0.");
            }
            elseif($formMoverProd->get('negocio')->getData() == $producto->getNegocio() ){
                $this->addFlash('error', "Elige otro negocio para mover este producto.");
            }
            else{
                $existe = false;
                foreach ($formMoverProd->get('negocio')->getData()->getProductos() as $prod) {
                    if($prod->getNombre()== $producto->getNombre()){
                        $prod->setCantidad($prod->getCantidad()+$formMoverProd->get('cantidad_mover')->getData());
                        $prod->setPrecio($producto->getPrecio());
                        $prod->setCosto($producto->getCosto());
                        $prod->setCantidadCuadre($prod->getCantidad());
                        $em->persist($prod);
                        $existe = true;
                    }
                }
                $neg = $producto->getNegocio();
                if(!$existe){
                    $prod = new Producto();
                    $prod->setNombre($producto->getNombre());
                    $prod->setRegistro(new \DateTime('now'));
                    $prod->setDescr($producto->getDescr());
                    $prod->setImg($producto->getImg());
                    $prod->setPrecio($producto->getPrecio());
                    $prod->setCosto($producto->getCosto());
                    $prod->setCantMin($producto->getCantMin());
                    $prod->setNegocio($formMoverProd->get('negocio')->getData());
                    $prod->setCantidad($formMoverProd->get('cantidad_mover')->getData());
                    $prod->setCantidadCuadre($formMoverProd->get('cantidad_mover')->getData());
                    $em->persist($prod);                    
                }
                $producto->setCantidad($producto->getCantidad()- $formMoverProd->get('cantidad_mover')->getData());
                $producto->setCantidadCuadre($producto->getCantidad());
                $log = new Log(new \DateTime('now'), 'PRODUCTO NEGOCIO', "Mover producto ".$producto->getNombre() ." origen: ".$neg." cantidad: ".$formMoverProd->get('cantidad_mover')->getData()." destino: " .$formMoverProd->get('negocio')->getData(), $this->getUser());
                $em->persist($log);
                $em->persist($producto);
                $em->flush();
                $mensaje = "Producto movido por ". $this->getUser() ." : " . $producto->getNombre() ." origen: ".$neg." cantidad: ".$formMoverProd->get('cantidad_mover')->getData()." destino: " .$formMoverProd->get('negocio')->getData();
                $telegram->notifTelegramGrupo($userRepository->findAllAdmin(), $mensaje);        
                $this->addFlash('success', "Producto movido correctamente.");
                return $this->redirectToRoute('negocio_show', ['id' => $producto->getNegocio()->getId()]);
            }
        }
                   
        return $this->render(
            'negocio/moverProd.html.twig',
            [
                'producto' => $producto,
                'negocio' => $producto->getNegocio(),
                'formMover' => $formMoverProd->createView(),
                'categorias' => $categoriaRepository->findAll(),
                'config' => $configuracionRepository->findOneById(1),
            ]
        );
    }


    /**
     * @Route("/admin/negocio/{id}/delete", name="negocio_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Negocio $negocio): Response
    {
        if ($this->isCsrfTokenValid('delete' . $negocio->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $log = new Log(new \DateTime('now'), 'NEGOCIO', 'Eliminó negocio ' . $negocio->getNombre() , $this->getUser());
            $entityManager->persist($log);
            $entityManager->remove($negocio);
            $entityManager->flush();
        }
        return $this->redirectToRoute('negocio_index');
    }

    /**
     * @Route("/trab/negocio/{id}/cuadre", name="negocio_cuadre")
     */
    public function cuadre(ConfiguracionRepository $configuracionRepository, CategoriaRepository $categoriaRepository, Request $request, Negocio $negocio): Response
    {        
        $em = $this->getDoctrine()->getManager();
        $cuadre = new Cuadre();
        $form = $this->createFormBuilder()
            ->add('fondo', MoneyType::class, ['label' => "cantidad a dejar en caja", 'currency' => 'USD'])
            ->add('trabajador_entrante', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $er) use ($negocio) {
                    return $er->createQueryBuilder('x')
                        ->innerJoin('x.negocios', 'n')
                        ->where('n.id = :neg')
                        ->setParameter('neg', $negocio);
                },])
            ->add('trabajador_saliente', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $er) use ($negocio) {
                    return $er->createQueryBuilder('x')
                        ->innerJoin('x.negocios', 'n')
                        ->where('n.id = :neg')
                        ->setParameter('neg', $negocio);
                },])
            ->getForm();

        $form->handleRequest($request);
        $totalRecaudado = 0;
        $totalGanancia = 0;

        foreach ($negocio->getProductos() as $producto) {
           
                $vendidos = $producto->getCantidad() - $producto->getCantidadCuadre();
                $totalRecaudado += $vendidos * $producto->getPrecio();
                $totalGanancia += ($producto->getPrecio() - $producto->getCosto()) * $vendidos;
            
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($negocio->getTrabajadores()->count() == 0) {
                $this->addFlash('error', "Para completar el cuadre debe tener trabajadores en el negocio");                
            } else {
                $cuadre->setFondo($form->get('fondo')->getData());
                $cuadre->setTrabajadorEntrante($form->get('trabajador_entrante')->getData());
                $cuadre->setTrabajadorSaliente($form->get('trabajador_saliente')->getData());
                $cuadre->setFecha(new \DateTime('now'));
                $cuadre->setTotal($totalRecaudado);
                $cuadre->setGanacia($totalGanancia);
                $cuadre->setNegocio($negocio);
                $em->persist($cuadre);
                $em->flush();

                $templateProcessor = new TemplateProcessor('plantillas/plantilla.docx');
                $templateProcessor->cloneRow('producto', count($negocio->getProductos()));

                $templateProcessor->setValue('negocio', htmlspecialchars($negocio->getNombre()));
                $templateProcessor->setValue('id', htmlspecialchars($cuadre->getId()));
                $templateProcessor->setValue('fecha', htmlspecialchars($cuadre->getFecha()->format("d/m/Y g:ia")));
                $templateProcessor->setValue('trabajadors', htmlspecialchars($cuadre->getTrabajadorSaliente()));
                $templateProcessor->setValue('trabajadore', htmlspecialchars($cuadre->getTrabajadorEntrante()));
                $templateProcessor->setValue('total', htmlspecialchars($cuadre->getTotal()));
                $templateProcessor->setValue('ganancia', htmlspecialchars($cuadre->getGanacia()));
                $templateProcessor->setValue('caja', htmlspecialchars($cuadre->getFondo()));

                for ($i = 0; $i < count($negocio->getProductos()); $i++) {
                    $templateProcessor->setValue('producto#' . ($i + 1), htmlspecialchars($negocio->getProductos()[$i]->getNombre()));
                    $templateProcessor->setValue('precio#' . ($i + 1), htmlspecialchars($negocio->getProductos()[$i]->getPrecio()));
                    if ($negocio->getProductos()[$i]->getCantidadCuadre() < $negocio->getProductos()[$i]->getCantidad()) {
                        $templateProcessor->setValue('unidades#' . ($i + 1), htmlspecialchars($negocio->getProductos()[$i]->getCantidadCuadre()));
                        $vendidas = $negocio->getProductos()[$i]->getCantidad() - $negocio->getProductos()[$i]->getCantidadCuadre();
                        $templateProcessor->setValue('vendidas#' . ($i + 1), htmlspecialchars($vendidas));
                        $negocio->getProductos()[$i]->setCantidad($negocio->getProductos()[$i]->getCantidadCuadre());
                        $negocio->getProductos()[$i]->setCantidadCuadre($negocio->getProductos()[$i]->getCantidadCuadre());
                        $em->persist($negocio->getProductos()[$i]);
                    } else {
                        $templateProcessor->setValue('unidades#' . ($i + 1), htmlspecialchars($negocio->getProductos()[$i]->getCantidad()));
                        $templateProcessor->setValue('vendidas#' . ($i + 1), htmlspecialchars(0));
                    }
                }

                $dir = 'cuadres/' . $negocio->getId();
                //Creo la carpeta de los cuadres del negocio
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $ruta = $dir . '/cuadre_' . $cuadre->getId() . '.docx';
                $templateProcessor->saveAs(utf8_decode($ruta));

                $log = new Log(new \DateTime('now'), 'NEGOCIO', 'Creó cuadre negocio ' . $negocio->getNombre(), $this->getUser());
                $em->persist($log);

                $em->flush();
                $this->addFlash('success', "Cuadre creado correctamente.");
                return $this->redirectToRoute('negocio_show', ['id' => $negocio->getId()]);
            }
        }        
        return $this->render('negocio/cuadre.html.twig', [
                'totalRecaudado' => $totalRecaudado,
                'negocio' => $negocio,
                'formCaja' => $form->createView(),
                'categorias' => $categoriaRepository->findAll(),
                'config' => $configuracionRepository->findOneById(1),
            ]
        );
    }

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }


}
