<?php

$metrics = new awtMetrics();
$stat = $metrics->getStats();
$urlsByDate = [];
$uniqueUidsByDate = [];
$visitsByDate = [];
$uniqueVisitorsByDate = [];
$mostVisitedPages = [];

foreach ($stat as $entry) {
    $date = date('Y-m-d', strtotime($entry['date']));
    $url = $entry['url'];
    $uid = $entry['uid'];

    // Count number of URLs by date
    $urlsByDate[$date][$url] = ($urlsByDate[$date][$url] ?? 0) + 1;

    // Count number of unique UIDs by date
    if (!isset($uniqueUidsByDate[$date])) {
        $uniqueUidsByDate[$date] = [];
    }
    if (!in_array($uid, $uniqueUidsByDate[$date])) {
        $uniqueUidsByDate[$date][] = $uid;
    }

    if (array_key_exists($url, $mostVisitedPages)) {
        $mostVisitedPages[$url]++;
    } else {
        $mostVisitedPages[$url] = 1;
    }

    // Count number of visits by date
    $visitsByDate[$date] = ($visitsByDate[$date] ?? 0) + 1;

    // Count number of unique visitors by date
    if (!in_array($uid, $uniqueVisitorsByDate[$date] ?? [])) {
        $uniqueVisitorsByDate[$date][] = $uid;
    }
}


$googleChartData = [
    'urlsByDate' => $urlsByDate,
    'visitsByDate' => $visitsByDate,
    'uniqueVisitorsByDate' => $uniqueVisitorsByDate,
];

$googleChartDataJson = json_encode($googleChartData);
?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">
    google.charts.load('current', { 'packages': ['corechart'] });

    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', 'Visits');

        <?php
        echo "data.addRows([\n";
        foreach ($googleChartData['visitsByDate'] as $date => $visits) {
            echo "['{$date}', {$visits}],\n";
        }
        echo "]);\n";
        ?>

        var options = {
            title: 'Total visits',
            legend: { position: 'bottom' }
        };

        var chart = new google.visualization.AreaChart(document.getElementById('visits_chart_div'));
        chart.draw(data, options);
    }
</script>
<script type="text/javascript">
    google.charts.load('current', { 'packages': ['corechart'] });

    google.charts.setOnLoadCallback(drawUniqueVisitorsChart);

    function drawUniqueVisitorsChart() {
        var uniqueVisitorsData = new google.visualization.DataTable();
        uniqueVisitorsData.addColumn('string', 'Date');
        uniqueVisitorsData.addColumn('number', 'Unique Visitors');

        <?php
        echo "uniqueVisitorsData.addRows([\n";
        foreach ($googleChartData['uniqueVisitorsByDate'] as $date => $uniqueVisitors) {
            // Ensure that $uniqueVisitors is a numeric value
            $uniqueVisitors = count($uniqueVisitors);
            echo "['{$date}', {$uniqueVisitors}],\n";
        }
        echo "]);\n";
        ?>

        var uniqueVisitorsOptions = {
            title: 'Unique Visitors',
            legend: { position: 'bottom' }
        };

        var uniqueVisitorsChart = new google.visualization.AreaChart(document.getElementById('unique_visitors_chart_div'));
        uniqueVisitorsChart.draw(uniqueVisitorsData, uniqueVisitorsOptions);
    }
</script>
<script type="text/javascript">
    google.charts.load('current', { 'packages': ['corechart'] });
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Page');
        data.addColumn('number', 'Visits');

        <?php
        foreach ($mostVisitedPages as $page => $visits) {
            echo "data.addRow(['$page', $visits]);\n";
        }
        ?>

        var options = {
            title: 'Page visits share',
        };

        var chart = new google.visualization.PieChart(document.getElementById('most_visited_pages'));
        chart.draw(data, options);
    }
</script>
<script type="text/javascript">
    google.charts.load('current', { 'packages': ['table'] });
    google.charts.setOnLoadCallback(drawTable);

    function drawTable() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('string', 'URL');
        data.addColumn('number', 'Hits');

        <?php
        $tableData = [];
        foreach ($urlsByDate as $date => $urlCounts) {
            // Find the URL with the most hits for the current date
            $mostVisitedPage = array_keys($urlCounts, max($urlCounts))[0];
            $tableData[] = [$date, $mostVisitedPage, max($urlCounts)];
        }
        ?>

        var options = {
            title: 'Most visited page'
        }

        data.addRows(<?php echo json_encode($tableData); ?>);

        var table = new google.visualization.Table(document.getElementById('most_visited_pages_date'));
        table.draw(data, { showRowNumber: true, width: '100%', height: '100%' }, options);
    }
</script>
<script type="text/javascript">
    google.charts.load('current', { 'packages': ['corechart'] });
    google.charts.setOnLoadCallback(drawAreaChart);

    function drawAreaChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        <?php
        $uniqueUrls = [];
        foreach ($urlsByDate as $urlCounts) {
            $uniqueUrls = array_merge($uniqueUrls, array_keys($urlCounts));
        }
        $uniqueUrls = array_unique($uniqueUrls);
        foreach ($uniqueUrls as $url) {
            echo "data.addColumn('number', '$url');\n";
        }
        ?>

        data.addRows([
            <?php
            foreach ($urlsByDate as $date => $urlCounts) {
                $rowData = "['$date'";
                foreach ($uniqueUrls as $url) {
                    $count = $urlCounts[$url] ?? 0;
                    $rowData .= ", $count";
                }
                $rowData .= "],";
                echo $rowData . "\n";
            }
            ?>
        ]);

        var options = {
            title: 'Page Hits by Date',
            isStacked: false,
            legend: { position: 'bottom' },
            width: '100%',
            height: 400,
        };

        var chart = new google.visualization.AreaChart(document.getElementById('urls_by_date_area_chart'));
        chart.draw(data, options);
    }
</script>
<div class="metrics">
    <div class="header">
        <h1>Website Statistics Today</h1>
    </div>
    <div class="stats-today">
        <div class="wrapper">
            <h3>Visits today</h3>
            <p><?php echo $metrics->getMetricsTodayAll(); ?></p>
        </div>
        <div class="wrapper">
            <h3>Unique visits today</h3>
            <p><?php echo $metrics->getMetricsTodayUnique(); ?></p>
        </div>
        <div class="wrapper">
            <h3>Most visited</h3>
            <p><?php echo $metrics->getMostVisitedToday()[0]["url"]; ?></p>
        </div>
    </div>
    
    <div class="header">
        <h1>Website Statistics Overtime</h1>
    </div>
    
    <div class="chart-container">
        <div id="visits_chart_div" style="width: 48%; height: 400px; border-radius: 10px;"></div>
        <div id="unique_visitors_chart_div" style="width: 48%; height: 400px; border-radius: 10px;"></div>
        <div id="most_visited_pages" style="width: 48%; height: 400px;"></div>
        <div id="most_visited_pages_date" style="width: 48%; height: 400px;"></div>
        <div id="urls_by_date_area_chart" style="width: 97%; height: 400px;"></div>
    </div>
</div>

<style>

    
    .metrics {
        margin: 10px 30px;
    }

    .header {
        width: 97%;
        margin: 30px 0;
    }
    
    .chart-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }

    .stats-today {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin: 10px 25px;
    }

    .stats-today .wrapper {
        background: #fff;
        padding: 10px 30px;
    }
</style>    