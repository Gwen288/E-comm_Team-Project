<?php
require_once __DIR__ . '/../../core/core.php';
require_admin();

class AnalyticsDatabase extends Database
{
    public function connection()
    {
        return $this->conn;
    }
}

$database = new AnalyticsDatabase();
$conn = $database->connection();

function analytics_rows($conn, $sql, $startDate = null, $endDate = null)
{
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log('Analytics query prepare failed: ' . $conn->error);
        return [];
    }

    if ($startDate !== null && $endDate !== null) {
        $stmt->bind_param('ss', $startDate, $endDate);
    }

    if (!$stmt->execute()) {
        error_log('Analytics query failed: ' . $stmt->error);
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    return $rows;
}

function analytics_scalar($conn, $sql, $startDate = null, $endDate = null)
{
    $rows = analytics_rows($conn, $sql, $startDate, $endDate);

    if (!$rows) {
        return 0;
    }

    return array_values($rows[0])[0] ?? 0;
}

function analytics_json($value)
{
    return json_encode(
        $value,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    );
}

$today = date('Y-m-d');
$defaultStart = date('Y-m-d', strtotime('-29 days'));

$startDate = $_GET['start_date'] ?? $defaultStart;
$endDate = $_GET['end_date'] ?? $today;

$dateValid = DateTime::createFromFormat('Y-m-d', $startDate)
    && DateTime::createFromFormat('Y-m-d', $endDate)
    && $startDate <= $endDate
    && $endDate <= $today;

if (!$dateValid) {
    $startDate = $defaultStart;
    $endDate = $today;
}

$dateParams = [$startDate, $endDate];

/* =========================================================
   KPI CARDS
========================================================= */

$revenue = (float) analytics_scalar(
    $conn,
    "SELECT COALESCE(SUM(total_amount), 0)
     FROM orders
     WHERE LOWER(payment_status) = 'paid'
       AND order_date BETWEEN ? AND ?",
    ...$dateParams
);

$paidOrders = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM orders
     WHERE LOWER(payment_status) = 'paid'
       AND order_date BETWEEN ? AND ?",
    ...$dateParams
);

$totalOrders = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM orders
     WHERE order_date BETWEEN ? AND ?",
    ...$dateParams
);

$pendingOrders = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM orders
     WHERE LOWER(payment_status) = 'pending'
       AND order_date BETWEEN ? AND ?",
    ...$dateParams
);

$failedOrders = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM orders
     WHERE LOWER(payment_status) = 'failed'
       AND order_date BETWEEN ? AND ?",
    ...$dateParams
);

$averageOrderValue = $paidOrders > 0 ? $revenue / $paidOrders : 0;

$productCount = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM products"
);

$customerCount = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM customer WHERE user_role <> 1"
);

$categoryCount = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM categories"
);

$brandCount = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM brands"
);

/* =========================================================
   REVENUE TREND
========================================================= */

$monthlyRows = analytics_rows(
    $conn,
    "SELECT
        DATE_FORMAT(order_date, '%Y-%m') AS month_key,
        DATE_FORMAT(order_date, '%b %Y') AS month_label,
        SUM(total_amount) AS revenue,
        COUNT(*) AS orders_count
     FROM orders
     WHERE LOWER(payment_status) = 'paid'
       AND order_date BETWEEN ? AND ?
     GROUP BY YEAR(order_date), MONTH(order_date)
     ORDER BY YEAR(order_date), MONTH(order_date)",
    ...$dateParams
);

/* =========================================================
   TOP-SELLING PRODUCTS
========================================================= */

$topProducts = analytics_rows(
    $conn,
    "SELECT
        p.product_title,
        SUM(od.qty) AS units_sold,
        SUM(od.qty * p.product_price) AS estimated_sales
     FROM orderdetails od
     INNER JOIN orders o ON o.order_id = od.order_id
     INNER JOIN products p ON p.product_id = od.product_id
     WHERE LOWER(o.payment_status) = 'paid'
       AND o.order_date BETWEEN ? AND ?
     GROUP BY p.product_id, p.product_title
     ORDER BY units_sold DESC, estimated_sales DESC
     LIMIT 7",
    ...$dateParams
);

/* =========================================================
   CATEGORY PERFORMANCE
========================================================= */

