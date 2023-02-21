 # Conhecendo o conceito

 Para usar o Doctrine na linha de comando após instalá-lo com o Composer, use o comando:
 ```
 php bin\doctrine.php
 ```

# Instalando o componente

 O Doctrine é uma coleção de vários projetos, e um deles é o projeto de migrations. Para instalá-lo, inclua a dependência no composer.json:
 ```
 composer update
 composer require doctrine/migrations
 ```
O link a seguir contém instruções para usar o Doctrine Migrations: https://www.doctrine-project.org/projects/doctrine-migrations/en/3.6/reference/introduction.html#introduction

O arquivo `cli-config.php` será apagado do projeto. Ele é legível pelo CLI do Doctrine Migrations, mas ele não está completo, e o arquivo `bin\doctrine.php`, que é usado para as versões mais recentes do Doctrine, é suficiente para o CLI do Doctrine Migrations funcionar. Completar o `cli-config.php` vai dar mais trabalho para usar o Doctrine Migrations do que benefícios.

Para rodar o CLI do Doctrine Migrations, use:
```
php .\vendor\bin\doctrine-migrations
```

Comando para aplicar as migrations:
```
php .\vendor\bin\doctrine-migrations migrations:migrate
```
O comando vai falhar porque não temos um arquivo de configuração necessário. Na documentação do Doctrine Migrations há exemplos de arquivos `migrations` nos formatos `php`, `yaml`, `xml` `json`. Aqui usaremos o arquivo `migrations.php`, na raiz do projeto.

Além do arquivo `migrations.php`, precisamos também do arquivo `migrations-db.php`. Esse arquivo vai ter os parâmetros de acesso ao banco, o mesmo do retorno do arquivo `EntityManagerCreator.php`.

Após a criação desses dois arquivo, o comando `migrations:migrate` do CLI do Doctrine Migrations não vai funcionar porque... não há migrações. Primeiro criamos as migrações, depois as aplicamos no banco.

# Criando Migrations

Para criar uma migration no diretório indicado no arquivo `migrations.php`, use o comando:
```
php .\vendor\bin\doctrine-migrations migrations:generate
```

Para rodar a migration pontualmente, use o comando abaixo com o parâmetro `--up`:
```
php .\vendor\bin\doctrine-migrations migrations:execute --up 'Namespace\Migrations\VersionYYYYMMDD???'
```

Para reverter a migration pontual, use o comando abaixo com o parâmetro `--down`:

```
php .\vendor\bin\doctrine-migrations migrations:execute --down 'Namespace\Migrations\VersionYYYYMMDD???'
```

A migration criada terá 3 métodos:
1. `getDescription()` vai retornar a Descrição dessa migration;
2. `up(Schema $schema)` vai aplicar as mudanças que você quer fazer;
3. `down(Schema $schema)` reverte as mudanças que você quer fazer.

O desenvolvedor é quem tem o trabalho de desenvolver esses métodos. Abaixo temos um exemplo simples para implementar as classes de migration:
```php
<?php

declare(strict_types=1);

namespace Alura\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230218125658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Criação de uma tabela de testes.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE teste (
                id INTEGER PRIMARY KEY
                , coluna_teste VARCHAR(255)
            )'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE teste;');
    }
}

```

Mesmo exemplo, mas usando o objeto `$schema`:
```php
<?php

declare(strict_types=1);

namespace Alura\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230218125658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Criação de uma tabela de testes.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('teste');
        $table->addColumn('id', 'integer')->setAutoincrement(true);
        $table->addColumn('coluna_teste', 'string');
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('teste');
    }
}
```

# Conectando com o ORM

Sabe o arquivo `cli-config.php`? ^^'Vamos recriá-lo com base na documentação do Doctrine Migrations. Há dois modelos diferentes na documentação: um que aceita usar o Doctrine ORM e o primeiro que não aceita.

```php
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

return DependencyFactory::fromEntityManager(
    $config, 
    new ExistingEntityManager($entityManager)
);
```

Sabe o arquivo `migrations-db.php` que criamos? ^^'Ele não é mais necessário, por causa do arquivo `cli-config.php`.

Repita os comandos de migration pra ver se o código voltou a funcionar. Há alguns comandos novos na CLI do Doctrine Migrations quando usamos esse novo `cli-config.php`. Por exemplo, o `migrations:diff`:
```
php .\vendor\bin\doctrine-migrations migrations:diff
```

