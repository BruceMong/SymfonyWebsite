<?php

namespace App\Controller;

use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\DataHTMLRepository;
use App\Form\ArticleType;
use App\Entity\Article;

use App\Entity\Commande;
use App\Form\PasswordChangeType;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Form\MailType;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;


class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     *  @Route("/", name="homepage")
     * 
     */
    public function home(DataHTMLRepository $repo, Request $request): Response
    {
        $dataAll = $repo->findAll();
        return $this->render('main/home.html.twig', [
            'dataAll' => $dataAll
        ]);
    }

    /**
     *  @Route("/infositeweb", name="infositeweb")
     * 
     */
    public function infodev(): Response
    {
        return $this->render('main/infositeweb.html.twig', []);
    }


    /**
     * @Route("/search", name="search")
     */
    public function searchBar(Request $request, CategoryRepository $repoCat, ArticleRepository $repoArt, $data = null): Response
    {
        $data = strip_tags($request->query->get('data'));

        if (!$data)
            return $this->redirectToRoute('home');

        //On check si article existe sinon on cherche dans les cats
        $artSearch =  $repoArt->findBySearch($data);
        if ($artSearch) {
            $allCategory = $repoCat->findAll();
            return $this->render('services/collectionViaSearch.html.twig', [
                'articles' => $artSearch,
                'allCategory' => $allCategory,
            ]);
        }
        $catSearch = $repoCat->findBySearch($data);
        if ($catSearch) {
            return $this->forward('App\Controller\ServicesController::showAll', [
                'CatBySearch'  => $catSearch[0],
            ]);
        }
        $allCategory = $repoCat->findAll();
        return $this->render('services/collectionViaSearch.html.twig', [
            'articles' => null,
            'allCategory' => $allCategory,
        ]);
    }
    /**
     * @Route("/profil", name="user_account")
     */
    public function profil(): Response
    {
        $user = $this->getUser();
        if ($user == null)
            return $this->redirectToRoute('security_connexion');

        $commandes = $user->getCommandes(); //historique des commandes

        //faire un filter sur la collection pour chopper rdv deja passé
        $today = new DateTime();
        $commandesOld = array();
        $commandesNext = array();

        foreach ($commandes->getValues() as $commande) {
            if ($commande->getDate() > $today) //Si la date de la commande est superieur à audj
                array_push($commandesNext, $commande);
            else
                array_push($commandesOld, $commande);
        }

        return $this->render('main/profil.html.twig', [
            'commandesNext' => $commandesNext,
            'commandesOld' => $commandesOld,
        ]);
    }

    /**
     * @Route("/profil/modify", name="profil_modify")
     */
    public function profilModify(Request $request, ObjectManager $manager): Response
    {
        if ($this->getUser() == null)
            return $this->redirectToRoute('security_connexion');

        $user = $this->getUser();
        $form = $this->createform(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Profil mise à jour avec succès !');
            return $this->redirectToRoute('user_account');
        }


        return $this->render('main/profil_modify.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**

     * @Route("/profil/change_password", name="change_password")
     * 
     * @return Response
     */
    public function changePassword(Request $request, UserRepository $repo, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {

        $user = $repo->findOneBy(['id' => ($this->getUser())->getId()]);
        if ($user == null)
            return $this->redirectToRoute('security_connexion');

        $form = $this->createform(PasswordChangeType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->getPassword();
            $oldPassword = $encoder->isPasswordValid($user, $form->get('oldPassword')->getData());
            if ($oldPassword == true) {
                $user->setPassword($encoder->encodePassword($user, $form->get('newPassword')->getData()));
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', 'Profil mise à jour avec succès !');
                return $this->redirectToRoute('user_account');
            } else {
                $this->addFlash('fail', 'L\'ancien mot de passe ne correspond pas avec celui que nous avons.');
            }
        }
        return $this->render('main/modifyPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/listeSoins", name="show_WishList")
     */
    public function showWishList(): Response
    {
        $user = $this->getUser();
        if ($user == null)
            return $this->redirectToRoute('security_connexion');

        $articlesInWish = ($user->getWishList())->getValues();

        return $this->render('main/show_WishList.html.twig', [
            'articlesInWish' => $articlesInWish
        ]);
    }

    /**
     * @Route("/listeSoins/delete", name="delete_AllWishList")
     */
    public function deleteAllWishList(Request $request, $from = null, ObjectManager $manager): Response
    {

        $user = $this->getUser();
        if ($user == null)
            return $this->redirectToRoute('security_connexion');

        $wishList = $user->getWishList();
        if (!$wishList->IsEmpty()) {
            $wishList->clear();
            $manager->persist($user);
            $manager->flush();
        }
        $this->addFlash(
            'success',
            "Tout les soins ont étaient supprimés de votre liste."
        );
        if ($from != null) {
            return $this->redirectToRoute('home');
        }

        return $this->redirectToRoute('show_WishList');
    }

    /**
     * @Route("/listeSoins/delete/{id}", name="delete_WishList")
     */
    public function deleteWishList(Article $article, ObjectManager $manager): Response
    {
        $user = $this->getUser();
        if ($user == null)
            return $this->redirectToRoute('security_connexion');

        $wishList = $user->getWishList();
        if ($wishList->contains($article)) {
            $wishList->removeElement($article);
            $manager->persist($user);
            $manager->flush();
        }
        $this->addFlash(
            'success',
            "Le soin à était supprimer de votre liste !"
        );
        return $this->redirectToRoute('show_WishList');
    }

    //https://www.service-public.fr/professionnels-entreprises/vosdroits/F31228
    /**
     * @Route("/mention_legal", name="mention_legal")
     */
    public function legal(DataHTMLRepository $repo): Response
    {
        $dataAll = $repo->findAll();
        return $this->render('main/mention_legal.html.twig', [
            'dataAll' => $dataAll
        ]);
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request,  DataHTMLRepository $repo, MailerInterface $mailer): Response
    {


        $dataAll = $repo->findAll();
        $formMail = $this->createform(MailType::class);
        $formMail->handleRequest($request);

        if ($formMail->isSubmitted() && $formMail->isValid()) {
            $data = $formMail->getData();
            $name = $data['name'];
            $addmail = $data['email'];
            $msg = $data['msg'];
            $email = (new TemplatedEmail())
                ->from(new Address('sindybeautebot@gmail.com', 'SindyBeaute Mail Bot'))
                ->to('sindybeaute@gmail.com') //mettre par la suite l'adresse de sindy
                ->subject('Mail de ' . $name . '(' . $addmail . ')')
                ->htmlTemplate('main/emailContact.html.twig')
                ->context([
                    'nom' => $name,
                    'addmail' => $addmail,
                    'msg' => $msg
                ]);

            $mailer->send($email);
            $this->addFlash('success', 'Votre mail à bien était envoyé');
        }



        return $this->render('main/contact.html.twig', [
            'formMail' => $formMail->createView(),
            'dataAll' => $dataAll
        ]);
    }

    /**
     * @Route("/profil/delete/{id}", name="delete_commande")
     */
    public function deleteCommande(Commande $commande): Response
    {
        $user = $this->getUser();
        if ($user == null)
            return $this->redirectToRoute('security_connexion');
        $today = new DateTime();
        if ($commande->getDate() > $today) {
            $this->addFlash('fail', 'Vous ne pouvez pas supprimé un rendez-vous pas encore passé, veuillez annulée par téléphone si il faut');
            return $this->redirectToRoute('user_account');
        }
        if (!$commande) {
            throw $this->createNotFoundException('No commande found');
            return $this->redirectToRoute('user_account');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($commande);
        $em->flush();
        $this->addFlash('success', 'Historique supprimé avec succès');
        return $this->redirectToRoute('user_account');
    }
}
