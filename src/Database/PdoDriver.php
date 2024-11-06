<?php

namespace LightWeight\Database;

use LightWeight\Database\Contracts\DatabaseDriverContract;
use PDO;

class PdoDriver implements DatabaseDriverContract
{
    protected ?PDO $pdo;

    /**
     * @inheritDoc
     */
    public function close()
    {
        $this->pdo = null;
    }

    /**
     * @inheritDoc
     */
    public function connect(string $protocol, string $host, int $port, string $database, string $username, string $password)
    {
        $dsn = "$protocol:host=$host;port=$port;dbname=$database";
        $this->pdo = new PDO($dsn, $username, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
    /**
     * @inheritDoc
     */
    public function statement(string $query, array $bind = []): array
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($bind);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * @inheritDoc
     */
    public function execute(string $query, array $bind = []): bool
    {
        $statement = $this->pdo->prepare($query);
        return $statement->execute($bind);
    }
}
