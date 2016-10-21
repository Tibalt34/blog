<?php

namespace AdminBundle\Controller;

use AdminBundle\Entity\News;
use AdminBundle\Entity\User;
use AdminBundle\Form\NewsType;
use AdminBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of AdminViewController
 *
 * @author jonathan
 */
class AdminViewController extends Controller {

    /**
     * @Route("/admin", name="admin")
     */
    public function getAdmin() {
        
        return $this->render('AdminBundle:Default:admin.html.twig');
    }

    /**
     * @Route("/connexion", name="connexion")
     * 
     */
    public function getConnexion() {  //Fonction pour connecter l'utilisateur
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {

            return $this->redirectToRoute("bool"); //Retour sur la page principal//             
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        return $this->render('AdminBundle:Default:connexion.html.twig', array(
                    'last_username' => $authenticationUtils->getLastUsername(),
                    'error' => $authenticationUtils->getLastAuthenticationError(),
        ));
    }

    /**
     * @Route("/inscription", name="inscription")
     * 
     */
    public function getInscription(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $user = new User();//Instance de l'entité User

        //Si il détecte une requete ajax
        if ($request->getMethod() == "POST") {
            $user->setUsername($request->get('username'));
            $user->setPassword($request->get('password'));
            $user->setNom($request->get('nom'));
            $user->setPrenom($request->get('prenom'));
            $user->setSalt("");
            $user->setRoles(array("ROLE_USER"));

            $em->persist($user);
            $em->flush();


            $response = new JsonResponse(); //Retour des données au format Json
            $response->setData(array('reussite' => 'Inscription reussi !'));

            return $response;
        }
        return $this->render('AdminBundle:Default:inscription.html.twig');
    }

    /**
     * @Route ("/articles",name="articles")
     */
    public function getArticles() {
        //ici je récupere toutes les news
        $repository = $this->getDoctrine()->getManager()->getRepository('AdminBundle:News');
        $listNews = $repository->findAll();

        return $this->render('AdminBundle:Default:articles.html.twig', array('listNews' => $listNews));
    }
    
    /**
     * @Route("/add",name="add")
     */
    public function addArticle(Request $request){
        
        //Je crée un nouvel objet
        $article = new News();
        //Je crée le formulaire à partir de la classe NewsType
        $news= $this->createForm(NewsType::class,$article);
        //quand le formulaire est envoyé on envoi un nouvel article
        if ($request->getMethod() == 'POST') {
            $news->handleRequest($request);
            $em = $this->getDoctrine()->getEntityManager();
            //Met le pseudo dans l'utilisateur courant dans le champ auteur
            
            $article->setAuteur($this->getUser()->getPseudo());
            $article->setDate(new \DateTime());
            
            $em->persist($article);
            $em->flush();
            return $this->redirectToRoute('articles');
        }
    
        return $this->render('AdminBundle:Default:newarticle.html.twig' , array('form' => $news->createView()));
     
    }
    
     /**
     * @Route("/modif/{id}", name="modif")
     */
    public function modifArticle($id, Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $article = $em->find('AdminBundle:News', $id);
        $news= $this->createForm(NewsType::class,$article);
         if ($request->getMethod() == 'POST') {
            $news->handleRequest($request);
            $em->merge($article);
            $em->flush();
            return $this->redirectToRoute('articles');
        }
        return $this->render('AdminBundle:Default:modifarticle.html.twig' , array('form' => $news->createView()));
        
    }
     /**
     * @Route("/supp/{id}", name="supp")
     */
    public function suppArticle($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $news = $em->find('AdminBundle:News', $id);
        //ici je récupère l'entité par l'idée
        $em->remove($news);
        //ici je la supprimé
        $em->flush();
        //affecte tous les changements en base de données
        return $this->redirectToRoute('articles');
        //ci-dessus une fois mon article supprimé je redirige vers la vue articles
    }
    
     /**
     * @Route ("/brouillons",name="brouillons")
     */
    public function getBrouillons() {
        //ici je récupere toutes les brouillons
        $repository = $this->getDoctrine()->getManager()->getRepository('AdminBundle:News');
        $listBrouillons = $repository->findAll();

        return $this->render('AdminBundle:Default:brouillons.html.twig', array('listBrouillons' => $listBrouillons));
    }
    
     /**
     * @Route("/profil", name="profil")
     */
    public function getProfil() {
        //ici je récupere toutes mon profil
        $repository = $this->getDoctrine()->getManager()->getRepository('AdminBundle:User');
        $monProfil = $repository->findBy(array('pseudo' => $this->getUser()->getPseudo()));

        return $this->render('AdminBundle:Default:profil.html.twig', array('monProfil' => $monProfil));
    }
    
    /**
     * @Route("/modifprofil", name="modifprofil")
     */
    public function getModifprofil(Request $request) 
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $profil= $this->createForm(UserType::class, $this->getUser());
         if ($request->getMethod() == 'POST') {
            $profil->handleRequest($request);
            $em->merge($this->getUser());
            $em->flush();
            return $this->redirectToRoute('modifprofil');
        }
        return $this->render('AdminBundle:Default:modifprofil.html.twig' , array('form' => $profil->createView()));
        
    }

    
}
