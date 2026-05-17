<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/http.php';

try {
    $stmt = db()->query(
        'SELECT
             Service_Type AS ServiceType,
             COUNT(*) AS OrderCount,
             SUM(Price) AS TotalRevenue,
             SUM(Cost) AS TotalCost,
             SUM(Price - Cost) AS TotalProfit
         FROM serviceinfo
         GROUP BY Service_Type
         ORDER BY TotalProfit DESC'
    );

    $result = array_map(static function (array $row): array {
        $row['OrderCount'] = (int) $row['OrderCount'];
        $row['TotalRevenue'] = round((float) $row['TotalRevenue'], 2);
        $row['TotalCost'] = round((float) $row['TotalCost'], 2);
        $row['TotalProfit'] = round((float) $row['TotalProfit'], 2);
        $row['TotalProfitFormatted'] = '$' . number_format($row['TotalProfit'], 2);
        return $row;
    }, $stmt->fetchAll());

    json_response($result);
} catch (PDOException $exception) {
    json_response(['error' => 'Unable to load service profit data.'], 500);
}
