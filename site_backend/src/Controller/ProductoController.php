<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Entity\Log;
use App\Entity\Negocio;
use App\Entity\Pedido;
use App\Entity\Producto;
use App\Entity\User;
use App\Form\CantProdType;
use App\Form\EntradaProdType;
use App\Form\ProductoType;
use App\ImageOptimizer;
use App\Repository\CategoriaRepository;
use App\Repository\ConfiguracionRepository;
use App\Repository\ProductoRepository;
use App\Repository\UserRepository;
use App\Telegram;
use Cassandra\Session;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

date_default_timezone_set("America/Havana");

class ProductoController extends AbstractController
{


    /**
     * @Route("/admin/prod/new/{id}", name="producto_new", methods={"GET","POST"})
     */
    public function new(UserRepository $userRepository, Telegram $telegram, CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository, ImageOptimizer $imageOptimizer, Request $request, Categoria $categoria): Response
    {
        $producto = new Producto();
        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->getDoctrine()->getManager();
                if ($imgFile = $form->get('imagen')->getData()) {
                    $fileName = $this->generateUniqueFileName() . '.' . $imgFile->guessExtension();
                    $micarpeta = 'uploads/productos/' . $categoria->getId();
                    if (!file_exists($micarpeta)) {
                        mkdir($micarpeta, 0777, true);
                    }
                    $imgFile->move(
                        $micarpeta,
                        $fileName
                    );
                    $imageOptimizer->resize($micarpeta . '/' . $fileName);
                    $producto->setImg($fileName);
                }
                $producto->setRegistro(new \DateTime('now'));
                $producto->setCategoria($categoria);
                $entityManager->persist($producto);
                $mensaje = "Producto registrado por ". $this->getUser() ." : " . $producto->getNombre() ." categoria: ".$producto->getCategoria(). " cantiadad: ". $producto->getCantidad();
                $log = new Log(new \DateTime('now'), 'PRODUCTO', $mensaje, $this->getUser());
                $entityManager->persist($log);                
                $telegram->notifTelegramGrupo($userRepository->findAllTrab(), $mensaje);  
                $entityManager->flush();
                $this->addFlash('success', 'Producto registado correctamente');
            } catch (FileException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
            return $this->redirectToRoute('categoria_edit', ['id' => $categoria->getId()]);
        }

