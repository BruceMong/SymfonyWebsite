<?php

namespace App\Controller;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\RegistrationType;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SecurityController extends AbstractController
{

    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }


    /** 
     * @Route("/connexion", name="security_connexion")
     */
    public function connexion(AuthenticationUtils $utils, $error = null, $username = null, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();
        if ($this->getUser() != null) {
            return $this->redirectToRoute('home');
        }

        $user = new User();
        $formReg = $this->createform(RegistrationFormType::class, $user);
        $formReg->handleRequest($request);

        if ($formReg->isSubmitted() && $formReg->isValid()) {
            // encode the plain password

            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $formReg->get('password')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            dump($entityManager->flush());

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email_security',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('sindybeautebot@gmail.com', 'SindyBeauté Mail Bot'))
                    ->to($user->getEmail())
                    ->subject('Veuillez confirmer votre adresse mail')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email
            $this->addFlash('success', 'Un mail vous à été envoyé afin de validé votre compte');
            return $this->redirectToRoute('home');
        }

        if ($formReg->isSubmitted() && !($formReg->isValid()))  // pour le redirect sur le form reg en cas d'erreur de register
            $from = "reg";
        else
            $from = "log";

        return $this->render('security/connexion.html.twig', [
            'formReg' => $formReg->createView(),
            'hasError' => $error !== null,
            'username' => $username,
            'from' => $from
        ]);
    }



    /** 
     * @Route("/verify/email", name="app_verify_email_security")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('home');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre adresse mail a été vérifié');

        return $this->redirectToRoute('home');
    }


    /** 
     * @Route("/loginCheck", name="security_connexionCheck")
     */
    public function loginCheck(Request $request)
    {

        $remember = $request->request->get('rememeberMe');
        if ($remember != null) {
            $session = $request->getSession();
            $session->set('remember', $remember);
        }
        dump($request);
        exit;
    }

    /** 
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
    }
}
