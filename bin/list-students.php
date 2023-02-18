<?php

use Alura\Doctrine\Entity\Course;
use Alura\Doctrine\Entity\Phone;
use Alura\Doctrine\Entity\Student;
use Alura\Doctrine\Helper\EntityManagerCreator;

require_once __DIR__ . '/../vendor/autoload.php';

$entityManager = EntityManagerCreator::createEntityManager();
$dql = 'SELECT student FROM Alura\\Doctrine\\Entity\\Student as student';

/** @var Student[] $studentList */
$studentList = $entityManager->createQuery($dql)->getResult();

foreach ($studentList as $student) {
    echo "ID: $student->id\nNome: $student->name";

    if ($student->phones()->count() > 0) {
        echo PHP_EOL;
        echo "Telefones: ";

        echo implode(', ', $student->phones()
            ->map(fn(Phone $phone) => $phone->number)
            ->toArray());
    }

    if ($student->courses()->count() > 0) {
        echo PHP_EOL;
        echo "Cursos: ";

        echo implode(', ', $student->courses()
            ->map(fn(Course $course) => $course->nome)
            ->toArray());
    }

    echo PHP_EOL . PHP_EOL;
}

// O comando modificado para buscar o número de alunos usando DQL, não SQL.
$dql = 'SELECT COUNT(student) AS numero FROM Alura\\Doctrine\\Entity\\Student student';

// Abaixo uma outra forma diferente de se obter o mesmo resultado:
$studentClass = Student::class; // Conteúdo: "Alura\Doctrine\Entity\Student".
$dql = "SELECT COUNT(student) AS numero FROM $studentClass student";

var_dump($entityManager->createQuery($dql)->getSingleScalarResult());
