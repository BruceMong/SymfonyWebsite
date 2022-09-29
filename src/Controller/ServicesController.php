<?php

namespace App\Controller;

//require __DIR__ . '/vendor/autoload.php';



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Entity\Article;
use Symfony\Component\Finder\Finder;
use App\Entity\Category;
use App\Entity\Commande;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use App\Repository\CategoryRepository;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Migrations\Provider\EmptySchemaProvider;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


use App\Service\ContainerParametersHelper;
use Google_Service_Calendar;


use DateTime;
use DateTimeZone;

use DateInterval;
use Google_Service_Calendar_Event;
use Symfony\Component\HttpFoundation\Session\Session;


class ServicesController extends AbstractController
{

    /** KernelInterface $appKernel */
    private $appKernel;

    public function __construct(KernelInterface $appKernel)
    {
        $this->appKernel = $appKernel;
    }




    /**
     * @Route("/services/collection/{id}", name="services_collection")
     * @Route("/services/collection", name="services_collection")
     * @Route("/services")
     * 
 
     */
    public function showAll(CategoryRepository $repo, Request $request, Category $CatBySearch = null): Response
    {
        $allCategory = $repo->findAll(); //pour le navbar cat
        if ($CatBySearch)
            $id_cat = $CatBySearch->getId();
        else
            $id_cat = $request->query->get('id');


        $fromAll = false;

        if ($id_cat != null) {
            $category = $repo->findBy(['id' => $id_cat]);
        } else {
            $category = $repo->findAll();
            $fromAll = true;
        }

        $allArticles = new ArrayCollection();

        foreach ($category as $cat) {
            $allArticles->add($cat->getArticles()->getValues());
        }
        return $this->render('services/collection.html.twig', [
            'allArticles' => $allArticles,
            'category' => $category,
            'allCategory' => $allCategory,
            'fromAll' => $fromAll
        ]);
    }

    /**
     * @Route("/services/{id}/wish_modify", name="services_wish_modify")
     */
    public function addToWishList(Article $article, ObjectManager $manager)
    {

        $user = $this->getUser();
        if ($user == null)
            return $this->redirectToRoute('security_connexion');

        $wishList = $user->getWishList();
        if (!($wishList->contains($article))) {
            $wishList->add($article);
            $manager->persist($user); //ptetre user
            $manager->flush();
            $this->addFlash(
                'success',
                "Le soin à était enregister dans votre liste !"
            );
        } else {
            $wishList->removeElement($article);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash(
                'success',
                "Le soin à était supprimer de votre liste !"
            );
        }
        return $this->redirectToRoute('services_showById', [
            'id' => $article->getId()
        ]);
    }


