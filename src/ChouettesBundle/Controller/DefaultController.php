<?php

namespace ChouettesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
// Connexion à la BdD
        $em = $this->getDoctrine()->getManager();
// Recupération des données CITATIONS
        $citations = $em->getRepository('ChouettesBundle:Citation')->findAll();
// Recupération des données MODELE pour les images affichables sur la pages d'accueil
        $modeles = $em->getRepository('ChouettesBundle:Modele')->findBy(array('add_block' => true));

// -----------------------------------------------------------------------------------------------------
// Mise en place random pour afficher aléatoirement les CITATIONS sur la page
// d'accueil. Si aucune citation existe dans la base de données on renvoi comme contenu un chaine vide
// Dans Default/index.html.twig
// -----------------------------------------------------------------------------------------------------
        if (!empty($citations)) {
            $randomcitation = $citations[array_rand($citations)]->getText();
        } else {
            $randomcitation = '';
        }


// -----------------------------------------------------------------------------------------------------
// Mise en place random pour afficher aléatoirement les PHOTOS par CATEGORIES sur la page
// d'accueil.
// -----------------------------------------------------------------------------------------------------
// 1 - Bijoux
// 2 - Doudous
// 3 - Accessoires
// Récuperation des champs contenus dans la variable $modeles

// Initialisation des variables
        $bijoux = array();
        $doudou = array();
        $accessoire = array();

// Parcours de l'objet modeles
        foreach ($modeles as $modele) {
// Isole les modeles catgorie bijoux
            if ($modele->getCategorie()->getNom() == 'Bijoux') {
                $bijoux[] = $modele;
            }
// Isole les modeles catgorie doudou
            elseif ($modele->getCategorie()->getNom() == 'Doudous') {
                $doudou[] = $modele;
            }
// Isole les modeles catgorie accessoire
            else {
                $accessoire[] = $modele;
            }
        }

// -----------------------------------------------------------------------------------------------------
// Test si la base de données est suffisament remplie
// si pas assez de modele dans chaque catégorie redirection vers création
// nouveau modèle
// -----------------------------------------------------------------------------------------------------
        if(empty($accessoire) || empty($bijoux) || count($doudou)<2) {

// Ajout message pour inviter l'admin à completer sa base de données
            $this->addFlash(
                'notice',
                'Il faut remplir au minimum, 1 modèle bijoux, 1 modèle accessoire et 2 modèles doudous'
            );

            return $this->redirectToRoute('modele_new');

        }


// Choix aléatoire des modèles à afficher
        $randomBijoux = $bijoux[array_rand($bijoux)];
        $indexElimine = array_rand($doudou);
        $randomDoudou = $doudou[$indexElimine];
        unset($doudou[$indexElimine]);
        $randomDoudou2 = $doudou[array_rand($doudou)];
        $randomAccessoire = $accessoire[array_rand($accessoire)];

// -----------------------------------------------------------------------------------------------------
// retourne citation et images dans Default/index.html.twig
// -----------------------------------------------------------------------------------------------------
        return $this->render('@Chouettes/Default/index.html.twig', array(
            'bijoux' => $randomBijoux,
            'doudous' => $randomDoudou,
            'doudous2' => $randomDoudou2,
            'accessoire' => $randomAccessoire,
            'citation' => $randomcitation
        ));
    }

    public function doudousAction()
    {
        $em = $this->getDoctrine()->getManager();
        $modeles = $em->getRepository('ChouettesBundle:Categorie')->getDoudouByCateg('Doudous');
        return $this->render('@Chouettes/user/doudous.html.twig', array(
            'modeles' => $modeles
        ));
    }

    public function bijouxAction()
    {
        $em = $this->getDoctrine()->getManager();
        $modeles = $em->getRepository('ChouettesBundle:Categorie')->getDoudouByCateg('Bijoux');
        return $this->render('@Chouettes/user/bijoux.html.twig', array(
            'modeles' => $modeles
        ));
    }

    public function accessoiresAction()
    {
        $em = $this->getDoctrine()->getManager();
        $modeles = $em->getRepository('ChouettesBundle:Categorie')->getDoudouByCateg('Accessoires');
        return $this->render('@Chouettes/user/accessoires.html.twig', array(
            'modeles' => $modeles
        ));
    }

    public function aboutAction()
    {
        $em = $this->getDoctrine()->getManager();
        $whoamis = $em->getRepository("ChouettesBundle:Whoami")->findAll();
        return $this->render('@Chouettes/user/about.html.twig', array(
            'whoamis'=>$whoamis
        ));
    }

    public function contactAction()
    {
        return $this->render('@Chouettes/user/contact.html.twig');
    }

    public function sendAction(Request $request)
    {
        $from = $this->getParameter('mailer_user');
// Instanciation des variables name, firstname, mail, sujet, msg pour récupérer la data
        $name = $request->request->get('nom');
        $firstname = $request->request->get('prenom');
        $mail = $request->request->get('mail');
        $sujet = $request->request->get('Sujet');
        $msg = $request->request->get('msg');
// Instanciation d'un nouveau message vers l'administrateur avec la prise en compte des variables
        $message = \Swift_Message::newInstance()
            ->setSubject('Contact Chouettes')
            ->setFrom(array($from => 'ChouettesHiboux'))
            ->setTo($from)
            ->setBody(
                $this->renderView(
                    '@Chouettes/user/mail.html.twig',
                    array(
                        'nom' => $name,
                        'prenom' => $firstname,
                        'mail' => $mail,
                        'sujet' => $sujet,
                        'message' => $msg
                    )
                ),
                'text/html'
            );

// Instanciation d'un nouveau message vers l'utilisateur avec la prise en compte des variables
        $message2 = \Swift_Message::newInstance()
            ->setSubject('Copie Contact Chouettes')
            ->setFrom(array($from => 'ChouettesHiboux'))
            ->setTo($mail)
            ->setBody(
                $this->renderView(
                    '@Chouettes/user/mail2.html.twig',
                    array(
                        'nom' => $name,
                        'prenom' => $firstname,
                        'mail' => $mail,
                        'sujet' => $sujet,
                        'message' => $msg
                    )
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);
        $this->get('mailer')->send($message2);
// Ajout message sur page d'accueil pour informé de l'envoi du mail
        $this->addFlash(
            'notice',
            'Votre message a bien été envoyé'
        );
        return $this->redirectToRoute('chouettes_homepage');

    }

    public function adminAction()
    {
        return $this->render('@Chouettes/Admin/index.html.twig');
    }
}
