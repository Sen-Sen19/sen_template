<?php include 'plugins/navbar.php'; ?>
<?php include 'plugins/sidebar/admin_bar.php'; ?>

<style>
  .card-shadow {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
  #chart {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
</style>
<script src="../../dist/js/apexChart.js"></script>

<div class="content-wrapper">
  <div class="content-header"></div>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-12">
          <div class="card card-gray-dark card-outline card-shadow">
            <div class="card-header">
            <h3 class="card-title">   <i class="nav-icon fas fa-chart-bar"></i> Chart</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                  <i class="fas fa-expand"></i>
                </button>
              </div>
            </div>

          
            <div class="card-body">
              <div class="row">
                <div class="col-sm-6">
                  <label for="yearSelect">Select Year:</label>
                  <select id="yearSelect" class="form-control">
                    <option value="2021">2021</option>
                    <option value="2022">2022</option>
                    <option value="2023">2023</option>
                    <option value="2024">2024</option>
                  </select>
                </div>
                <div class="col-sm-6">
                  <label for="chartTypeSelect">Select Chart Type:</label>
                  <select id="chartTypeSelect" class="form-control">
                    <option value="area">Area</option>
                    <option value="line">Line</option>
                    <option value="bar">Bar</option>
                    <option value="radar">Radar</option>
                  </select>
                </div>
              </div>
              
              <div id="chart"></div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include 'plugins/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const yearSelect = document.getElementById('yearSelect');
    const chartTypeSelect = document.getElementById('chartTypeSelect');
    let chart; 

    yearSelect.addEventListener('change', function() {
        fetchChartData(yearSelect.value, chartTypeSelect.value);
    });

    chartTypeSelect.addEventListener('change', function() {
        fetchChartData(yearSelect.value, chartTypeSelect.value);
    });

    fetchChartData(yearSelect.value, chartTypeSelect.value);

    function fetchChartData(year, chartType) {
        fetch(`../../process/graph.php?year=${year}`)
            .then(response => response.json())
            .then(data => {
                const options = {
                    series: [{
                        name: 'Rate',
                        data: data.rates
                    }],
                    chart: {
                        type: chartType,
                        height: 450
                    },
                    title: {
                        text: 'Monthly Performance'
                    },
                    xaxis: {
                        categories: data.months
                    },
                    colors: ['#00aa03'] 
                };

                if (chart) {
                    chart.destroy();
                }

                chart = new ApexCharts(document.querySelector("#chart"), options);
                chart.render();
            });
    }
});
</script>
