<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/http.php';

try {
    $stmt = db()->query(
        'SELECT InvoiceID, `Date`, Price, Cost
         FROM serviceinfo
         ORDER BY `Date` ASC, InvoiceID ASC'
    );

    $cumulativeRevenue = 0;
    $cumulativeCost = 0;
    $cumulativeProfit = 0;
    $result = [];

    foreach ($stmt->fetchAll() as $row) {
        $revenue = (float) $row['Price'];
        $cost = (float) $row['Cost'];
        $profit = $revenue - $cost;
        $cumulativeRevenue += $revenue;
        $cumulativeCost += $cost;
        $cumulativeProfit += $profit;

        $result[] = [
            'InvoiceID' => (int) $row['InvoiceID'],
            'Date' => $row['Date'],
            'Revenue' => round($cumulativeRevenue, 2),
            'Cost' => round($cumulativeCost, 2),
            'Profit' => round($cumulativeProfit, 2),
            'RevenueFormatted' => '$' . number_format($cumulativeRevenue, 2),
            'CostFormatted' => '$' . number_format($cumulativeCost, 2),
            'ProfitFormatted' => '$' . number_format($cumulativeProfit, 2),
        ];
    }

    json_response($result);
} catch (PDOException $exception) {
    json_response(['error' => 'Unable to load profit trend data.'], 500);
}