        return $this->render('producto/new.html.twig', [
            'producto' => $producto,
            'categorium' => $categoria,
            'form' => $form->createView(),
            'categorias' => $categoriaRepository->findAll(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }


    /**
     * @Route("/prod/{id}", name="producto_show", methods={"GET","POST"})
     */
    public function show(Producto $producto, CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository, Request $request): Response
    {
        return $this->render('producto/show.html.twig', [
            'producto' => $producto,
            'categorias' => $categoriaRepository->findAll(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("/admin/prod/{id}/edit", name="producto_edit", methods={"GET","POST"})
     */
    public function edit(Telegram $telegram, UserRepository $userRepository, Request $request, Producto $producto, ImageOptimizer $imageOptimizer, CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository): Response
    {
        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $this->getDoctrine()->getManager();
                if ($imgFile = $form->get('imagen')->getData()) {
                    $fileName = $this->generateUniqueFileName() . '.' . $imgFile->guessExtension();
                    $micarpeta = 'uploads/productos/' . $producto->getCategoria()->getId();
                    //Creo la carpeta de las img del departamento
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
                $producto->setRegistro(new \DateTime('now'));
                $entityManager->persist($producto);
                $mensaje = "Producto editado por ". $this->getUser() ." : " . $producto->getNombre() ." categoria: ".$producto->getCategoria();
                $log = new Log(new \DateTime('now'), 'PRODUCTO', $mensaje, $this->getUser());
                $entityManager->persist($log);                
                $telegram->notifTelegramGrupo($userRepository->findAllTrab(), $mensaje);  
                $entityManager->flush();
                $this->addFlash('success', 'Producto registado correctamente');
            } catch (FileException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }

            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('categoria_edit', ['id' => $producto->getCategoria()->getId()]);
        }

        return $this->render('producto/edit.html.twig', [
            'producto' => $producto,
            'form' => $form->createView(),
            'categorias' => $categoriaRepository->findAll(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("/trab/prod/{id}/entrada", name="producto_entrada", methods={"GET","POST"})
     */
    public function entrada(Telegram $telegram, UserRepository $userRepository, Request $request, Producto $producto, CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository): Response
    {
        $form = $this->createForm(EntradaProdType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $costro_entrada = $form->get('costo')->getData();
                $cantidad_entrada = $form->get('entrada')->getData();
                if($cantidad_entrada < 0 || $costro_entrada < 0){
                    new Exception("No se pudo completar la entrada.");
                }
                if($producto->getCosto() != $costro_entrada){
                    //Media de los costos
                    $cantTotal = $producto->getCantidad() + $cantidad_entrada;
                    $media = (($producto->getCosto() * $producto->getCantidad()) + ($costro_entrada * $cantidad_entrada)) / $cantTotal;
                    $producto->setCosto($media);
                }  
                $cant_anterior = $producto->getCantidad();
                $entityManager = $this->getDoctrine()->getManager();                
                $producto->setCantidad($producto->getCantidad() + $form->get('entrada')->getData());
                $producto->setCantidadCuadre($producto->getCantidad());
                $entityManager->persist($producto);                
                $mensaje = "Entrada de producto por ". $this->getUser() ." : " . $producto->getNombre()." negocio: ". $producto->getNegocio() ." cantidad + entrada: ".$cant_anterior ." + ".$form->get('entrada')->getData();
                $log = new Log(new \DateTime('now'), 'PRODUCTO', $mensaje, $this->getUser());
                $entityManager->persist($log);                
                $telegram->notifTelegramGrupo($userRepository->findAllTrab(), $mensaje);  
                $entityManager->flush();
                $this->addFlash('success', 'Entrada de producto correctamente');
            } catch (Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
            }

            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('negocio_show', ['id' => $producto->getNegocio()->getId()]);
        }

        return $this->render('producto/entrada.html.twig', [
            'producto' => $producto,
            'form' => $form->createView(),
            'categorias' => $categoriaRepository->findAll(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("/trab/prod/{id}/edit_neg", name="producto_edit_neg", methods={"GET","POST"})
     */
    public function edit_neg(Request $request, Producto $producto, Telegram $telegram, UserRepository $userRepository, ImageOptimizer $imageOptimizer, CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository): Response
    {
        if (!$this->getUser()->permisoNegocio($producto->getNegocio()) && $this->getUser()->getRolPadre() != "ROLE_ADMIN") {
            $this->addFlash('error', "No tiene permisos para entrar a ese negocio.");
            return $this->redirectToRoute('negocio_index');
        }
        else{
            $form = $this->createForm(ProductoType::class, $producto);
            $precio_c_antes = $producto->getCosto();
            $precio_v_antes = $producto->getPrecio();
            $cantidad_antes = $producto->getCantidad();
            $form->handleRequest($request); 
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $entityManager = $this->getDoctrine()->getManager();
                    if ($imgFile = $form->get('imagen')->getData()) {
                        $fileName = $this->generateUniqueFileName() . '.' . $imgFile->guessExtension();
                        $micarpeta = 'uploads/productos/neg_' . $producto->getNegocio()->getId();
                        //Creo la carpeta de las img del departamento
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
                    $producto->setRegistro(new \DateTime('now'));
                    $producto->setCantidadCuadre($producto->getCantidad());
                                      
                    $mensaje = "Producto editado por ". $this->getUser() ." : " 
                    . $producto->getNombre()
                    ." negocio: ".$producto->getNegocio() 
                    ." ANTES: costo: ". $precio_c_antes
                    ." venta: ". $precio_v_antes
                    ." cantidad: ". $cantidad_antes
                    ." DESPUES: costo: ".$producto->getCosto()
                    ." venta: ".$producto->getPrecio()
                    ." cantidad: ".$producto->getCantidad();
                    $log = new Log(new \DateTime('now'), 'PRODUCTO NEGOCIO', $mensaje, $this->getUser());
                    $entityManager->persist($producto);  
                    $entityManager->persist($log);
                    $entityManager->flush();                  
                    $telegram->notifTelegramGrupo($userRepository->findAllTrab(), $mensaje);  
                    $this->addFlash('success', 'Producto registado correctamente');
                } catch (FileException $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }

                $this->getDoctrine()->getManager()->flush();
                return $this->redirectToRoute('negocio_show', ['id' => $producto->getNegocio()->getId()]);
            }

            return $this->render('producto/edit_neg.html.twig', [
                'producto' => $producto,
                'form' => $form->createView(),
                'categorias' => $categoriaRepository->findAll(),
                'config' => $configuracionRepository->findOneById(1),
            ]);
        }        
    }

    /**
     * @Route("/admin/prod/{id}/delete", name="producto_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Producto $producto, Telegram $telegram, UserRepository $userRepository): Response
    {
        $idCat = $producto->getCategoria()->getId();
        if ($this->isCsrfTokenValid('delete' . $producto->getId(), $request->request->get('_token'))) {
            $mensaje = "Producto eliminado por ". $this->getUser() ." : " . $producto->getNombre() ." categoria: ".$producto->getCategoria();
            $telegram->notifTelegramGrupo($userRepository->findAllTrab(), $mensaje);
            $this->addFlash('success', "Producto eliminado correctamente");
            $log = new Log(new \DateTime('now'), 'PRODUCTO', $mensaje, $this->getUser());            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($log);
            $entityManager->remove($producto);
            $entityManager->flush();
        }
        return $this->redirectToRoute('categoria_edit', ['id' => $idCat]);

    }

    /**
     * @Route("/trab/prodneg/{id}/delete", name="producto_delete2", methods={"DELETE"})
     */
    public function delete2(Request $request, Producto $producto, Telegram $telegram, UserRepository $userRepository): Response
    {
        $idNeg = $producto->getNegocio()->getId();
        if ($this->isCsrfTokenValid('delete' . $producto->getId(), $request->request->get('_token'))) {
            $mensaje = "Producto eliminado por ". $this->getUser() ." : " . $producto->getNombre() ." negocio: ".$producto->getNegocio();
            $telegram->notifTelegramGrupo($userRepository->findAllTrab(), $mensaje);
            $log = new Log(new \DateTime('now'), 'PRODUCTO', $mensaje, $this->getUser());            
            $this->addFlash('success', "Producto eliminado correctamente");
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($log);
            $entityManager->remove($producto);
            $entityManager->flush();
        }
        return $this->redirectToRoute('negocio_show', ['id' => $idNeg]);
    }

    /**
     * @Route("/search", methods={"GET"}, name="buscar_producto")
     */
    public function search(Request $request, ProductoRepository $productos, CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$request->isXmlHttpRequest()) {
            return $this->render('producto/buscar.html.twig', ['categorias' => $categoriaRepository->findAll(), 'config' => $configuracionRepository->findOneById(1)]);
        }

        $query = $request->query->get('q', '');
        $limit = $request->query->get('l', 10);
        $foundProd = $productos->findBySearchQuery($query);

        $results = [];
        foreach ($foundProd as $prod) {
            $results[] = [
                'nombre' => htmlspecialchars($prod->getNombre(), ENT_COMPAT | ENT_HTML5),
                'precio' => htmlspecialchars(number_format($prod->getPrecio(), 2, '.', ','), ENT_COMPAT | ENT_HTML5),
                'cambiocup' => htmlspecialchars(number_format($configuracionRepository->findOneById(1)->getCambiocup() * $prod->getPrecio(), 0, '.', ','), ENT_COMPAT | ENT_HTML5),
                'imagen' => "/uploads/productos/" . $prod->getCategoria()->getId() . "/" . $prod->getImg(),
                'urldesc' => $this->generateUrl('producto_show', ['id' => $prod->getId()]),
                'urlcomp' => $this->generateUrl('pedido_new', ['prod' => $prod->getId(), 'cant' => 1])
            ];
        }
        return $this->json($results);
    }

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}
