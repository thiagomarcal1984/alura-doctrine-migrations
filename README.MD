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
