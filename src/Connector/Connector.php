<?php
declare(strict_types = 1);

namespace ORMY\Connector;

use Exception;
use ORMY\Connector\QueryBuilder\MySQLQueryBuilder;
use ORMY\Connector\QueryBuilder\QueryBuilderInterface;
use ORMY\Exceptions\ConnectionException;
use PDO;

/**
 * ORM Connector
 */
class Connector implements ConnectorInterface
{
    /**
     * @var PDO
     */
    private PDO   $pdo;

    /**
     * @var array
     */
    private array $properties;

    /**
     * Конструктор.
     *
     * @param string $dsn
     * @param string $host
     * @param string $pass
     */
    public function __construct(string $dsn, string $host, string $pass)
    {
        try {
            $this->pdo = new PDO($dsn, $host, $pass);
        } catch (\PDOException $exception) {
            throw new ConnectionException('DataBase connection exception, check input args!');
        }
        $this->parseDsn($dsn);
    }

    /**
     * Method parses input dsn
     *
     * @param string $dsn
     */
    private function parseDsn(string $dsn): void
    {
        $this->properties['dsn']  = $dsn;
        $body                     = explode(':', mb_strtolower($dsn), 2);
        $this->properties['type'] = $body[0];
        foreach (explode(';', $body[1]) as $value) {
            $propertyKeyValue                       = explode('=', $value, 2);
            $this->properties[$propertyKeyValue[0]] = $propertyKeyValue[1];
        }
    }

    /**
     * Получить Properties
     *
     * @param string $name
     *
     * @return bool|mixed
     */
    public function getProperty(string $name)
    {
        return $this->properties[$name] ?? false;
    }

    /**
     * Получить Properties
     *
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Получить Pdo
     *
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @param string $sqlquery
     *
     * @return bool|\PDOStatement
     */
    public function prepare(string $sqlquery)
    {
        try {
            return $this->pdo->prepare($sqlquery);
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $sqlquery
     *
     * @return bool
     */
    public function exec(string $sqlquery): bool
    {
        try {
            return $this->pdo->prepare($sqlquery)->execute();
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $sqlquery
     *
     * @param int    $fetchStyle
     *
     * @return array
     */
    public function query(string $sqlquery, int $fetchStyle = PDO::FETCH_ASSOC): array
    {
        try {
            return $this->pdo->query($sqlquery)->fetchAll($fetchStyle);
        } catch (Exception $exception) {
            return ['result' => false];
        }
    }

    /**
     * Method returns query builder
     *
     * @return QueryBuilderInterface
     */
    public function createQueryBuilder(): QueryBuilderInterface
    {
        return new MySQLQueryBuilder($this);
    }

    /**
     * Получить DBName
     *
     * @return string
     */
    public function getDBName(): string
    {
        return $this->getProperty('dbname');
    }
}
