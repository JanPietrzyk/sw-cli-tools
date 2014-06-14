<?php

namespace ShopwareCli\Services;

use ShopwareCli\Config;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class DefaultRepositoryFactory
{

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getDefaultRepositories()
    {
        /** @var Config $config */
        $config = $this->container->get('config');

        $repositories = array();

        foreach ($config->getRepositories() as $type => $data) {
            $className = 'ShopwareCli\\Services\\Repositories\\' . $type;

            $options = array();
            if (isset($data['config'])) {
                $options = array(
                    'base_url' => $data['config']['endpoint'],
                    'username' => $data['config']['username'],
                    'password' => $data['config']['password']
                );
            }

            foreach ($data['repositories'] as $name => $repoConfig) {
                $cacheTime = isset($repoConfig['cache']) ? $repoConfig['cache'] : 3600;

                $repo = new $className(
                    isset($repoConfig['url']) ? $repoConfig['url'] : '',
                    $name,
                    $this->container->get('rest_service_factory')->factory($options, $cacheTime)
                );
                $repositories[] = $repo;

                if ($repo instanceof ContainerAwareInterface) {
                    $repo->setContainer($this->container);
                }
            }
        }

        return $repositories;
    }
}