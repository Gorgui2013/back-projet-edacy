<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\Entity\Media;
use App\Entity\Article;
use App\Repository\ArticleRepository;

class InitFluxRssController extends AbstractController
{

    // champ de recupération du flux RSS
    private $_fluxRss = 'https://www.lemonde.fr/rss/en_continu.xml';

    // Le nombre d'item dans le plux RSS
    private $_countItem = 0;

    // champ de récupération du repositorie de l'entité Article
    private $_q;

    //
    private $_serializer;
    private $_normalizer;
    private $_encoder;

    // Contructeur avec initialisation de l'entity manager
    public function __construct(ArticleRepository $q) {
        $this->_q = $q;
        $this->_normalizer = new ObjectNormalizer();
        $this->_encoder = new JsonEncoder();
        $this->_serializer = new Serializer([new DateTimeNormalizer(), $this->_normalizer], [$this->_encoder]);
    }

    /**
     * @Route("/api/init", methods={"GET"})
     */
    public function index(): Response
    {
        $this->chargement();
         $resp = $this->_serializer->serialize(['message' => 'Chargement faite avec succé'], 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($resp, 200);
    }

    // fonction de chargement de tous les articles en appelant addArticle();
    public function chargement() {

        // lecture du flux RSS avec le module SIMPLEXML de PHP
        $data = simplexml_load_file($this->_fluxRss);

        // Récupération du nombre d'item contenu dans le flux
        $this->_countItem = $data->channel->item->count();

        // Parcour de la liste des items pour alimenter notre base de données
        foreach($data->channel->item as $elt) {
            $this->addArticle($elt);
        }
    }

    // fonction de chargement d'un article
    public function addArticle($elt) {
        // Instatiation d'un article correspondant à un item du flux RSS
        $article = new Article();
        $article->setTitle($elt->title);
        $article->setDescription($elt->description);
        $article->setLink($elt->link);
        $article->setpubDate(new \DateTime($elt->pubDate));

        // Instatiation d'un media d'article du flux RSS
        $media = new Media();
        $media->setCredit($elt->children('media', true)->content->credit);
        $media->setDescription($elt->children('media', true)->content->description);
        $media->setUrl($elt->children('media', true)->attributes()->url);
        $media->setHeight((int)$elt->children('media', true)->attributes()->height);
        $media->setWidth((int)$elt->children('media', true)->attributes()->width);

        // Ajout du media dans l'article
        $article->setMedia($media);

        // persistence des information d'une article dans la base
        $this->_q->add($article);
    }
}
