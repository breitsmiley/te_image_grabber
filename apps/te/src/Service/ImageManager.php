<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ImageManager
{
    const ALGO_REGEX = 1;
    const ALGO_DOM = 2;
    const ALGO_REGEX_PATTERN = "/<img[^>]+src=[\"'](?<imgSRC>.*)[\"'][^>]*>/i";
    const CROP_SIZE = 200;
    const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg'];

    private $sc;
    private $fileSystem;
    private $imageDir;
    private $imageStorageDirPath;
    private $fontPath;

    public function __construct(string $imageDir, ContainerInterface $sc)
    {
        $this->sc = $sc;
        $this->fileSystem = new Filesystem();
        $this->imageDir = $imageDir;
        $this->imageStorageDirPath = $this->sc->get('kernel')->getProjectDir() . "/public/{$this->imageDir}";
        $this->fontPath = $this->sc->get('kernel')->getProjectDir() . "/public/build/NotoMono-Regular.ttf";
    }

    /**
     * @param string $html
     * @return array
     */
    private function parseImagesRegex(string $html): array
    {
        $regexResult = preg_match_all(self::ALGO_REGEX_PATTERN, $html, $imgSrcList);

        if (false === $regexResult) {
            return [];
        }

        return $imgSrcList['imgSRC'];
    }

    /**
     * @param string $html
     * @return array
     */
    private function parseImagesDOM(string $html): array
    {
        $crawler = new Crawler($html);
        $crawlerResult = $crawler
            ->filterXpath('//img')
            ->extract(array('src'));

        return $crawlerResult;
    }


    /**
     * @param string $url
     * @param int $algo
     * @return array
     */
    private function getPageImages(string $url, $algo = self::ALGO_DOM): array
    {
        $html = file_get_contents($url);

        if (false === $html) {
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
     * @param string $imageURL
     * @param int $cropSize
     * @return bool|string
     */
    private function processImage(string $imageURL, int $cropSize)
    {
        // Load, Crop, Text, Save
        //------------------------
        $ts = time();
        $date = date("d.m.y H:i:s");

        // ...Load
        try {
            $image = file_get_contents($imageURL);
            $img = new \Imagick();

            $img->readImageBlob($image);

            // ... Crop
            $img->cropImage($cropSize, $cropSize, 0, 0);

            // ...Text
            $draw = new \ImagickDraw();
            $draw->setFillColor('black');
            $draw->setFont($this->fontPath);
            $draw->setFontSize(25);
            $draw->setStrokeColor('red');
            $draw->setStrokeWidth(1);

            $img->annotateImage($draw, 10, 198, -45, $date);

            // ...Save
            $imgExt = mb_strtolower($img->getImageFormat());
            $fileName = $ts . '_' . md5($imageURL . rand(1, 1000)) . '.' . $imgExt;
            $img->writeImage($this->imageStorageDirPath . '/' . $fileName);

        } catch (\ImagickException $e) {
            return false;
        }
        return $fileName;
    }

    private function getImageStoragePathByName(string $imageFileName): string
    {
        return '/' . $this->imageDir . '/' . $imageFileName;
    }

    /**
     * @param string $url
     * @param int $minWidth
     * @param int $minHeight
     * @param int $cropSize
     * @param array $imageExtensions
     * @return array
     */
    public function grabPageImages(
        string $url,
        int $minWidth,
        int $minHeight,
        int $cropSize = self::CROP_SIZE,
        array $imageExtensions = self::IMAGE_EXTENSIONS
    )
    {
        $imageSrcList = $this->getPageImages($url);

        $imageSrcList = array_map(
            function ($src) use ($url) {

                $urlParts = parse_url($url);
                $urlHost = $urlParts['host'];
                $urlSchema = $urlParts['scheme'];

                if (1 === preg_match('/^\/.+/i', $src)) {
                    $schemaPrefix = $urlSchema ? $urlSchema . '://' : '';

                    return $schemaPrefix . $urlHost . $src;
                }

                return $src;

            },
            $imageSrcList
        );

        $imageSrcList = array_filter(
            $imageSrcList,
            function ($src) use ($minWidth, $minHeight, $imageExtensions) {
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

            }
        );

        $savedImagePathList = [];
        foreach ($imageSrcList as $imageSrc) {
            $fileName = $this->processImage($imageSrc, $cropSize);
            if ($fileName) {
                $savedImagePathList[] = $this->getImageStoragePathByName($fileName);
            }
        }

        return array_reverse($savedImagePathList);
    }

    /**
     * @return array
     */
    public function getImageSrcList()
    {
        $finder = new Finder();
        $finder->in($this->imageStorageDirPath)
            ->files()
            ->depth('== 0')
            ->sortByChangedTime()
            ->reverseSorting();

        $imageSrcList = [];
        foreach ($finder as $imageFile) {
            $imageSrcList[] = $this->getImageStoragePathByName($imageFile->getFilename());
        }

        return $imageSrcList;
    }

}