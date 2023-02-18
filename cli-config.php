<?php

require 'vendor/autoload.php';

use Alura\Doctrine\Helper\EntityManagerCreator;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;

// Definir o caminho absoluto do arquivo migrations.php:
$config = new PhpFile(__DIR__ . '/migrations.php'); 

// Usar o EntityManager do projeto.
$entityManager = EntityManagerCreator::createEntityManager();

return DependencyFactory::fromEntityManager($config, new ExistingEntityManager($entityManager));
