<?php

namespace SingletonPattern\Services;

use SingletonPattern\Exceptions\DbException;

class Db
{
    private static $instance; // Bieżący obiekt

    /** @var \PDO */
    private $pdo;

    private function __construct() // konstruktor jest prywatny, więc niemożliwe jest utworzenie obiektu poza klasę
    {
        $dbOptions = (require __DIR__ . '/../../settings.php')['db'];

        try {
            $this->pdo = new \PDO(
                'mysql:host=' . $dbOptions['host'] . ';dbname=' . $dbOptions['dbname'],
                $dbOptions['user'],
                $dbOptions['password']
            );
            $this->pdo->exec('SET NAMES UTF8');
        } catch (\PDOException $e) {
            throw new DbException('Error while connecting to database:');
        }
    }

    protected function __clone() {
    }

    public function query(string $sql, array $params = [], string $className = 'stdClass'): ?array // Biznes logika
    {
        $sth = $this->pdo->prepare($sql);
        $result = $sth->execute($params);

        if (false === $result) {
            return null;
        }

        return $sth->fetchAll(\PDO::FETCH_CLASS, $className);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getLastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }
}
