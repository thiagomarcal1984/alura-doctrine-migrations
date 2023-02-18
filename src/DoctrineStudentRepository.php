<?php 

namespace Alura\Doctrine;

use Doctrine\ORM\EntityRepository;

class DoctrineStudentRepository extends EntityRepository{
    /**
     * @return Student[]
     */
    public function studentsAndCourses(): array 
    {
        return $this->createQueryBuilder(alias: 'student')
            ->addSelect(select: 'phone')
            ->addSelect(select: 'course')
            ->leftJoin(join: 'student.phones', alias: 'phone')
            ->leftJoin(join: 'student.courses', alias: 'course')
            ->getQuery()
            ->getResult()
        ;
    }
}
