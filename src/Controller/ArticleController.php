<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\Entity\Article;
use App\Repository\ArticleRepository;

class ArticleController extends AbstractController
{
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
        $this->_serializer = new Serializer([$this->_normalizer], [$this->_encoder]);
    }
    /**
     * @Route("/api/articles", methods={"GET"})
     */
    public function index(): Response
    {
        $articles = $this->_serializer->serialize($this->_q->findAll(), 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($articles, 200, ['Content-Type' => 'application/json']);
    }
    /**
     * @Route("/api/articles/{id}", methods={"GET", "PATCH"})
     */
    public function singleArticle(int $id, Request $request): Response
    {
        // Récupération de la methode pour l'execution de la tache correspondante
        switch($request->getMethod()) {
            case "GET" : {
                $article = $this->_q->findById($id);
                if(!$article) {
                    return new Response('Not found.', Response::HTTP_NOT_FOUND);
                }
            };
            break;
            case "PATCH" : {
                if(!$this->_q->findById($id)) {
                    return new Response('Not found.', Response::HTTP_NOT_FOUND);
                }
                $article = $this->_serializer->deserialize($request->getContent(), Article::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $this->_q->findOneBy(["id" => $id])]);

                $this->_q->add($article);
            };
            break;
            default : break;
        }

        $jsonArticle = $this->_serializer->serialize($article, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonArticle, 200, ['Content-Type' => 'application/json']);
    }
}
