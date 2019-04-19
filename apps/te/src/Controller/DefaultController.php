<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DomCrawler\Crawler;


class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index()
    {

        $url = 'https://tile.expert';
//
        $html= file_get_contents($url);
        preg_match_all("/<img[^>]+src=[\"'](?<imgSRC>.*)[\"'][^>]*>/i",$html, $regexResult);


        $crawler = new Crawler($html);
        $crawlerResult = $crawler
            ->filterXpath('//img')
            ->extract(array('src'));


        $file = file_get_contents('https://img.tile.expert/img_lb/ibero-porcelanico/advance/per_sito/m_main.jpg');
        $insert = file_put_contents('/app/var/m_main.jpg', $file);
        if (!$insert) {
            throw new \Exception('Failed to write image');
        }

        $tplData = [
            'regexResult' => $regexResult['imgSRC'],
            'crawlerResult' => $crawlerResult,
        ];


        return $this->render('default/index.html.twig', $tplData);

    }
}
