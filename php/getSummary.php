<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/http.php';

try {
    $summary = db()->query(
        'SELECT
             COUNT(*) AS orderCount,
             COALESCE(SUM(Price), 0) AS totalRevenue,
             COALESCE(SUM(Cost), 0) AS totalCost,
             COALESCE(SUM(Price - Cost), 0) AS totalProfit
         FROM serviceinfo'
    )->fetch();

    $latest = db()->query(
        'SELECT InvoiceID, Service_Type, Price, Cost, Client_Name, `Date`
         FROM serviceinfo
         ORDER BY `Date` DESC, InvoiceID DESC
         LIMIT 5'
    )->fetchAll();

    json_response([
        'orderCount' => (int) $summary['orderCount'],
        'totalRevenue' => round((float) $summary['totalRevenue'], 2),
        'totalCost' => round((float) $summary['totalCost'], 2),
        'totalProfit' => round((float) $summary['totalProfit'], 2),
        'latestOrders' => $latest,
    ]);
} catch (PDOException $exception) {
    json_response(['error' => 'Unable to load dashboard summary.'], 500);
}
