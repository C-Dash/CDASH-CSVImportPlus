<?php
namespace CSVImport\Mapping;

use CSVImport\Mapping\MappingInterface;

abstract class AbstractMapping implements MappingInterface
{
    protected $args;

    protected $api;

    protected $logger;
    
    protected $serviceLocator;

    public function __construct($args, $serviceLocator)
    {
        $this->args = $args;
        $this->logger = $serviceLocator->get('Omeka\Logger');
        $this->api = $serviceLocator->get('Omeka\ApiManager');
        $this->serviceLocator = $serviceLocator;
    }

}