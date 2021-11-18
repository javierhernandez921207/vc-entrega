<?php

namespace App\Controller;

use App\Entity\Producto;
use App\Entity\User;
use App\Repository\CategoriaRepository;
use App\Repository\ConfiguracionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class AlmacenController extends AbstractController
{
    /**
     * @Route("/", options={"expose"=true}, name="index")
     */
    public function index(Request $request, MailerController $mailerController,MailerInterface $mailer, PaginatorInterface $paginator, CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $ultProductos = $em->getRepository(Producto::class)->findUltimosProd();
        $pagUltProd = $paginator->paginate($ultProductos, $request->query->getInt('page', 1), 20);


        return $this->render('almacen/index.html.twig', [
            'ultProductos' => $pagUltProd,
            'controller_name' => 'AlmacenController',
            'categorias' => $categoriaRepository->findAll(),
            'config' => $configuracionRepository->findOneById(1)
        ]);
    }
}
