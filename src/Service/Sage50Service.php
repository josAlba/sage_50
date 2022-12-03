<?php

namespace App\Service;

use App\Entity\Client\Sage50\Stocks;
use App\Repository\Client\Sage50\StocksRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use function sqlsrv_errors;
use function sqlsrv_free_stmt;
use function sqlsrv_query;

class Sage50Service
{
    public function __construct(
        private readonly StocksRepository $stocksRepository,
        private readonly EntityManagerInterface $entityManager,
        private $serverName,
        private $dataBase
    )
    {
    }

    public function updateStocks(): void
    {
        $connectionInfo = array("Database" => $this->dataBase);
        $conn = \sqlsrv_connect($this->serverName, $connectionInfo);

        if (!$conn) {
            throw new RuntimeException(sqlsrv_errors());
        }

        echo "\n CONECTADO AL SQL SERVER";

        $sql = "SELECT ARTICULO FROM stocks2 GROUP BY ARTICULO;";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) {
            throw new RuntimeException(sqlsrv_errors());
        }

        while ($rowReference = \sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $reference = $rowReference['ARTICULO'];

            echo "\n REFERENCE:" . $reference;

            $sql = "SELECT FINAL FROM stocks2 WHERE ARTICULO='" . $reference . "';";
            $stmt2 = sqlsrv_query($conn, $sql);
            if ($stmt2 === false) {
                continue;
            }

            $stock = 0;

            while ($row = \sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
                $stock += (int)$row['FINAL'];
            }

            echo ":" . $stock;

            $this->saveStock($reference, $stock);

            sqlsrv_free_stmt($stmt2);
        }

        sqlsrv_free_stmt($stmt);
    }

    private function saveStock(string $reference, int $stock): void
    {
        $article = $this->stocksRepository->findOneBy(['reference' => $reference]);

        if ($article === null) {
            $article = new Stocks();
            $article->setReference($reference);
        }

        $article->setStock($stock);
        $article->setDateUpdate(date_create_from_format('Y-m-d H:i:s', date('Y-m-d H:i:s')));

        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }
}