    /**
     * @Route("/services/reservation", name="services_booking_post")
     */
    public function reserverBypost(Request $request, ArticleRepository $repoArticle, RequestStack $requestStack, ContainerParametersHelper $pathHelpers, MailerInterface $mailer, ObjectManager $manager)
    {
        $session = $request->getSession();
        //$dateFromGet = new DateTime($request->query->get('datePage'));
        $dateFromGet = DateTime::createFromFormat('d/m/Y:H:i:s', $request->query->get('datePage'));
        //dump($dateFromGet);
        //dump(new DateTime());
        //dump($request->query->get('datePage'));
        //exit;
        if ($request->query->get('sens') != null && $dateFromGet != null) {     //Si le mec à appuyé sur semaine prochaine ou semaine d'avant 
            return $this->forward('App\Controller\ServicesController::reserver', [
                'article'  => $session->get('article'),
                'prix' => $session->get('prix'),
                'prixAbo' => $session->get('prixAbo'),
                'duree' => $session->get('duree'),
                'sens' => $request->query->get('sens'),
                'date' => $dateFromGet
            ]);
        }

        //On vérifie toutes les données du rdv par rapport à la BDD pour pas qu'il y est d'entourloupe
        $date = new DateTime($request->request->get('date'), new DateTimeZone('Europe/Paris'));

        $debutHor = explode(":", $request->request->get('horaireStart'));
        $debutHor = (clone $date)->setTime($debutHor[0], $debutHor[1]);

        $finHor = explode(":", $request->request->get('horaireEnd'));
        $finHor = (clone $date)->setTime($finHor[0], $finHor[1]);

        $userPost = $request->request->get('user');
        $email = $request->request->get('email');
        $prix = $request->request->get('prix');
        $prixAbo  = $request->request->get('prixAbo');
        $duree = $request->request->get('duree');
        $articles = $request->request->get('articles');
        $telephone = $request->request->get('telephone');

        //verificationForRdv($date, $debutHor, $finHor,  $client, $email, $prix, $prixAbo, $duree, $articles);
        if ($date == null || $debutHor == null || $finHor == null ||  $userPost == null || $email == null || $prix == null || $prixAbo == null || $duree == null || $articles == null || $telephone == null || $articles == ":") {
            //Faire une erreur par rapport aux manques informations
            throw new \Exception('Une erreur à était veuillez recommencer ou envoyer mail si le problème persiste.');
        }

        require_once($pathHelpers->getApplicationRootDir() . '/insertEventAPI.php');
        $client = getClient($pathHelpers);
        $service = new Google_Service_Calendar($client);
        $calendarId = 'primary';
        date_default_timezone_set('Europe/Paris');
        $optParams = array(
            'maxResults' => 100,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMax' => $finHor->format('c'),
            'timeMin' => $debutHor->format('c')
        );
        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();
        if ($events != null) {
            //faire une erreur par rapport au créneaux qui est prix je sais pas quoi
            throw new \Exception('Le créneau n\'est plus disponible, veuillez réessayer :(');
        }


        //vérification : prix, prixAbo duree par rapport au articles 
        $articles = explode(":", $articles);
        array_pop($articles); //on vire le ":" //franchement j'aurais pu le faire en js mais flemme
        $articleId = $articles; //on crée un ptit clone pour la commande plus tard
        $prixV = 0;
        $prixAboV = 0;
        $dureeV = 0;
        $strArticles = "";
        $commande = new Commande(); // On profite du foreach pour add aussi les articles dans la commande (historique)

        foreach ($articles as $article) {
            $article = $repoArticle->findOneBy(['id' => $article]);
            if ($article != null) {
                $prixV += $article->getPrix();
                $dureeV += $article->getDuree();
                if ($article->getPrixAbo() != null) //faut voir ça
                    $prixAboV += $article->getPrixAbo();

                $strArticles = $strArticles . '<br/>   - ' . $article->getNom() . '<br/>' . '(Prix: ' . $prixV . '€, Prix Abo: ' . $prixAboV . '€, Duree: ' . $dureeV . 'min)';
                $commande->addArticle($article);
            } else {
                //faire erreur car id article inexistant 
            }
        }
        $articles = $strArticles;
        //dump($articles);

        if ($prix != $prixV ||  $prixAbo != $prixAboV || $duree != $dureeV) {
            throw new \Exception('Les erreurs des articles sont incorrectes, veuillez réessayer :(');
        }

        //verif User : nom prenom + email
        $user = $this->getUser();
        // dump($user->getNom() . ' ' . $user->getPrenom() . '!=' . $userPost . '||' . $user->getEmail() . '!=' . $email);
        // exit;
        if ($user->getPrenom() . ' ' . $user->getNom() != $userPost || $user->getEmail() != $email) {
            // erreur par rapport au fait que l'email ou l'utilisateur ne sois pas bon
            throw new \Exception('informations utilisateur incorrectes, veuillez réessayer :(');
        }

        $event = new Google_Service_Calendar_Event(array(
            'summary' => 'RDV',
            'location' => 'Sindy Beauté',
            'description' =>
            '<p>Client: ' . $userPost  . '<br/>' .
                'Email: ' . $email . '<br/>' .
                'Telephone: <a href="tel:' . $telephone . '">'  . $telephone . '</a><br/>' .
                'Prix: ' . $prix . '€<br/>' .
                'Prix Abonnement: ' . $prixAbo . '€<br/>' .
                'Durée: ' . $duree . 'min <br/>' .
                'Soin(s): ' . $articles . '</p>',
            'start' => array(
                'dateTime' => $debutHor->format('c'),
            ),
            'end' => array(
                'dateTime' => $finHor->format('c'),
            ),
            'reminders' => array(
                'useDefault' => TRUE,
                //'overrides' => array(
                //array('method' => 'email', 'minutes' => 24 * 60),
                //array('method' => 'popup', 'minutes' => 10),
                //),
            ),
        ));




        $event = $service->events->insert($calendarId, $event);
        //add to user historique (commandes)
        $commande->setUser($this->getUser()); //pas très propre mais osef
        $commande->setDate($debutHor);
        //les articles ont étaient ajouté dans la boucle foreach plus haut
        $manager->persist($commande);
        $manager->flush();


        $msg = 'Votre rendez-vous à été validé pour le ' . $debutHor->format('d/m/Y H:i') . 'h';
        $this->addFlash('success', $msg);

        //email de détails pour le client
        $email = (new TemplatedEmail())
            ->from(new Address('sindybeautebot@gmail.com', 'SindyBeaute Mail Bot'))
            ->to($email)
            ->subject('Votre rendez-vous SindyBeauté')
            ->htmlTemplate('services/emailRDV.html.twig')
            ->context([
                'date' => $debutHor,
                'client' => $userPost,
                'telephone' => $telephone,
                'prix' => $prix,
                'prixAbonnement' => $prixAbo,
                'duree' => $duree,
                'soins' => $articles
            ]);
        $mailer->send($email);

        if ($session->get('fromBooking') == "wishlist") {
            return $this->forward('App\Controller\MainController::deleteAllWishList', [
                'from'  => "booking",
            ]);
        }
        return $this->redirectToRoute('home');
    }
    /**
     * Affichage reservation 
     *
     * @Route("/services/reservation", name="services_booking")
     */
    public function reserver($article, $prix, $prixAbo, $duree, $date = null, $sens = null, Request $request, ContainerParametersHelper $pathHelpers)
    {
        // Convert a date or timestamp into French.
        function monthToFrench($date)
        {
            $date = intval($date - 1);
            $french_months = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
            return $french_months[$date];
        }
        function rdv(DateTime $debut, DateTime $fin, $duree)
        {

            if ($debut > $fin)
                return false;

            $interval = $debut->diff($fin);
            $h = $interval->h;
            $m = $interval->i;
            $t = ($h * 60) + $m;
            if ($duree <= $t)  //compare durée rdv avec intervalle
                return true; //Si la durée du RDV est inf à l'intervale entre 2 zone libre (rdv ou début journeé par exemple alors on ajoute le crénaux dans le tab)
            else
                return false;
        }
        //horaire d'ouverture/fermeture
        $heureO = 10;
        $minO = 0;
        $heureF =  17;
        $minF = 0;

        if ($this->getUser() == null)
            return $this->redirectToRoute('security_connexion');

        if ($article == null) {
            $this->addFlash(
                'fail',
                "Aucun article séléctionné !"
            );
            return $this->redirectToRoute('show_WishList');
        }
        require_once($pathHelpers->getApplicationRootDir() . '/planningApi.php');
        // Get the API client and construct the service object.
        $client = getClient($pathHelpers);
        $service = new Google_Service_Calendar($client);


        //TO DO : Si le mec est à samedi ou dimanche le replacer au lundi de la semaine prochaine sinon c'est n'imp
        // Print the next 10 events on the user's calendar.
        $info = array();
        $startCalendar = new DateTime();
        $startCalendar->setTimezone(new DateTimeZone('Europe/Paris'));

        // vv rajouter une condition pour que si on reviens a la semaine de base on rentre pas dans la condi pcq sinon 
        //sa fait buggué par rapport au start calendar qui commence au début de la journée (10h) alors que journée =17h par exemple
        //piste: si la semaine choisi est dans la semaine actuel alors on rentre pas dans la condi
        dump(($startCalendar < (new DateTime())));
        //if ($date != null && $sens != null && ($startCalendar < (new DateTime())) == true) {
        if ($date != null && $sens != null) {
            $startCalendar = $date;
            dump($startCalendar);

            if ($sens == "next") {
                $startCalendar->add(new DateInterval('P7D')); //On ajoute une semaine mais faut se placer à lundi sinon bug
                if ($startCalendar->format('w') != 1)
                    $startCalendar->modify('last Monday');
            } else {
                $startCalendar->sub(new DateInterval('P7D'));
                if ($startCalendar->format('w') != 1)
                    $startCalendar->modify('last Monday');
            }

            if (($startCalendar < (new DateTime())) == false) {
                $startCalendar->setTime($heureO, $minO);
            } else {
                $startCalendar = new DateTime();
            }
            dump($startCalendar);
        }


        if ($startCalendar->format('w') == 6 || $startCalendar->format('w') == 0) { //si date est samedi ou dimanche ou l'initialise au lundi prochain
            $startCalendar->modify('next Monday');
            $startCalendar->setTime($heureO, $minO);
        }
        if ($startCalendar->format('w') != 1) {
            array_push($info, monthToFrench((clone $startCalendar)->modify('last monday')->format('m'))); //Pour afficher mois début semaine du front après
            $monday = (clone $startCalendar)->modify('last monday');
        } else {
            array_push($info, monthToFrench((clone $startCalendar)->format('m')));
            $monday = (clone $startCalendar);
        }
        if ($startCalendar->format('w') != 5)
            array_push($info, monthToFrench((clone $startCalendar)->modify('next friday')->format('m'))); //Pour afficher mois de fin semaine (vendredi) du front après
        else
            array_push($info, monthToFrench((clone $startCalendar)->format('m')));


        $endWeek =  clone $startCalendar;
        if ($endWeek->format('w') != 5)  //On prend la date du vendredi de la semaine actuelle (fin de semaine) 
            $endWeek->modify('next Friday');
        $endWeek->setTime($heureF, $minF);

        $calendarId = 'primary';
        date_default_timezone_set('Europe/Paris');
        $optParams = array(
            'maxResults' => 100,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMax' => $endWeek->format('c'),
            'timeMin' => $startCalendar->format('c')
        );
        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems(); //on choppe les event

        dump($endWeek->format('c'));
        dump($startCalendar->format('c'));

        // Vérification disponibilité planning.
        $rdvPossible = array();
        dump($startCalendar);
        if ($startCalendar->format('H') < 10)
            $startDispo = $startCalendar->format('Y-m-d') . ' ' . $heureO . ':' . $minF .  ':00';
        else
            $startDispo = $startCalendar->format('Y-m-d H:i:s');

        $event = new Google_Service_Calendar_Event(array(   //j'add un event qui prend tout le weekend, pour le mettre dans la boucle de traitement des events
            'summary' => 'EndWeek',
            //'description' => 'FakeEvent Vendredi soir à dimanche',
            'start' => array(
                'dateTime' => $endWeek->format('c'),
            ),
            'end' => array(
                'dateTime' => ($endWeek->modify('+2 day')->setTime($heureF, $minF))->format('c'),
            )
        ));

        array_push($events, $event);
        dump($startCalendar);
        $g = false;
        foreach ($events as $event) {
            $start = $event->start->dateTime;
            $startRdv = new DateTime($startDispo, new DateTimeZone('Europe/Paris'));
            $startEvent = new DateTime($start, new DateTimeZone('Europe/Paris'));
            do {
                //dump($startRdv->format('Y-m-d H:i') . '   ' . $startEvent->format('Y-m-d H:i'));
                $temp = (clone $startRdv)->setTime($heureF, $minF);
                if (($startEvent > $temp) == true) { //Si le prochain event est superieur à 18h de la date startrdv
                    if (rdv($startRdv, $temp, $duree) == true) { // On check si un rdv est possible entre la entre startRdv est ma fo, de journée (18h)
                        array_push($rdvPossible, [clone $startRdv, $temp]);  // On add l'event dans le tableau des rdv possible si il y a la place
                        //okay j'ai bloquer pendant 3h pcq il fallait mettre un clone dans le tab sinon la valeur changer lors de l'ajout d'un jour
                    }
                    $startRdv->add(new DateInterval('P1D')); //on init start rdv au lendemain 10h (heure ouverture) pour voir si rdv possible entre 10h et le début de l'event
                    $startRdv->setTime($heureO, $minO);
                    $g = true;
                } else {
                    $g = false;
                }
            } while ($g != false);

            if (rdv($startRdv, $startEvent, $duree) == true) {
                array_push($rdvPossible, [$startRdv, $startEvent]); //Si la durée du RDV est inf à l'intervale entre 2 zone libre (rdv ou début journeé par exemple alors on ajoute le crénaux dans le tab)
            }
            $startDispo = $event->end->dateTime;
        }


        dump($rdvPossible);
        //trie de tableau selon jour de travail (=lundi, jeudi, Vendredi) pour opti le twig après fin je sais pas si ça opti vraiment
        foreach ($rdvPossible as $key => $rdv) {
            $today = new DateTime();
            $today->setTimezone(new DateTimeZone('Europe/Paris'));
            switch ($rdv[0]->format('w')) {
                case 0:
                    unset($rdvPossible[$key]);
                    break;
                case 2:
                    unset($rdvPossible[$key]);
                    break;
                case 3:
                    unset($rdvPossible[$key]);
                    break;
                case 6:
                    unset($rdvPossible[$key]);
                    break;
            }
            if ($rdv[0] < $today->setTime($heureO, $minO)) //on vire les evenement passé
                unset($rdvPossible[$key]);
        }

        $days = array($monday->format('j'), $monday->add(new DateInterval('P1D'))->format('j'), $monday->add(new DateInterval('P1D'))->format('j'), $monday->add(new DateInterval('P1D'))->format('j'), $monday->add(new DateInterval('P1D'))->format('j'));
        $session = $request->getSession();

        $session->set('article', $article);
        $session->set('prix', $prix);
        $session->set('prixAbo', $prixAbo);
        $session->set('duree', $duree);
        $session->set('date', $startCalendar);

        //on compare la date aujoudhui avec celle actuelle
        // si < 7 alors on affiche pas le bouton prev
        function checkPrev($endWeek)
        {
            $today = new DateTime();
            $interval = $today->diff($endWeek);
            dump($interval);
            dump($endWeek);
        }
        $prev = checkPrev($endWeek);





        return $this->render('services/booking.html.twig', [
            'prix' => $prix,
            'prixAbo' => $prixAbo,
            'duree' => $duree,
            'events' => $rdvPossible,
            'info' => $info,
            'articles' => $article,
            'days' => $days,
            'user' => $this->getUser(),
            'monday' => $startCalendar
        ]);
    }