Esse comando gera uma migração que mostra a diferença entre como está o DB atual e como estão as classes de Entidade!

# Revisando o código
Vamos pegar o código de `bin\insert-student.php` como exemplo de como funciona a comunicação com o banco de dados usando o ORM:
```php
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
```

# Analisando o SQL executado
O `EntityManagerCreator.php` foi modificado para exibir a saída SQL no console: 

```php
<?php

namespace Alura\Doctrine\Helper;

use Doctrine\DBAL\Logging\Middleware;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

class EntityManagerCreator
{
    public static function createEntityManager(): EntityManager
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__ . "/.."],
            true
        );

        $consoleOutput = new ConsoleOutput(ConsoleOutput::VERBOSITY_DEBUG);
        $consoleLogger = new ConsoleLogger($consoleOutput);
        $logMiddleware = new Middleware($consoleLogger);
        
        $config->setMiddlewares([
            $logMiddleware,
        ]);

        $conn = [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__ . '/../../db.sqlite',
        ];

        return EntityManager::create($conn, $config);
    }
}
```
Do jeito como o código está atualmente, cada vez que uma entidade é mencionada um SQL simples é executado. Isso é ruim porque muitos comandos SQL são disparados ao invés de um único comando mais elaborado.

# Conhecendo DQL
Por ora, o problema de execução de múltiplos SQL simples continua. A diferença está na forma como os objetos Student são recuperados:

```php
$studentRepository = $entityManager->getRepository(Student::class);
$studentList = $studentRepository->findAll();
```
O código acima recupera as entidades por meio de um repositório. Já o código abaixo recupera as entidades por meio de uma query DQL (Doctrine Query Language):
```php
$dql = 'SELECT student FROM Alura\\Doctrine\\Entity\\Student as student';
$studentList = $entityManager->createQuery($dql)->getResult();
```

# Buscando dados simples
As queries DQL tem alguns métodos.
1. `getResult()` retorna um array bidimensional de linhas e colunas do resultado.
2. `getSingleResult()` retorna um array unidimensional contendo as colunas do resultado único.
3. `getSingleScalarResult()` retorna um array unidimensional contendo o tipo primitivo retornado no resultado.

Código modificado:
```php
// O comando modificado para buscar o número de alunos usando DQL, não SQL.
$dql = 'SELECT COUNT(student) AS numero FROM Alura\\Doctrine\\Entity\\Student student';

// Abaixo uma outra forma diferente de se obter o mesmo resultado:
$studentClass = Student::class; // Conteúdo: "Alura\Doctrine\Entity\Student".
$dql = "SELECT COUNT(student) AS numero FROM $studentClass student";

var_dump($entityManager->createQuery($dql)->getSingleScalarResult());
// Resultado: int(*número_de_estudantes*)
```

É possível executar operações CRUD (update, delete) com DQL! 

# Fetch Eager
Por padrão, as entidades obtidas por meio de relacionamentos só são realizados quando necessário (o Fetch Lazy). Mas há vezes em que é necessário buscar essas entidades mesmo sem uso imediato (o Fetch Eager).

Essa configuração pode ser feita direto na entidade:
```php
use Doctrine\ORM\Mapping\{ Entity, OneToMany };

#[Entity]
class Student
{
    ...

    #[OneToMany(
        mappedBy: "student",
        targetEntity: Phone::class,
        cascade: ["persist", "remove"],
        fetch: 'EAGER' // Força a busca dos telefones.
    )]
    private Collection $phones;

    ...
}
```

# Eager join
A solução para o problema de múltiplas execuções de SQL simples é: use joins na DQL.

Por que no código se usam LEFT JOINS? Porque se o lado direito da relação for vazio, as linhas do lado esquerdo não são retornadas. Por exemplo, alunos sem telefone não seriam incluídos no resultado.

```php
$dql = '
    SELECT student, phone, course 
    FROM Alura\\Doctrine\\Entity\\Student AS student 
    LEFT JOIN student.phones AS phone
    LEFT JOIN student.courses AS course
';

$studentList = $entityManager->createQuery($dql)->getResult();
```
# Criando um repositório
As entidades do Doctrine podem informar qual classe representa seu repositório. Às vezes precisamos de um repositório com mais métodos além daqueles que já vem nos repositórios padrão do Doctrine.

