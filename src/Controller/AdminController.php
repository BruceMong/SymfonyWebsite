<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use App\Form\UserType;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Form\EventDataType;
use App\Form\DataHtmlType;
use App\Entity\Article;
use App\Entity\User;
use App\Entity\DataHTML;
use App\Entity\Category;
use App\Repository\UserRepository;
use App\Repository\DataHTMLRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Migrations\Provider\EmptySchemaProvider;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use App\Form\MailType;

class AdminController extends AbstractController
{
    /**

     * @Route("/admin", name="panel_admin")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function panel()
    {
        return $this->render('admin/panel.html.twig', []);
    }

    /**
     * Permet de voir les utilisateurs dans la bdd
     *
     * @Route("/admin/viewusers_mails", name="admin_viewUsers_mail")
     * 
     * 
     * @return Response
     */
    public function getMailsAllUser(Request $request, UserRepository $repo, ObjectManager $manager)
    {
        $allUser = $repo->findAll();

        return $this->render('admin/viewEmails.html.twig', [
            'users' => $allUser
        ]);
    }
    /**
     * Permet de voir les utilisateurs dans la bdd
     *
     * @Route("/admin/viewusers", name="admin_viewUsers")
     * 
     * 
     * @return Response
     */
    public function viewUsers(Request $request, UserRepository $repo, ObjectManager $manager)
    {
        $allUser = $repo->findAll();

        return $this->render('admin/viewUsers.html.twig', [
            'users' => $allUser
        ]);
    }
    /**

     * @Route("/admin/profilUser/delete/{id}", name="admin_deleteAccount")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function deleteUser(User $user, UserRepository $repo, Request $request, ObjectManager $manager)
    {

        if (!$user) {
            throw $this->createNotFoundException('No guest found');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'Utilisateur supprimé avec succès');

        return $this->redirectToRoute('admin_viewUsers');
    }



    /**
     * Permet de voir/modifier/supprimer le profil d'un utilisateur
     * @Route("/admin/profilview/{id}", name="admin_profilDetail")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function profil_view(User $user, Request $request, UserRepository $repo, ObjectManager $manager): Response
    {
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

        return $this->render('admin/view_profil.html.twig', [
            'commandesNext' => $commandesNext,
            'commandesOld' => $commandesOld,
            'user' => $user
        ]);
    }

    /**
     * Permet modifier profil utilisateur autre
     * @Route("/admin/profilmodify/{id}", name="admin_profil_modify")
     * @IsGranted("ROLE_ADMIN")
     */
    public function profilModifyAdmin(User $user, Request $request, ObjectManager $manager): Response
    {
        if ($this->getUser() == null)
            return $this->redirectToRoute('security_connexion');

        $form = $this->createform(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Profil mise à jour avec succès !');
            return $this->redirectToRoute('admin_viewUsers');
        }


        return $this->render('main/profil_modify.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * Permet de créer une annonce
     *
     * @Route("/services/create", name="services_create")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function createArticle(Request $request, ObjectManager $manager)
    {
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($article);
            $manager->flush();
            $this->addFlash(
                'success',
                "Article crée avec succès"
            );

            return $this->redirectToRoute('services_showById', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('admin/createArticle.html.twig', [
            'form' => $form->createView()
        ]);
    }




    /**
     * Permet d'afficher le formulaire d'édition
     *
     * @Route("/services/{id}/edit", name="services_edit")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function editArticle(Article $article, Request $request, ObjectManager $manager)
    {

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($article);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les modifications ont bien été enregistrées !"
            );

            return $this->redirectToRoute('services_showById', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('services/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article
        ]);
    }

    /**

     * @Route("/admin/article/delete/{id}", name="admin_deleteArticle")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function deleteArticle(Article $article)
    {

        if (!$article) {
            throw $this->createNotFoundException('Aucun article trouvé');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        $this->addFlash('success', 'Article supprimé avec succès');

        return $this->redirectToRoute('panel_admin');
    }



    /**
     * Permet d'afficher le formulaire d'édition
     *
     * @Route("/services/{id}/edit_cat", name="services_edit_cat")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function editCat(Category $category, Request $request, ObjectManager $manager)
    {

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($category);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les modifications ont bien été enregistrées !"
            );

            return $this->redirectToRoute('services_collection', [
                'id' => $category->getId()
            ]);
        }

        return $this->render('services/editCat.html.twig', [
            'form' => $form->createView(),
            'category' => $category
        ]);
    }


    /**

     * @Route("/admin/show/eventdata", name="event_data")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function showEventData(DataHTMLRepository $repo)
    {
        $eventData =  $repo->findOneBy(['id' => "14"]);
        return $this->render('admin/showEventData.html.twig', [
            'eventData' => $eventData
        ]);
    }



    /**

     * @Route("/admin/change_eventdata", name="event_dataChange")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function ChangeEventData(DataHTMLRepository $repo, ObjectManager $manager)
    {
        $eventData =  $repo->findOneBy(['id' => "14"]);

        if ($eventData->getName() == "ON") {
            $eventData->setName("OFF");
            $this->addFlash('success', 'Bannière d\'évenement désactivé !');
        } else {
            $eventData->setName("ON");
            $this->addFlash('success', 'Bannière d\'évenement activé !');
        }
        $manager->persist($eventData);
        $manager->flush();


        return $this->render('admin/showEventData.html.twig', [
            'eventData' => $eventData
        ]);
    }


    /**

     * @Route("/admin/eventdata/modify", name="modify_event")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function modifyEventData(DataHTMLRepository $repo, ObjectManager $manager, Request $request)
    {
        $data =  $repo->findOneBy(['id' => "14"]);
        $form = $this->createform(EventDataType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($data);
            $manager->flush();
            $this->addFlash('success', 'Texte mise à jour avec succès !');
        }

        return $this->render('admin/modifyEvent.html.twig', [
            'form' => $form->createView(),
            'data' => $data
        ]);
    }


    /**

     * @Route("/admin/data", name="show_data")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function showData(DataHTMLRepository $repo)
    {
        $dataAll = $repo->findAll();
        return $this->render('admin/showData.html.twig', [
            'dataAll' => $dataAll
        ]);
    }
    /**

     * @Route("/admin/data/home", name="show_data_home")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function showDataHome(DataHTMLRepository $repo)
    {
        $dataAll = $repo->findAll();
        return $this->render('admin/showDataHome.html.twig', [
            'dataAll' => $dataAll
        ]);
    }

    /**

     * @Route("/admin/data/mentions", name="show_data_mentions")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function showDataMentions(DataHTMLRepository $repo)
    {
        $dataAll = $repo->findAll();
        return $this->render('admin/showDataMention.html.twig', [
            'dataAll' => $dataAll
        ]);
    }

    /**

     * @Route("/admin/data/contact", name="show_data_contact")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function showDataContact(DataHTMLRepository $repo, MailerInterface $mailer, Request $request)
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
                ->to('brucemongthe13@gmail.com') //mettre par la suite l'adresse de sindy
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
        return $this->render('admin/showDataContact.html.twig', [
            'dataAll' => $dataAll,
            'formMail' => $formMail->createView()
        ]);
    }
    /**

     * @Route("/admin/data/{id}", name="modify_data")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @return Response
     */
    public function modifyData(DataHTML $data, ObjectManager $manager, Request $request)
    {
        $form = $this->createform(DataHtmlType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($data);
            $manager->flush();
            $this->addFlash('success', 'Texte mise à jour avec succès !');
        }

        return $this->render('admin/modifyData.html.twig', [
            'form' => $form->createView(),
            'data' => $data
        ]);
    }
}
