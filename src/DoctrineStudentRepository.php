<?php 

namespace Alura\Doctrine;

use Doctrine\ORM\EntityRepository;

class DoctrineStudentRepository extends EntityRepository{
    /**
     * @return Student[]
     */
    public function studentsAndCourses(): array 
    {
        $dql = '
            SELECT 
                student
                , phone
                , course 
            FROM Alura\\Doctrine\\Entity\\Student AS student 
            LEFT JOIN student.phones AS phone
            LEFT JOIN student.courses AS course
        ';

        return $this->getEntityManager()
            ->createQuery($dql)->getResult()
        ;
    }
}