    /**
     * Permet d'afficher une seule annonce
     *
     * @Route("/services/booking", name="services_BookingFromWishList")
     */
    public function BookingFromShowWishList(Request $request)
    {
        $user = $this->getUser();
        if ($user == null)
            return $this->redirectToRoute('security_connexion');

        $wishList = $user->getWishList();
        if ($wishList->isEmpty()) {
            $this->addFlash(
                'fail',
                "Aucun article séléctionné !"
            );
            return $this->redirectToRoute('home');
        }

        $articlesInWish = $wishList->getValues();
        $prix = 0;
        $prixAbo = 0;
        $duree = 0;
        foreach ($articlesInWish as $article) {
            $prix += $article->getPrix();
            $duree += $article->getDuree();
            $prixAbo += $article->getPrixAbo();
        }
        $session = $request->getSession();
        $session->set('fromBooking', "wishlist");
        $response = $this->forward('App\Controller\ServicesController::reserver', [
            'article'  => $articlesInWish,
            'prix' => $prix,
            'prixAbo' => $prixAbo,
            'duree' => $duree
        ]);

        return $response;
    }
    /**
     * Permet d'afficher une seule annonce
     *
     * @Route("/services/{id}/booking", name="services_BookingFromShowById")
     */
    public function BookingFromShowById(Article $article, Request $request, ObjectManager $manager)
    {


        $user = $this->getUser();
        if ($user == null)
            return $this->redirectToRoute('security_connexion');

        $wishList = $user->getWishList();
        if ($wishList->isEmpty()) {
            $session = $request->getSession();
            $session->set('fromBooking', "id");
            $articleArr = array($article); // je met l'article dans un tab sinon ça fait tb dans la suite

            return $this->forward('App\Controller\ServicesController::reserver', [
                'article'  => $articleArr,
                'prix' => $article->getPrix(),
                'prixAbo' => $article->getPrixAbo(),
                'duree' => $article->getDuree(),
            ]);
        } else
            return $this->redirectToRoute('show_WishList', ['_fragment' => 'RDV']);
    }

