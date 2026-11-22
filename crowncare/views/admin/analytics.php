<?php
require_once __DIR__ . '/../../core/core.php';
require_admin();

/*
 * Admin analytics dashboard: sales, orders and quiz figures, last 30 days by
 * default. The queries are run here in PHP; Chart.js draws them at the bottom.
 */

/* Database keeps its connection private; the queries below need it directly. */
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
    // PHP 8.1+ throws on a failed query; log it and leave that chart empty rather than blank the page.
    try {
        return analytics_run($conn, $sql, $startDate, $endDate);
    } catch (Throwable $e) {
        error_log('Analytics query failed: ' . $e->getMessage());
        return [];
    }
}

function analytics_run($conn, $sql, $startDate = null, $endDate = null)
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

/** Escapes the values before they are printed inside a <script> tag. */
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

// Ignore bad dates from the address bar and fall back to the default window.
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
     FROM cc_orders
     WHERE LOWER(payment_status) = 'paid'
       AND order_date BETWEEN ? AND ?",
    ...$dateParams
);

$paidOrders = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM cc_orders
     WHERE LOWER(payment_status) = 'paid'
       AND order_date BETWEEN ? AND ?",
    ...$dateParams
);

$totalOrders = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM cc_orders
     WHERE order_date BETWEEN ? AND ?",
    ...$dateParams
);

$pendingOrders = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM cc_orders
     WHERE LOWER(payment_status) = 'pending'
       AND order_date BETWEEN ? AND ?",
    ...$dateParams
);

$failedOrders = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM cc_orders
     WHERE LOWER(payment_status) = 'failed'
       AND order_date BETWEEN ? AND ?",
    ...$dateParams
);

// Revenue below only counts paid orders, so the average uses paid orders too.
$averageOrderValue = $paidOrders > 0 ? $revenue / $paidOrders : 0;

$productCount = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM cc_products"
);

$customerCount = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM cc_customer WHERE user_role <> 1"
);

$categoryCount = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM cc_categories"
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
     FROM cc_orders
     WHERE LOWER(payment_status) = 'paid'
       AND order_date BETWEEN ? AND ?
     GROUP BY DATE_FORMAT(order_date, '%Y-%m'), DATE_FORMAT(order_date, '%b %Y')
     ORDER BY month_key",
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
        SUM(od.qty * COALESCE(od.unit_price, p.product_price)) AS estimated_sales
     FROM cc_orderdetails od
     INNER JOIN cc_orders o ON o.order_id = od.order_id
     INNER JOIN cc_products p ON p.product_id = od.product_id
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
        SUM(od.qty * COALESCE(od.unit_price, p.product_price)) AS estimated_sales
     FROM cc_orderdetails od
     INNER JOIN cc_orders o ON o.order_id = od.order_id
     INNER JOIN cc_products p ON p.product_id = od.product_id
     INNER JOIN cc_categories c ON c.cat_id = p.product_cat
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
     FROM cc_orders
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
     FROM cc_payment
     WHERE payment_date BETWEEN ? AND ?
       AND LOWER(status) = 'success'
     GROUP BY channel_name
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
     FROM cc_orders
     WHERE LOWER(payment_status) = 'paid'
       AND order_date BETWEEN ? AND ?
     GROUP BY city_name
     ORDER BY order_count DESC
     LIMIT 6",
    ...$dateParams
);

/* =========================================================
   HAIR PROFILE INSIGHTS
   These come from saved quiz profiles, not from the date filter.
========================================================= */

$hairAttributeRows = analytics_rows(
    $conn,
    "SELECT
        ha.attribute_type,
        ha.attribute_name,
        COUNT(*) AS selections
     FROM cc_hair_profile_attributes hpa
     INNER JOIN cc_hair_attributes ha
        ON ha.attribute_id = hpa.attribute_id
     GROUP BY ha.attribute_type, ha.attribute_name
     ORDER BY selections DESC
     LIMIT 10"
);

$profileCount = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(*) FROM cc_hair_profiles"
);

$recommendationCoverage = (int) analytics_scalar(
    $conn,
    "SELECT COUNT(DISTINCT product_id)
     FROM cc_product_hair_attributes"
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
     FROM cc_orders o
     INNER JOIN cc_customer c ON c.customer_id = o.customer_id
     WHERE o.order_date BETWEEN ? AND ?
     ORDER BY o.order_id DESC
     LIMIT 6",
    ...$dateParams
);

/* ---------- Chart data: PHP rows become plain arrays for the script below ---------- */
$monthlyLabels = array_column($monthlyRows, 'month_label');

$monthlyRevenue = array_map(
    'floatval',
    array_column($monthlyRows, 'revenue')
);

$monthlyOrderCounts = array_map(
    'intval',
    array_column($monthlyRows, 'orders_count')
);

$pageTitle = 'Analytics';
require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Analytics</h1>
        <p><?php echo e(date('j M Y', strtotime($startDate)) . ' to ' . date('j M Y', strtotime($endDate))); ?></p>
    </div>
</div>

<form class="cc-date-filter" method="GET">
    <label>
        <span>From</span>
        <input type="date" name="start_date" value="<?php echo e($startDate); ?>" max="<?php echo e($today); ?>">
    </label>
    <label>
        <span>To</span>
        <input type="date" name="end_date" value="<?php echo e($endDate); ?>" max="<?php echo e($today); ?>">
    </label>
    <button type="submit" class="admin-btn admin-btn-primary">Apply</button>
    <a class="admin-btn" href="<?php echo BASE_URL; ?>views/admin/analytics.php">Reset</a>
