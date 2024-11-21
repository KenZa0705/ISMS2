<?php
require_once '../../login/dbh.inc.php';
session_start();

// Check authentication
if (!isset($_SESSION['user'])) {
    header("Location: ../../login/login.php");
    exit();
}

date_default_timezone_set('Asia/Manila');

try {
    // Summary Statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM student");
    $stmt->execute();
    $total_students = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM announcement");
    $stmt->execute();
    $total_announcements = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM department");
    $stmt->execute();
    $total_departments = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sms_log WHERE status = 'sent'");
    $stmt->execute();
    $total_sms = $stmt->fetchColumn();

    // Department Statistics
    $stmt = $pdo->prepare("
        SELECT d.department_name, COUNT(ad.announcement_id) as count
        FROM department d
        LEFT JOIN announcement_department ad ON d.department_id = ad.department_id
        GROUP BY d.department_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $dept_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Year Level Statistics
    $stmt = $pdo->prepare("
        SELECT yl.year_level, COUNT(s.student_id) as count
        FROM year_level yl
        LEFT JOIN student s ON yl.year_level_id = s.year_level_id
        GROUP BY yl.year_level
        ORDER BY yl.year_level
    ");
    $stmt->execute();
    $year_level_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Monthly Trends (Last 6 months)
    $stmt = $pdo->prepare("
        SELECT 
            DATE_TRUNC('month', updated_at) as month,
            COUNT(*) as count
        FROM announcement
        WHERE updated_at >= NOW() - INTERVAL '6 months'
        GROUP BY DATE_TRUNC('month', updated_at)
        ORDER BY month
    ");
    $stmt->execute();
    $monthly_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // SMS Status Distribution
    $stmt = $pdo->prepare("
        SELECT 
            status,
            COUNT(*) as count,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sms_log), 1) as percentage
        FROM sms_log
        GROUP BY status
    ");
    $stmt->execute();
    $sms_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    exit('Database error occurred');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Analytics Dashboard - ISMS Portal</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include '../../cdn/head.html'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f6fa;
            color: #2d3436;
        }

        .dashboard-wrapper {
            padding: 2rem;
            animation: fadeIn 0.5s ease-in-out;
        }

        .dashboard-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-title {
            font-weight: 600;
            color: #2d3436;
            font-size: 1.75rem;
            margin: 0;
        }

        .date-info {
            color: #636e72;
            font-size: 0.9rem;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            padding: 1.5rem;
            height: 100%;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .stats-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stats-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .stats-info h3 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .stats-info p {
            color: #636e72;
            margin: 0;
            font-size: 0.9rem;
        }

        .chart-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: none;
            transition: all 0.3s ease;
        }

        .chart-card:hover {
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3436;
            margin: 0;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .bg-purple {
            background-color: #6c5ce7;
        }

        .bg-blue {
            background-color: #0984e3;
        }

        .bg-green {
            background-color: #00b894;
        }

        .bg-orange {
            background-color: #e17055;
        }

        @media (max-width: 768px) {
            .dashboard-wrapper {
                padding: 1rem;
            }

            .stats-card {
                margin-bottom: 1rem;
            }

            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="dashboard-wrapper">
        <div class="dashboard-header">
            <div>
                <h1 class="dashboard-title">Analytics Overview</h1>
                <p class="date-info">Last updated: <?php echo date('F d, Y h:i A'); ?>&nbsp;(PHT)</p>
                <a href="../admin.php">Go back to Feed</a>
            </div>
            <div class="refresh-button">
                <button class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Data
                </button>
            </div>
        </div>

        <!-- Summary Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card animate__animated animate__fadeIn">
                    <div class="stats-icon bg-purple">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo number_format($total_students); ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card animate__animated animate__fadeIn" style="animation-delay: 0.1s">
                    <div class="stats-icon bg-blue">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo number_format($total_announcements); ?></h3>
                        <p>Total Announcements</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card animate__animated animate__fadeIn" style="animation-delay: 0.2s">
                    <div class="stats-icon bg-green">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo number_format($total_departments); ?></h3>
                        <p>Departments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card animate__animated animate__fadeIn" style="animation-delay: 0.3s">
                    <div class="stats-icon bg-orange">
                        <i class="bi bi-send-fill"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo number_format($total_sms); ?></h3>
                        <p>SMS Sent</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Charts Row -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="chart-card animate__animated animate__fadeIn" style="animation-delay: 0.4s">
                    <div class="chart-header">
                        <h2 class="chart-title">Announcement Trends</h2>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active" data-period="month" onclick="updateTrendChart('month')">Monthly</button>
                            <button class="btn btn-outline-secondary" data-period="week" onclick="updateTrendChart('week')">Weekly</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-card animate__animated animate__fadeIn" style="animation-delay: 0.5s">
                    <div class="chart-header">
                        <h2 class="chart-title">SMS Delivery Status</h2>
                    </div>
                    <div class="chart-container">
                        <canvas id="smsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Charts Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="chart-card animate__animated animate__fadeIn" style="animation-delay: 0.6s">
                    <div class="chart-header">
                        <h2 class="chart-title">Department Distribution</h2>
                    </div>
                    <div class="chart-container">
                        <canvas id="deptChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-card animate__animated animate__fadeIn" style="animation-delay: 0.7s">
                    <div class="chart-header">
                        <h2 class="chart-title">Year Level Distribution</h2>
                    </div>
                    <div class="chart-container">
                        <canvas id="yearLevelChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../cdn/body.html'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment"></script>

    <script>
        // Global chart instances
        let trendChart, smsChart, deptChart, yearLevelChart;

        // Chart color scheme
        const chartColors = {
            purple: '#6c5ce7',
            blue: '#0984e3',
            green: '#00b894',
            orange: '#e17055',
            red: '#d63031',
            yellow: '#fdcb6e'
        };

        // Common chart options
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#2d3436',
                    bodyColor: '#2d3436',
                    borderColor: '#dfe6e9',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return ` ${context.parsed}`;
                        }
                    }
                }
            }
        };

        // Initialize all charts
        function initializeCharts() {
            // Trend Chart
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_map(function ($item) {
                                return date('M Y', strtotime($item['month']));
                            }, $monthly_stats)); ?>,
                    datasets: [{
                        label: 'Announcements',
                        data: <?php echo json_encode(array_map(function ($item) {
                                    return $item['count'];
                                }, $monthly_stats)); ?>,
                        borderColor: chartColors.green,
                        backgroundColor: `${chartColors.green}20`,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: '#f0f0f0'
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

            // SMS Chart
            const smsCtx = document.getElementById('smsChart').getContext('2d');
            smsChart = new Chart(smsCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_map(function ($item) {
                                return ucfirst($item['status']) . ' (' . $item['percentage'] . '%)';
                            }, $sms_stats)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_map(function ($item) {
                                    return $item['count'];
                                }, $sms_stats)); ?>,
                        backgroundColor: [chartColors.green, chartColors.red]
                    }]
                },
                options: commonOptions
            });

            // Department Chart
            const deptCtx = document.getElementById('deptChart').getContext('2d');
            deptChart = new Chart(deptCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_map(function ($item) {
                                return $item['department_name'];
                            }, $dept_stats)); ?>,
                    datasets: [{
                        label: 'Announcements',
                        data: <?php echo json_encode(array_map(function ($item) {
                                    return $item['count'];
                                }, $dept_stats)); ?>,
                        backgroundColor: chartColors.blue,
                        borderRadius: 6
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: '#f0f0f0'
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

            // Year Level Chart
            const yearLevelCtx = document.getElementById('yearLevelChart').getContext('2d');
            yearLevelChart = new Chart(yearLevelCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_map(function ($item) {
                                return $item['year_level'];
                            }, $year_level_stats)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_map(function ($item) {
                                    return $item['count'];
                                }, $year_level_stats)); ?>,
                        backgroundColor: Object.values(chartColors)
                    }]
                },
                options: commonOptions
            });
        }

        // Refresh dashboard data
        function refreshDashboard() {
            const refreshBtn = document.querySelector('.refresh-button button');
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise animate-spin"></i> Refreshing...';

            fetch('dashboard_data.php')
                .then(response => response.json())
                .then(data => {
                    updateCharts(data);
                    refreshBtn.disabled = false;
                    refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh Data';
                })
                .catch(error => {
                    console.error('Error refreshing dashboard:', error);
                    refreshBtn.disabled = false;
                    refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh Data';
                });
        }

        // Update trend chart period
        function updateTrendChart(period) {
            const buttons = document.querySelectorAll('[data-period]');
            buttons.forEach(btn => btn.classList.remove('active'));
            document.querySelector(`[data-period="${period}"]`).classList.add('active');

            fetch(`dashboard_data.php?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    trendChart.data.labels = data.labels;
                    trendChart.data.datasets[0].data = data.values;
                    trendChart.update();
                });
        }

        // Initialize everything when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            document.querySelector('.loading-overlay').style.display = 'none';
        });

        // Add resize handler for responsive charts
        window.addEventListener('resize', function() {
            if (trendChart) trendChart.resize();
            if (smsChart) smsChart.resize();
            if (deptChart) deptChart.resize();
            if (yearLevelChart) yearLevelChart.resize();
        });
    </script>
</body>

</html>