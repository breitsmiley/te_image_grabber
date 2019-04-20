<?php

namespace App\Controller;

use App\Service\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(ImageManager $imageManager)
    {
        $url = 'https://tile.expert';
//        $url = 'https://dig.ua/';

        $tplData = [
            'test' => $imageManager->grabPageImages($url, 0 , 0),
        ];

        return $this->render('default/index.html.twig', $tplData);

    }
}
