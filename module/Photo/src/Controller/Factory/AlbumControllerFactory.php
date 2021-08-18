<?php

namespace Photo\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Photo\Controller\AlbumController;

class AlbumControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     *
     * @return AlbumController
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): AlbumController {
        return new AlbumController(
            $container->get('photo_service_album'),
            $container->get('album_page_cache'),
            $container->get('config')['photo'],
        );
    }
}