$categoryRows = analytics_rows(
    $conn,
    "SELECT
        c.cat_name,
        SUM(od.qty) AS units_sold,
        SUM(od.qty * p.product_price) AS estimated_sales
     FROM orderdetails od
     INNER JOIN orders o ON o.order_id = od.order_id
     INNER JOIN products p ON p.product_id = od.product_id
     INNER JOIN categories c ON c.cat_id = p.product_cat
     WHERE LOWER(o.payment_status) = 'paid'
       AND o.order_date BETWEEN ? AND ?
     GROUP BY c.cat_id, c.cat_name
     ORDER BY estimated_sales DESC",
    ...$dateParams
);

/* =========================================================
   ORDER PAYMENT STATUS
========================================================= */

$statusRows = analytics_rows(
    $conn,
    "SELECT
        LOWER(payment_status) AS status_name,
        COUNT(*) AS total
     FROM orders
     WHERE order_date BETWEEN ? AND ?
     GROUP BY LOWER(payment_status)",
    ...$dateParams
);

/* =========================================================
   PAYMENT CHANNELS
========================================================= */

$channelRows = analytics_rows(
    $conn,
    "SELECT
        COALESCE(NULLIF(channel, ''), 'Not recorded') AS channel_name,
        COUNT(*) AS total
     FROM payment
     WHERE payment_date BETWEEN ? AND ?
       AND LOWER(status) = 'success'
     GROUP BY COALESCE(NULLIF(channel, ''), 'Not recorded')
     ORDER BY total DESC",
    ...$dateParams
);

/* =========================================================
   CUSTOMER DELIVERY LOCATIONS
========================================================= */

$locationRows = analytics_rows(
    $conn,
    "SELECT
        COALESCE(NULLIF(delivery_city, ''), 'Not recorded') AS city_name,
        COUNT(*) AS order_count
     FROM orders
     WHERE LOWER(payment_status) = 'paid'
       AND order_date BETWEEN ? AND ?
     GROUP BY COALESCE(NULLIF(delivery_city, ''), 'Not recorded')
     ORDER BY order_count DESC
     LIMIT 6",
    ...$dateParams
);

/* =========================================================
   HAIR PROFILE INSIGHTS
========================================================= */

$hairAttributeRows = analytics_rows(
    $conn,
    "SELECT
        ha.attribute_type,
        ha.attribute_name,
        COUNT(*) AS selections
     FROM hair_profile_attributes hpa
     INNER JOIN hair_attributes ha
        ON ha.attribute_id = hpa.attribute_id
     GROUP BY ha.attribute_type, ha.attribute_name
     ORDER BY selections DESC
     LIMIT 10"
);

$profileCount = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM hair_profiles"
);

$recommendationCoverage = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(DISTINCT product_id)
     FROM product_hair_attributes"
);

/* =========================================================
   RECENT ORDERS
========================================================= */

$recentOrders = analytics_rows(
    $conn,
    "SELECT
        o.order_id,
        o.invoice_no,
        o.order_date,
        o.total_amount,
        o.payment_status,
        o.order_status,
        c.customer_name
     FROM orders o
     INNER JOIN customer c ON c.customer_id = o.customer_id
     WHERE o.order_date BETWEEN ? AND ?
     ORDER BY o.order_id DESC
     LIMIT 6",
    ...$dateParams
);

$monthlyLabels = array_column($monthlyRows, 'month_label');

$monthlyRevenue = array_map(
    'floatval',
    array_column($monthlyRows, 'revenue')
);

$monthlyOrderCounts = array_map(
    'intval',
    array_column($monthlyRows, 'orders_count')
);

require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header analytics-heading">
    <div>
        <p class="analytics-eyebrow">STORE PERFORMANCE</p>
        <h1>Analytics Overview</h1>
        <p>Understand sales, orders, products and customer preferences across CrownCare.</p>
    </div>

    <div class="analytics-period-label">
        <span>REPORTING PERIOD</span>
        <strong>
            <?php echo htmlspecialchars(date('M j, Y', strtotime($startDate))); ?>
            –
            <?php echo htmlspecialchars(date('M j, Y', strtotime($endDate))); ?>
        </strong>
    </div>
</div>

<form class="analytics-filter" method="GET">
    <div>
        <label for="start_date">From</label>
        <input
            type="date"
            id="start_date"
            name="start_date"
            value="<?php echo htmlspecialchars($startDate); ?>"
            max="<?php echo $today; ?>"
        >
    </div>

    <div>
        <label for="end_date">To</label>
        <input
            type="date"
            id="end_date"
            name="end_date"
            value="<?php echo htmlspecialchars($endDate); ?>"
            max="<?php echo $today; ?>"
        >
    </div>

    <button type="submit">Apply dates</button>

    <a href="<?php echo BASE_URL; ?>views/admin/analytics.php">
        Reset
    </a>
