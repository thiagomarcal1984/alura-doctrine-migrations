<?php

declare(strict_types=1);

namespace Alura\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230218133847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Course (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nome VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE Phone (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, student_id INTEGER DEFAULT NULL, number VARCHAR(255) NOT NULL, CONSTRAINT FK_858EB8D9CB944F1A FOREIGN KEY (student_id) REFERENCES Student (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_858EB8D9CB944F1A ON Phone (student_id)');
        $this->addSql('CREATE TABLE Student (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE student_course (student_id INTEGER NOT NULL, course_id INTEGER NOT NULL, PRIMARY KEY(student_id, course_id), CONSTRAINT FK_98A8B739CB944F1A FOREIGN KEY (student_id) REFERENCES Student (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_98A8B739591CC992 FOREIGN KEY (course_id) REFERENCES Course (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_98A8B739CB944F1A ON student_course (student_id)');
        $this->addSql('CREATE INDEX IDX_98A8B739591CC992 ON student_course (course_id)');
        $this->addSql('DROP TABLE teste');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE teste (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, coluna_teste VARCHAR(255) NOT NULL COLLATE "BINARY")');
        $this->addSql('DROP TABLE Course');
        $this->addSql('DROP TABLE Phone');
        $this->addSql('DROP TABLE Student');
        $this->addSql('DROP TABLE student_course');
    }
}
