<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

date_default_timezone_set("America/Havana");

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('pass')->getData()
                )
            );
            $user->setRegistro(new \DateTime('now'));
            $user->setSaldo(0);
            $user->setDeuda(0);
            $user->setEstado('activado');
            $user->setIsVerified(true);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            //try {
            //    $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            //        (new TemplatedEmail())
            //            ->from(new Address('soporte@vc-entrega.com', 'Soporte VC Entrega'))
            //            ->to($user->getCorreo())
            //            ->subject('Activa tu cuenta VC entrega')
            //           ->htmlTemplate('user/registration/confirmation_email.html.twig')
            //    );
            //} catch (\Exception $exception) {
            //    $this->addFlash('error', $exception->getMessage());
            //}
            // do anything else you need here, like send an email
            $this->addFlash('success', 'Cuenta creada correctamente');
            //$request->getSession()->set('verificaemail', $user->getCorreo());
            return $this->redirectToRoute('app_login');
        }
        return $this->render('user/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            //$this->addFlash('verify_email_error', $exception->getReason());
            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $request->getSession()->remove('verificaemail');
        $request->getSession()->set('verificado', 1);
        return $this->redirectToRoute('app_login');
    }
}