</form>

<!-- KPI CARDS -->

<div class="analytics-kpi-grid">

    <article class="analytics-kpi kpi-revenue">
        <div class="analytics-kpi-top">
            <span>Paid revenue</span>
            <span class="analytics-icon">GH₵</span>
        </div>

        <strong>
            GH₵<?php echo number_format($revenue, 2); ?>
        </strong>

        <small>Revenue from paid orders in this period</small>
    </article>

    <article class="analytics-kpi kpi-orders">
        <div class="analytics-kpi-top">
            <span>Orders placed</span>
            <span class="analytics-icon">▤</span>
        </div>

        <strong><?php echo number_format($totalOrders); ?></strong>

        <small>
            <?php echo number_format($paidOrders); ?> paid ·
            <?php echo number_format($pendingOrders); ?> pending
        </small>
    </article>

    <article class="analytics-kpi kpi-average">
        <div class="analytics-kpi-top">
            <span>Average paid order</span>
            <span class="analytics-icon">↗</span>
        </div>

        <strong>
            GH₵<?php echo number_format($averageOrderValue, 2); ?>
        </strong>

        <small>Paid revenue divided by paid orders</small>
    </article>

    <article class="analytics-kpi kpi-customers">
        <div class="analytics-kpi-top">
            <span>Customer accounts</span>
            <span class="analytics-icon">♙</span>
        </div>

        <strong><?php echo number_format($customerCount); ?></strong>

        <small>All registered non-admin accounts</small>
    </article>

</div>

<!-- SECONDARY STATISTICS -->

<div class="analytics-secondary-stats">
    <div>
        <span>Products</span>
        <strong><?php echo number_format($productCount); ?></strong>
    </div>

    <div>
        <span>Categories</span>
        <strong><?php echo number_format($categoryCount); ?></strong>
    </div>

    <div>
        <span>Brands</span>
        <strong><?php echo number_format($brandCount); ?></strong>
    </div>

    <div>
        <span>Failed payments</span>
        <strong><?php echo number_format($failedOrders); ?></strong>
    </div>
</div>

<!-- CHARTS -->

<div class="analytics-chart-grid">

    <section class="analytics-panel analytics-panel-wide">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">REVENUE</span>
                <h2>Sales over time</h2>
            </div>

            <span class="analytics-chart-note">Paid orders · GH₵</span>
        </div>

        <div class="analytics-chart-wrap">
            <canvas id="revenueChart"></canvas>
        </div>
    </section>

    <section class="analytics-panel">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">ORDER HEALTH</span>
                <h2>Payment status</h2>
            </div>
        </div>

        <div class="analytics-chart-wrap analytics-doughnut-wrap">
            <canvas id="statusChart"></canvas>
        </div>

        <div class="analytics-legend">
            <span><i class="legend-dot dot-paid"></i>Paid</span>
            <span><i class="legend-dot dot-pending"></i>Pending</span>
            <span><i class="legend-dot dot-failed"></i>Failed</span>
        </div>
    </section>

    <section class="analytics-panel">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">PRODUCTS</span>
                <h2>Top-selling products</h2>
            </div>
        </div>

        <div class="analytics-chart-wrap">
            <canvas id="productsChart"></canvas>
        </div>

        <?php if (empty($topProducts)): ?>
            <p class="analytics-empty">No paid product sales in this period.</p>
        <?php endif; ?>
    </section>

    <section class="analytics-panel">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">CATEGORIES</span>
                <h2>Category sales</h2>
            </div>
        </div>

        <div class="analytics-chart-wrap">
            <canvas id="categoryChart"></canvas>
        </div>

        <?php if (empty($categoryRows)): ?>
            <p class="analytics-empty">No category sales in this period.</p>
        <?php endif; ?>
    </section>

    <section class="analytics-panel">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">PAYMENTS</span>
                <h2>Payment channels</h2>
            </div>
        </div>

        <div class="analytics-chart-wrap">
            <canvas id="channelChart"></canvas>
        </div>

        <?php if (empty($channelRows)): ?>
            <p class="analytics-empty">No successful payment channel records in this period.</p>
        <?php endif; ?>
    </section>

    <section class="analytics-panel">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">DELIVERY LOCATIONS</span>
                <h2>Orders by city</h2>
            </div>
        </div>

        <div class="analytics-chart-wrap">
            <canvas id="locationChart"></canvas>
        </div>

        <?php if (empty($locationRows)): ?>
            <p class="analytics-empty">No paid delivery records in this period.</p>
        <?php endif; ?>
    </section>

