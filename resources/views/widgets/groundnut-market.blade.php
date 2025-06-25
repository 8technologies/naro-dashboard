<style>
    .my-card-5 {
        /* border to left side only */
        border: 0;
        border-left: 9px solid transparent;
        border-radius: 0rem;
        border-top: #9700b1 5px solid !important;
    }
</style>
<div class="card mb-4 mb-md-5 border-0 my-card-5">
    <!--begin::Header-->
    <div class="d-flex justify-content-between px-3 pt-2 px-md-4 border-bottom">
        <h4 style="line-height: 1; margrin: 0; " class="fs-22 fw-800"> 
            Farmers Monthly Income Vs. Expenditure
        </h4>
    </div>
    <div class="card-body py-2 py-md-3">
        <canvas id="barGraph" style="width: 100%; height: 335px;"></canvas>
    </div>
</div>

<script>
    $(function() {
        // put in this $last_4_months
        var varieties = ['Income', 'Eexpenditure', 'Profit or Loss'];
        var productionData = {
            labels: JSON.parse('<?php echo json_encode($last_4_months); ?>'),
            datasets: [
                {
                    label: 'Income',
                    data: JSON.parse('<?php echo json_encode($incomes); ?>'),
                    backgroundColor: getBackgroundColor(0),
                },
                {
                    label: 'Expenditure',
                    data: JSON.parse('<?php echo json_encode($expenses); ?>'),
                    backgroundColor: getBackgroundColor(1),
                },
                {
                    label: 'Profit or Loss',
                    data: JSON.parse('<?php echo json_encode($profit); ?>'),
                    backgroundColor: getBackgroundColor(2),
                },
            ],
        };

        var config = {
            type: 'line',
            data: productionData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        beginAtZero: true,
                    },
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                },
                animation: {
                    animateScale: true,
                    animateRotate: true,
                },
            },
        };

        var ctx = document.getElementById('barGraph').getContext('2d');
        new Chart(ctx, config);
    });

    // Function to generate background colors for the datasets
    function getBackgroundColor(index) {
        var colors = [ '#33FF57','#FF5733', '#5733FF']; // Color options
        return colors[index];
    }
</script>
