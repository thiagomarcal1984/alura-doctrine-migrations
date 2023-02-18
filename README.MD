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
