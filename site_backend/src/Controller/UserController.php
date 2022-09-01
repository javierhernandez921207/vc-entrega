<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Pedido;
use App\Entity\User;
use App\Form\PagarDeudaFormType;
use App\Form\RecargarFormType;
use App\Form\ResetPasswordType;
use App\Form\RolFormType;
use App\Form\UserFormType;
use App\Repository\CategoriaRepository;
use App\Repository\ConfiguracionRepository;
use App\Telegram;
use http\Env\Response;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Transport;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\AdminRecipient;

date_default_timezone_set("America/Havana");
class UserController extends AbstractController
{
    /**
     * @Route("admin/user/list", name="user_list")
     */
    public function list(Request $request, PaginatorInterface $paginator, CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $usuarios = $em->getRepository(User::class)->findAllClient();
        $usuariosAdmin = $em->getRepository(User::class)->findAllTrab();
        $pagination = $paginator->paginate($usuarios, $request->query->getInt('page', 1), 50);
        $pagination2 = $paginator->paginate($usuariosAdmin, $request->query->getInt('pageWork', 1), 50);

        return $this->render('user/list.html.twig', [
            'controller_name' => 'UserController', 'usuarios' => $pagination, 'usuariosAdmin' => $pagination2,
            'categorias' => $categoriaRepository->findAll(),
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("/user/{id}", name="user_show")
     */
    public function show(Request $request, User $user, Telegram $telegram, PaginatorInterface $paginator, NotifierInterface $notifier, CategoriaRepository $categoriaRepository, ConfiguracionRepository $configuracionRepository, UserPasswordEncoderInterface $encoder)
    {
        $em = $this->getDoctrine()->getManager();
        if ($user->getId() != $this->getUser()->getId() and $this->getUser()->getRolPadre() != 'ROLE_ADMIN')
            return $this->redirectToRoute('user_show', ['id' => $this->getUser()->getId()]);

        //CREO LOS FORM
        $formRec = $this->createForm(RecargarFormType::class);
        $formRol = $this->createForm(RolFormType::class);
        $formDatosPer = $this->createForm(UserFormType::class, $user);
        $formPagarDeuda = $this->createForm(PagarDeudaFormType::class);
        $formReset = $this->createForm(ResetPasswordType::class);

        $formRol->get('Rol')->setData($user->getRolPadre());
        $formRol->handleRequest($request);
        $formRec->handleRequest($request);
        $formDatosPer->handleRequest($request);
        $formPagarDeuda->handleRequest($request);
        $formReset->handleRequest($request);
        //VERIFICO SI ALGUNO FE LANZADO

        if ($formRol->isSubmitted() && $formRol->isValid()) {
            $user->setRoles([$formRol->get('Rol')->getData()]);
            $em->persist($user);
            $rol = "Cliente";
            if ($formRol->get('Rol')->getData() == "ROLE_TRAB") {
                $rol = "Trabajador";
            } elseif ($formRol->get('Rol')->getData() == "ROLE_ADMIN") {
                $rol = "Administrador";
            }
            $log = new Log(new \DateTime('now'), 'PERMISOS', "Cambio de " . $user->getUserName() . " a rol : " . $rol, $user);
            $em->persist($log);
            if ($user->getId() != $this->getUser()->getId()) {
                $log2 = new Log(new \DateTime('now'), 'PERMISOS', "Cambio de " . $user->getUserName() . " a rol : " . $rol, $this->getUser());
                $em->persist($log2);
            }
            $this->addFlash('success', "Usuario " . $user->getUserName() . " cambiado a " . $rol . " correctamente.");
            $em->flush();
            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        if ($formRec->isSubmitted() && $formRec->isValid()) {
            $user->setSaldo($user->getSaldo() + $formRec->get('Money')->getData());
            if ($formRec->get('deuda')->getData()) {
                $user->setDeuda($user->getDeuda() + $formRec->get('Money')->getData());
            }
            $em->persist($user);
            $log = new Log(new \DateTime('now'), 'RECARGA', "Saldo aumentado en $ " . $formRec->get('Money')->getData() . ". Total $ " . $user->getSaldo(), $user);
            $em->persist($log);
            if ($user->getId() != $this->getUser()->getId()) {
                $log2 = new Log(new \DateTime('now'), 'RECARGA', "Saldo aumentado en $ " . $formRec->get('Money')->getData() . ". Total $ " . $user->getSaldo(), $this->getUser());
                $em->persist($log2);
            }
            $this->addFlash('success', "Usuario " . $user->getUserName() . " recargado correctamente con $" . $formRec->get('Money')->getData() . ". Saldo actual $ " . $user->getSaldo());
            if ($user->getIdTelegram()) {
                $message = "Hola tienes una recarga de $ " . $formRec->get('Money')->getData() . ". Saldo actual $ " . $user->getSaldo() . ". Deuda actual $" . $user->getDeuda();
                $telegram->notifTelegramUsuario($user, $message);
            }
            $em->flush();
            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        if ($formReset->isSubmitted() && $formReset->isValid()) {
            $user->setPassword($encoder->encodePassword($user, $formReset->get('newPassword')->getData()));
            $this->addFlash('success', "Contraseña cambiada.");
            $log = new Log(new \DateTime('now'), 'PERMISOS', "Cambio de contraseña de acceso.", $user);
            $em->persist($log);
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        if ($formPagarDeuda->isSubmitted() && $formPagarDeuda->isValid()) {
            if ($user->getDeuda() > $formPagarDeuda->get('Money')->getData()) {
                $diff = $user->getDeuda() - $formPagarDeuda->get('Money')->getData();
                $user->setDeuda($diff);
                $log = new Log(new \DateTime('now'), 'DEUDA', "Deuda pagada. Restante $" . $diff, $user);
                $em->persist($log);
                if ($user->getId() != $this->getUser()->getId()) {
                    $log2 = new Log(new \DateTime('now'), 'DEUDA', "Deuda pagada de " . $user->getNombre() . " " . $user->getApellidos() . " Restante $" . $diff, $this->getUser());
                    $em->persist($log2);
                }
                $this->addFlash('success', 'Deuda pagada. Restante por pagar $' . $diff);
                if ($user->getIdTelegram()) {
                    $message = "Deuda pagada. Restante por pagar $" . $diff;
                    $telegram->notifTelegramUsuario($user, $message);
                }
            } elseif ($user->getDeuda() == $formPagarDeuda->get('Money')->getData()) {
                $user->setDeuda(0);
                $this->addFlash('success', 'Deuda pagada totalmente.');
                $log = new Log(new \DateTime('now'), 'DEUDA', "Deuda pagada totalmente.", $user);
                $em->persist($log);
                if ($user->getId() != $this->getUser()->getId()) {
                    $log2 = new Log(new \DateTime('now'), 'DEUDA', "Deuda pagada totalmente de " . $user->getNombre() . " " . $user->getApellidos(), $this->getUser());
                    $em->persist($log2);
                }
                if ($user->getIdTelegram()) {
                    $message = "Deuda pagada totalmente.";
                    $telegram->notifTelegramUsuario($user, $message);
                }
            } else {
                $this->addFlash('error', 'El pago excede la deuda.');
            }
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        if ($formDatosPer->isSubmitted() && $formDatosPer->isValid()) {
            $user->setNombre($formDatosPer->get('nombre')->getData());
            $user->setApellidos($formDatosPer->get('apellidos')->getData());
            $user->setCorreo($formDatosPer->get('correo')->getData());
            $user->setDir($formDatosPer->get('dir')->getData());
            $user->setTelf($formDatosPer->get('telf')->getData());
            $user->setIdTelegram($formDatosPer->get('idTelegram')->getData());
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', "Datos personales guardados correctamente.");
            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        $logs = $this->getDoctrine()->getRepository(Log::class)->findByUsuario($user);
        //$pagination = $paginator->paginate($logs, $request->query->getInt('log', 1), 5000, array('pageParameterName' => 'log'));
        $pedAll = $em->getRepository(Pedido::class)->findAllByCliente($user->getId());
        //$pagination2 = $paginator->paginate($pedAll, $request->query->getInt('pedidos', 1), 5000, array('pageParameterName' => 'pedidos'));
        return $this->render('user/perfil.html.twig', [
            'usuario' => $user,
            'logs' => $logs,
            'formRol' => $formRol->createView(),
            'formRec' => $formRec->createView(),
            'formDatPer' => $formDatosPer->createView(),
            'formPagarDeuda' => $formPagarDeuda->createView(),
            'formResetPass' => $formReset->createView(),
            'categorias' => $categoriaRepository->findAll(),
            'pedAll' => $pedAll,
            'config' => $configuracionRepository->findOneById(1),
        ]);
    }

    /**
     * @Route("admin/user/{id}/delete", name="user_delete")
     */
    public function delete(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('user_list');
        }
        $user = $em->getRepository(User::class)->findOneById($id);
        $log = new Log(new \DateTime('now'), 'ELIMINAR USUARIO', 'ID: ' . $user->getId() . ' ' . $user->getNombre() . ' ' . $user->getApellidos(), $this->getUser());

        $em->persist($log);
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', "Usuario eliminado correctamente.");
        return $this->redirectToRoute('user_list');
    }

}
