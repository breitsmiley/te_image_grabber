<?php

namespace App\Service;

use FasterImage\FasterImage;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use GuzzleHttp\Promise as GuzzlePromise;

class ImageManager
{
    const ALGO_REGEX = 1;
    const ALGO_DOM = 2;
    const ALGO_REGEX_PATTERN = "/<img[^>]+src=[\"'](?<imgSRC>.*)[\"'][^>]*>/i";
    const CROP_SIZE = 200;
    const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg'];
    const GRAB_MODE_SLOW = 'slow';
    const GRAB_MODE_FAST = 'fast';

    private $sc;
    private $fileSystem;
    private $guzzleCLient;
    private $imageDir;
    private $imageStorageDirPath;
    private $fontPath;

    public function __construct(string $imageDir, ContainerInterface $sc)
    {
        $this->sc = $sc;
        $this->fileSystem = new Filesystem();
        $this->guzzleCLient = $this->sc->get('eight_points_guzzle.client.my_client');
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
     * @param string $imageBlob
     * @param int $cropSize
     * @return bool|string
     */
    private function processImage(string $imageBlob, int $cropSize)
    {
        // Load, Crop, Text, Save
        //------------------------
        $ts = time();
        $date = date("d.m.y H:i:s");

        // ...Load
        try {
            $img = new \Imagick();

            $img->readImageBlob($imageBlob);

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
            $fileName = $ts . '_' . md5($ts . rand(1, 1000)) . '.' . $imgExt;
            $img->writeImage($this->imageStorageDirPath . '/' . $fileName);

        } catch (\ImagickException $e) {
            return false;
        }
        return $fileName;
    }

    /**
     * @param string $imageFileName
     * @return string
     */
    private function getImageStoragePathByName(string $imageFileName): string
    {
        return '/' . $this->imageDir . '/' . $imageFileName;
    }

    /**
     * @param array $imageSrcList
     * @param int $minWidth
     * @param int $minHeight
     * @return array
     */
    private function filterImagesSlow(
        array $imageSrcList,
        int $minWidth,
        int $minHeight
    )
    {
        $imageExtensions = self::IMAGE_EXTENSIONS;
        return array_filter(
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
    }

    /**
     * @param array $imageSrcList
     * @param int $minWidth
     * @param int $minHeight
     * @return array
     */
    private function filterImagesFast(
        array $imageSrcList,
        int $minWidth,
        int $minHeight
    )
    {
        $client = new FasterImage();

        try {
            $images = $client->batch($imageSrcList);
        } catch (\Exception $e) {
            return [];
        }

        $imageSrcList = [];
        foreach ($images as $imageSrc => $imageData) {
            list($width, $height) = $imageData['size'];

            if ($width >= $minWidth && $height >= $minHeight) {
                $imageSrcList[] = $imageSrc;
            }
        }

        return $imageSrcList;
    }

    /**
     * @param array $imageSrcList
     * @return array
     */
    private function getBlobImagesSlow(array $imageSrcList)
    {

        $blobImages = [];
        foreach ($imageSrcList as $imageSrc) {
            $imageContent = file_get_contents($imageSrc);

            if ($imageContent) {
                $blobImages[] = $imageContent;
            }
        }
        return $blobImages;
    }

    /**
     * @param array $imageSrcList
     * @return array
     */
    public function getBlobImagesFast(array $imageSrcList)
    {

        $requestPromises = [];
        foreach ($imageSrcList as $uri) {
            $requestPromises[] = $this->guzzleCLient->getAsync($uri);
        }
        $responses = GuzzlePromise\settle($requestPromises)->wait();

        $blobImages = [];
        foreach ($responses as $oneResponseData) {

            if (!isset($oneResponseData['state'])
                || !isset($oneResponseData['value'])
            ) {
                continue;
            }
            /** @var  ResponseInterface $oneResponse */
            $oneResponse = $oneResponseData['value'];

            if ($oneResponseData['state'] === 'fulfilled'
                && $oneResponse->getStatusCode() === 200
            ) {
                $blobImages[] = $oneResponse->getBody()->getContents();

            }
        }
        return $blobImages;
    }

    /**
     * @param string $url
     * @param int $minWidth
     * @param int $minHeight
     * @param string $grubMode
     * @param int $cropSize
     * @return array
     */
    public function grabPageImages(
        string $url,
        int $minWidth,
        int $minHeight,
        string $grubMode = self::GRAB_MODE_FAST,
        int $cropSize = self::CROP_SIZE
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

        $imageSrcList = $grubMode === self::GRAB_MODE_FAST ?
            $this->filterImagesFast($imageSrcList, $minWidth, $minHeight) :
            $this->filterImagesSlow($imageSrcList, $minWidth, $minHeight);

        $blobImages = $grubMode === self::GRAB_MODE_FAST ?
            $this->getBlobImagesFast($imageSrcList) :
            $this->getBlobImagesSlow($imageSrcList);

        $savedImagePathList = [];
        foreach ($blobImages as $oneBlobImage) {
            $fileName = $this->processImage($oneBlobImage, $cropSize);
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