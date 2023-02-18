<?php

use Alura\Doctrine\Entity\Phone;
use Alura\Doctrine\Entity\Student;
use Alura\Doctrine\Helper\EntityManagerCreator;

require_once __DIR__ . '/../vendor/autoload.php';

$entityManager = EntityManagerCreator::createEntityManager();

$student = new Student($argv[1]);

// $argc conta os parâmetros fornecidos via CLI.
// É o mesmo que count($argv).
for ($i=2; $i < $argc; $i++) {
    $student->addPhone(new Phone($argv[$i]));
}

$entityManager->persist($student);
$entityManager->flush();
