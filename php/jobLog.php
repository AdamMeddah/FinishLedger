<?php

require_once __DIR__ . '/database.php';

$orders = [];
$error = null;

try {
    $stmt = db()->query(
        'SELECT InvoiceID, Service_Type, Price, Cost, Client_Name, `Date`
         FROM serviceinfo
         ORDER BY `Date` DESC, InvoiceID DESC'
    );
    $orders = $stmt->fetchAll();
} catch (PDOException $exception) {
    $error = 'Unable to load the order log.';
}

$headers = [
    'InvoiceID' => 'Invoice ID',
    'Service_Type' => 'Service Type',
    'Price' => 'Revenue',
    'Cost' => 'Cost',
    'Client_Name' => 'Client',
    'Date' => 'Date',
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Log | FinishLedger</title>
    <link rel="stylesheet" type="text/css" href="../css/index.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="../index.html">FinishLedger</a>
            <nav class="nav-links" aria-label="Primary navigation">
                <a href="../index.html">Dashboard</a>
                <a href="jobLog.php" class="active">Order Log</a>
                <a href="../html/insights.html">Insights</a>
                <a href="../html/help.html">Help</a>
            </nav>
            <a id="login-btn" href="login.php">Login</a>
        </aside>

        <main class="content-area">
            <section class="page-header">
                <p>Operations</p>
                <h1>Order Log</h1>
            </section>

            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="job-logs">
                        <thead>
                            <tr>
                                <?php foreach ($headers as $label): ?>
                                    <th><?= htmlspecialchars($label) ?></th>
                                <?php endforeach; ?>
                                <th>Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <?php foreach (array_keys($headers) as $key): ?>
                                        <td>
                                            <?php if (in_array($key, ['Price', 'Cost'], true)): ?>
                                                $<?= number_format((float) $order[$key], 2) ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars((string) $order[$key]) ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td>$<?= number_format((float) $order['Price'] - (float) $order['Cost'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <script src="../js/index.js"></script>
</body>
</html>