    /**
     * Permet d'afficher une seule annonce
     *
     * @Route("/services/rebooking/{id}", name="services_BookingFromCommande") 
     */
    public function BookingFromCommande(Commande $commande, Request $request)
    {
        $user = $this->getUser();
        if ($user == null)
            return $this->redirectToRoute('security_connexion');


        $prix = 0;
        $prixAbo = 0;
        $duree = 0;
        foreach ($commande->getArticles() as $article) {
            $prix += $article->getPrix();
            $duree += $article->getDuree();
            $prixAbo += $article->getPrixAbo();
        }
        $session = $request->getSession();
        $session->set('fromBooking', "wishlist");
        return $this->forward('App\Controller\ServicesController::reserver', [
            'article'  => $commande->getArticles(),
            'prix' => $prix,
            'prixAbo' => $prixAbo,
            'duree' => $duree
        ]);
    }

    /**
     * Permet d'afficher une seule annonce
     *
     * @Route("/services/{id}", name="services_showById")
     */
    public function showById(Article $article)
    {
        $user = $this->getUser();
        if ($user == null)
            return $this->render('services/showById.html.twig', [
                'article' => $article,
                'inWishList' => false
            ]);

        if (!$article)
            throw $this->createNotFoundException('L\'article n\'existe pas.');

        $wishList = $user->getWishList();

        $inWishList = false;
        if ($wishList->contains($article)) {
            $inWishList = true;
        }

        return $this->render('services/showById.html.twig', [
            'article' => $article,
            'inWishList' => $inWishList
        ]);
    }
}
