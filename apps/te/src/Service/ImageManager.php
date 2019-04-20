<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;

class ImageManager
{
    const ALGO_REGEX = 1;
    const ALGO_DOM = 2;
    const ALGO_REGEX_PATTERN = "/<img[^>]+src=[\"'](?<imgSRC>.*)[\"'][^>]*>/i";
    const IMAGE_EXTENSIONS = ['png','jpg','jpeg'];
//    private $domain;
    private $sc;
    private $imageDir;
    private $imageStorageDirPath;
//    private $imageExtensions;

    public function __construct(string $imageDir, ContainerInterface $sc)
    {
        $this->sc = $sc;
        $this->imageDir = $imageDir;
        $this->imageStorageDirPath = $this->sc->get('kernel')->getProjectDir() .  "/public/{$this->imageDir}";
    }


    public function grabPageImages(string $url, int $minWidth, int $minHeight, array $imageExtensions = self::IMAGE_EXTENSIONS)
    {
        $srcImageList = $this->getPageImages($url);
        $srcImageList = [$srcImageList[0]];
        $srcImageList = array_map(function ($src) use ($url) {

            $urlParts = parse_url($url);
            $urlHost = $urlParts['host'];
            $urlSchema = $urlParts['scheme'];

            if (1 === preg_match('/^\/.+/i', $src)) {
                $schemaPrefix = $urlSchema ? $urlSchema . '://' : '';
                return  $schemaPrefix . $urlHost . $src;
            }

            return $src;

        }, $srcImageList);

        $srcImageList = array_filter($srcImageList, function ($src) use ($minWidth, $minHeight, $imageExtensions){
            // ... get rid of inappropriate image extensions
            $regexExtensionsPart = implode('|', $imageExtensions);
            if (1 !== preg_match("/.*\.({$regexExtensionsPart})((\?.*$)|$)/i", $src)) {
                return false;
            }
            // ... leave only correct images
            $imgMetData = @getimagesize($src);

            if (!$imgMetData) {
                return false;
            }

            return $imgMetData[0] >= $minWidth && $imgMetData[1] >= $minHeight;

        });

        // Load, Crop, Text, Save
        //------------------------
        $ts = time();
        $date = date("d.m.y H:i:s");

        // ...Load
        $image = file_get_contents($srcImageList[0]);
        $img = new \Imagick();
        $img->readImageBlob($image);

        // ... Crop
        $img->cropImage(200,200, 0, 0);


        // ...Text
        $draw = new \ImagickDraw();

        $draw->setFillColor('black');

        $draw->setFont('NotoMono-Regular.ttf');
//        $draw->setFontWeight(800);
        $draw->setFontSize( 25 );
//        $draw->setFontStyle( 15 );
        $draw->setStrokeColor( 'red' );
        $draw->setStrokeWidth( 1 );

        $img->annotateImage($draw, 10, 198, -45, $date);



        // ...Save
        $imgExt = mb_strtolower($img->getImageFormat());
        $fileName = $ts . '_' . md5($srcImageList[0] . rand(1,1000))  . '.' . $imgExt;
        $img->writeImage($this->imageStorageDirPath . '/' . $fileName);

        return $img->getImageFilename();

    }

    /**
     * @param string $url
     * @param int $algo
     * @return array
     */
    public function getPageImages(string $url, $algo = self::ALGO_DOM): array
    {
        $html = file_get_contents($url);

        if (FALSE === $html) {
            return [];
        }

        if ($algo === self::ALGO_DOM) {
            return $this->parseImagesDOM($html);
        } elseif ($algo === self::ALGO_REGEX) {
            return $this->parseImagesRegex($html);
        } else {
            return [];
        }
    }

    /**
     * @param string $html
     * @return array
     */
    private function parseImagesRegex(string $html): array {

        $regexResult = preg_match_all(self::ALGO_REGEX_PATTERN, $html, $imgSrcList);

        if (FALSE === $regexResult) {
            return [];
        }

        return $imgSrcList['imgSRC'];
    }

    /**
     * @param string $html
     * @return array
     */
    private function parseImagesDOM(string $html): array {

        $crawler = new Crawler($html);
        $crawlerResult = $crawler
            ->filterXpath('//img')
            ->extract(array('src'));

        return $crawlerResult;
    }

//    public function collectImages(string $url): array
//    {
//
//    }
//
//    public function setDomain($domain = null)
//    {
//        $this->domain = $domain;
//    }
//
//    public function getSite(): Site
//    {
//        return $this->site;
//    }
//
//    public function setSite(Site $site = null)
//    {
//        $this->site = $site;
//    }



}