</form>

<div class="admin-stat-grid cc-stat-grid">
    <div class="admin-stat-card">
        <span>Paid revenue</span>
        <strong><?php echo e(cc_money($revenue)); ?></strong>
    </div>
    <div class="admin-stat-card">
        <span>Orders placed</span>
        <strong><?php echo number_format($totalOrders); ?></strong>
    </div>
    <div class="admin-stat-card">
        <span>Paid orders</span>
        <strong><?php echo number_format($paidOrders); ?></strong>
    </div>
    <div class="admin-stat-card">
        <span>Average paid order</span>
        <strong><?php echo e(cc_money($averageOrderValue)); ?></strong>
    </div>
    <div class="admin-stat-card">
        <span>Failed payments</span>
        <strong><?php echo number_format($failedOrders); ?></strong>
    </div>
    <div class="admin-stat-card">
        <span>Customers</span>
        <strong><?php echo number_format($customerCount); ?></strong>
    </div>
</div>

<section class="admin-card">
    <div class="admin-card-header">
        <h2>Sales over time</h2>
    </div>
    <div class="cc-chart cc-chart--wide"><canvas id="revenueChart"></canvas></div>
</section>

<div class="cc-chart-grid">
    <section class="admin-card">
        <div class="admin-card-header"><h2>Payment status</h2></div>
        <div class="cc-chart"><canvas id="statusChart"></canvas></div>
    </section>

    <section class="admin-card">
        <div class="admin-card-header"><h2>Top-selling products</h2></div>
        <?php if (empty($topProducts)): ?>
            <p class="admin-empty">No sales in this period.</p>
        <?php else: ?>
            <div class="cc-chart"><canvas id="productsChart"></canvas></div>
        <?php endif; ?>
    </section>

    <section class="admin-card">
        <div class="admin-card-header"><h2>Sales by category</h2></div>
        <?php if (empty($categoryRows)): ?>
            <p class="admin-empty">No sales in this period.</p>
        <?php else: ?>
            <div class="cc-chart"><canvas id="categoryChart"></canvas></div>
        <?php endif; ?>
    </section>

    <section class="admin-card">
        <div class="admin-card-header"><h2>Payment methods</h2></div>
        <?php if (empty($channelRows)): ?>
            <p class="admin-empty">No payments in this period.</p>
        <?php else: ?>
            <div class="cc-chart"><canvas id="channelChart"></canvas></div>
        <?php endif; ?>
    </section>

    <section class="admin-card">
        <div class="admin-card-header"><h2>Orders by area</h2></div>
        <?php if (empty($locationRows)): ?>
            <p class="admin-empty">No paid orders in this period.</p>
        <?php else: ?>
            <div class="cc-chart"><canvas id="locationChart"></canvas></div>
        <?php endif; ?>
    </section>

    <section class="admin-card">
        <div class="admin-card-header"><h2>Hair quiz answers</h2></div>
        <?php if ($hairAttributeRows): ?>
            <div class="admin-table-container">
                <table class="admin-table">
                    <tbody>
                        <?php foreach ($hairAttributeRows as $attribute): ?>
                            <tr>
                                <td><?php echo e(ucfirst($attribute['attribute_type']) . ': ' . $attribute['attribute_name']); ?></td>
                                <td class="cc-num"><?php echo (int) $attribute['selections']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="admin-empty">No saved hair profiles yet.</p>
        <?php endif; ?>
    </section>
</div>

<section class="admin-card">
    <div class="admin-card-header">
        <h2>Recent orders</h2>
        <a href="<?php echo BASE_URL; ?>views/admin/orders.php">View all</a>
    </div>

    <?php if (empty($recentOrders)): ?>
        <p class="admin-empty">No orders in this period.</p>
    <?php else: ?>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><?php echo e(cc_order_number($order)); ?></td>
                            <td><?php echo e($order['customer_name']); ?></td>
                            <td><?php echo e(date('j M Y', strtotime($order['order_date']))); ?></td>
                            <td><?php echo e(cc_money($order['total_amount'])); ?></td>
                            <td><span class="<?php echo cc_order_status_class($order['order_status']); ?>"><?php echo e(cc_order_status_label($order['order_status'])); ?></span></td>
                            <td><a href="<?php echo BASE_URL; ?>views/admin/order_view.php?id=<?php echo (int) $order['order_id']; ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<!-- CHART.JS -->

<script src="<?php echo BASE_URL; ?>js/vendor/chart.umd.min.js"></script>

<script>
(function () {
    if (typeof Chart === 'undefined') return;

    // Creates a chart only when its canvas is on the page (empty charts are left out).
    function makeChart(id, config) {
        var canvas = document.getElementById(id);
        return canvas ? new Chart(canvas, config) : null;
    }


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

    Chart.defaults.font.family = "'Instrument Sans', 'Nunito Sans', sans-serif";
    Chart.defaults.color = '#6F625A';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;

    /* REVENUE TREND */

    makeChart('revenueChart', {
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

    // The query only returns statuses that occur; fill in the missing ones with zero.
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

    makeChart('statusChart', {

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

    makeChart('productsChart', {

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

    makeChart('categoryChart', {

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

    makeChart('channelChart', {

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

    makeChart('locationChart', {

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