Abaixo segue o código do repositório personalizado:
```php
<?php 

namespace Alura\Doctrine;

use Doctrine\ORM\EntityRepository;

class DoctrineStudentRepository extends EntityRepository{
    public function studentsAndCourses()
    {
        $dql = '
            SELECT student, phone, course 
            FROM Alura\\Doctrine\\Entity\\Student AS student 
            LEFT JOIN student.phones AS phone
            LEFT JOIN student.courses AS course
        ';

        return $this->getEntityManager()
            ->createQuery($dql)->getResult()
        ;
    }
}
```
E o código da entidade em que se indica o repositório que será usado:
```php
<?php

namespace Alura\Doctrine\Entity;

use Alura\Doctrine\DoctrineStudentRepository;
use Doctrine\ORM\Mapping\Entity;

#[Entity(repositoryClass: DoctrineStudentRepository::class)]
class Student
{ ... }
```
Código do cliente do repositório personalizado:
```php
$studentRepository = $entityManager->getRepository(Student::class);
$studentList = $studentRepository->studentsAndCourses();
```

# Usando QueryBuilder
QueryBuilder é um recurso do Doctrine que facilita a criação de DQL. Escrever as DQL direto pode ser mais rápido, mas os QueryBuilders são úteis para construir queries se certas condições forem satisfeitas (por exemplo, filtro e ordenação).

```php
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
            // cláusula FROM implícita no repositório.
            ->leftJoin(join: 'student.phones', alias: 'phone')
            ->leftJoin(join: 'student.courses', alias: 'course')
            ->getQuery() // Retorna a query depois de construída.
            ->getResult()
        ;
        /** // Mesma coisa que:
        * return $this->getEntityManager()->createQuery('
        *     SELECT student, phone, course 
        *     FROM Alura\\Doctrine\\Entity\\Student AS student 
        *     LEFT JOIN student.phones AS phone
        *     LEFT JOIN student.courses AS course
        * ')->getResult();
        */
    }
}
```
# Para saber mais: parâmetros
Para buscar um aluno com ID = 4, por exemplo, nós faríamos:
```php
$id = 4;
$dql = '
    SELECT student 
    FROM Alura\Doctrine\Entity\Student student 
    WHERE student.id = ?1
';
$query = $entityManager->createQuery($dql);
$query->setParameter(1, $id);
$student = $query->getSingleResult();
```

Dessa forma nós buscamos apenas um aluno (por isso o Single no método). Além de parâmetros **posicionais** (?1, ?2, etc) nós também podemos usar parâmetros **nomeados**:

```php
$id = 4;
$dql = '
    SELECT student 
    FROM Alura\Doctrine\Entity\Student student 
    WHERE student.id = :id
';
$query = $entityManager->createQuery($dql);
$query->setParameter('id', $id);
$student = $query->getSingleResult();
```
Lembrando que a query poderia ser criada também usando um query builder dentro de um repositório ao invés de chamar o createQuery do EntityManager.

# Tipos diferentes de cache

Há vários caches diferentes no Doctrine:
- `ORMSetup::createAttributeMetadataConfiguration`, método disponível na classe `EntityManagerCreator`, retorna um `Doctrine\ORM\Configuration`, cujos dados podem ser armazenados em cache. Esse é o chanado *cache de mapeamento (metadata)*;
- a conversão de queries DQL para SQL tem seu custo: as queries DQL convertidas para SQL podem ser armazenadas em cache. Esse é o chamado *cache de query*;
- os **resultados** das queries também podem ser armazenados em cache e por um tempo determinado: para isso, use os métodos `setCacheable(true)` e `setLifetime($integer)` nas queries. **Porém** é necessário invalidar esse cache caso novos registros sejam inseridos, e o cache precisa ser compartilhado (via Redis ou Memcached, por exemplo). Esse é o chamado *cache de resultados*;
- O Doctrine possui um cache chamado L2C (Level Two Cache). Esse objeto é complexo de configurar, mas serve para invalidar caches que possam estar desatualizados.

# Metadata cache
Código para implementação do metadata cache:
```php
<?php

namespace Alura\Doctrine\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
...

class EntityManagerCreator
{
    public static function createEntityManager(): EntityManager
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__ . "/.."],
            true
        );

        ...

        $config->setMetadataCache(new PhpFilesAdapter(
            namespace: 'metadata_cache', // Pasta que contém o cache.
            // defaultLifetime: 0, // Não expira.
            directory: __DIR__ . '/../../var/cache' // Caminho para o namespace.
        ));

        $conn = [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__ . '/../../db.sqlite',
        ];

        return EntityManager::create($conn, $config);
    }
}
```