</div>

<!-- RECENT ORDERS -->

<section class="analytics-panel analytics-table-panel">

    <div class="analytics-panel-heading">
        <div>
            <span class="analytics-eyebrow">LATEST ACTIVITY</span>
            <h2>Recent orders in this period</h2>
        </div>

        <a href="<?php echo BASE_URL; ?>views/admin/orders.php">
            View all orders →
        </a>
    </div>

    <?php if (empty($recentOrders)): ?>

        <p class="analytics-empty">
            There are no orders in the selected period.
        </p>

    <?php else: ?>

        <div class="admin-table-container">
            <table class="admin-table analytics-table">

                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Order status</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($recentOrders as $order): ?>

                    <tr>
                        <td>#<?php echo (int) $order['invoice_no']; ?></td>

                        <td>
                            <?php echo htmlspecialchars($order['customer_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($order['order_date']); ?>
                        </td>

                        <td>
                            GH₵<?php echo number_format((float) $order['total_amount'], 2); ?>
                        </td>

                        <td>
                            <span class="analytics-status status-<?php echo htmlspecialchars(strtolower($order['payment_status'])); ?>">
                                <?php echo htmlspecialchars($order['payment_status']); ?>
                            </span>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($order['order_status']); ?>
                        </td>

                        <td>
                            <a href="<?php echo BASE_URL; ?>views/admin/order_view.php?id=<?php echo (int) $order['order_id']; ?>">
                                View
                            </a>
                        </td>
                    </tr>

                <?php endforeach; ?>

                </tbody>
            </table>
        </div>

    <?php endif; ?>

</section>

<!-- HAIR PROFILE INSIGHTS -->

<section class="analytics-insights-grid">

    <article class="analytics-panel">

        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">HAIR PROFILES</span>
                <h2>Customer preference insights</h2>
            </div>
        </div>

        <div class="analytics-insight-number">
            <?php echo number_format($profileCount); ?>
            <span>saved hair profiles</span>
        </div>

        <?php if ($hairAttributeRows): ?>

            <div class="analytics-attribute-list">

                <?php foreach ($hairAttributeRows as $attribute): ?>

                    <div>
                        <span>
                            <?php
                            echo htmlspecialchars(
                                ucfirst($attribute['attribute_type'])
                                . ' · '
                                . $attribute['attribute_name']
                            );
                            ?>
                        </span>

                        <strong>
                            <?php echo (int) $attribute['selections']; ?>
                        </strong>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <p class="analytics-empty">
                Preference insights will appear as customers save hair profiles.
            </p>

        <?php endif; ?>

    </article>

    <article class="analytics-panel analytics-coverage">

        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">RECOMMENDATION CATALOGUE</span>
                <h2>Product matching coverage</h2>
            </div>
        </div>

        <div class="analytics-insight-number">
            <?php echo number_format($recommendationCoverage); ?>
            <span>products with hair attributes</span>
        </div>

        <p>
            These are products linked to at least one hair attribute
            for the recommendation feature.
        </p>

    </article>

</section>

<div class="analytics-data-note">
    <strong>How to read these figures</strong>

    <p>
        Revenue and product/category sales use orders marked as paid.
        Product and category sales are estimated from ordered quantities
        multiplied by the products’ current listed prices. Customer accounts
        and catalogue totals are lifetime counts; the date filter applies
        to order and payment activity. Customer registration trends cannot
        yet be calculated because the customer table does not store a
        registration date.
    </p>
</div>

<!-- CHART.JS -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
(function () {

    var palette = [
        '#A84E2B',
        '#7A8B69',
        '#D9A441',
        '#6D7FA8',
        '#B87991',
        '#6B9C91',
        '#9A7BB5'
    ];

    var gridColor = 'rgba(74, 44, 33, 0.08)';

    var money = function (value) {
        return 'GH₵' + Number(value).toLocaleString('en-GH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    Chart.defaults.font.family = "'Nunito Sans', sans-serif";
    Chart.defaults.color = '#6F625A';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;

    /* REVENUE TREND */

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',

        data: {
            labels: <?php echo analytics_json($monthlyLabels); ?>,

            datasets: [{
                label: 'Paid revenue',

                data: <?php echo analytics_json($monthlyRevenue); ?>,

                borderColor: '#A84E2B',
                backgroundColor: 'rgba(168, 78, 43, 0.13)',

                fill: true,
                tension: 0.35,

                pointRadius: 4,
                pointHoverRadius: 6,

                borderWidth: 3
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            interaction: {
                intersect: false,
                mode: 'index'
            },

            plugins: {
                legend: {
                    display: false
                },

                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return money(context.raw);
                        }
                    }
                }
            },

            scales: {
                y: {
                    beginAtZero: true,

                    grid: {
                        color: gridColor
                    },

                    ticks: {
                        callback: function (value) {
                            return 'GH₵' + Number(value).toLocaleString();
                        }
                    }
                },

                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    /* PAYMENT STATUS */

    var statusRows = <?php echo analytics_json($statusRows); ?>;

    var statusMap = {
        paid: 0,
        pending: 0,
        failed: 0
    };

    statusRows.forEach(function (row) {

        if (Object.prototype.hasOwnProperty.call(statusMap, row.status_name)) {
            statusMap[row.status_name] = Number(row.total);
        }

    });

    new Chart(document.getElementById('statusChart'), {

        type: 'doughnut',

        data: {
            labels: ['Paid', 'Pending', 'Failed'],

            datasets: [{
                data: [
                    statusMap.paid,
                    statusMap.pending,
                    statusMap.failed
                ],

                backgroundColor: [
                    '#6F9475',
                    '#E0B65D',
                    '#C97870'
                ],

                borderWidth: 0,
                hoverOffset: 7
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            cutout: '68%',

            plugins: {
                legend: {
                    display: false
                }
            }
        }

    });

    /* TOP-SELLING PRODUCTS */

    var topProducts = <?php echo analytics_json($topProducts); ?>;

    new Chart(document.getElementById('productsChart'), {

        type: 'bar',

        data: {
            labels: topProducts.map(function (row) {
                return row.product_title;
            }),

            datasets: [{
                label: 'Units sold',

                data: topProducts.map(function (row) {
                    return Number(row.units_sold);
                }),

                backgroundColor: palette,
                borderRadius: 7,
                maxBarThickness: 30
            }]
        },

        options: {
            indexAxis: 'y',

            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    display: false
                }
            },

            scales: {
                x: {
                    beginAtZero: true,

                    grid: {
                        color: gridColor
                    },

                    ticks: {
                        precision: 0
                    }
                },

                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }

    });

    /* CATEGORY SALES */

    var categories = <?php echo analytics_json($categoryRows); ?>;

    new Chart(document.getElementById('categoryChart'), {

        type: 'bar',

        data: {
            labels: categories.map(function (row) {
                return row.cat_name;
            }),

            datasets: [{
                label: 'Estimated sales',

                data: categories.map(function (row) {
                    return Number(row.estimated_sales);
                }),

                backgroundColor: palette,
                borderRadius: 7
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    display: false
                },

                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return money(context.raw);
                        }
                    }
                }
            },

            scales: {
                y: {
                    beginAtZero: true,

                    grid: {
                        color: gridColor
                    },

                    ticks: {
                        callback: function (value) {
                            return 'GH₵' + Number(value).toLocaleString();
                        }
                    }
                },

                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }

    });

    /* PAYMENT CHANNELS */

    var channels = <?php echo analytics_json($channelRows); ?>;

    new Chart(document.getElementById('channelChart'), {

        type: 'doughnut',

        data: {
            labels: channels.map(function (row) {
                return row.channel_name;
            }),

            datasets: [{
                data: channels.map(function (row) {
                    return Number(row.total);
                }),

                backgroundColor: palette,
                borderWidth: 0,
                hoverOffset: 7
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            cutout: '62%',

            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }

    });

    /* DELIVERY LOCATIONS */

    var locations = <?php echo analytics_json($locationRows); ?>;

    new Chart(document.getElementById('locationChart'), {

        type: 'bar',

        data: {
            labels: locations.map(function (row) {
                return row.city_name;
            }),

            datasets: [{
                label: 'Paid orders',

                data: locations.map(function (row) {
                    return Number(row.order_count);
                }),

                backgroundColor: '#7A8B69',
                borderRadius: 7
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    display: false
                }
            },

            scales: {
                y: {
                    beginAtZero: true,

                    grid: {
                        color: gridColor
                    },

                    ticks: {
                        precision: 0
                    }
                },

                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }

    });

})();
</script>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>