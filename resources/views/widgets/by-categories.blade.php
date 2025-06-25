<style>
    .my-card-3 {
        /* border to left side only */
        border: 0;
        border-left: 9px solid transparent;
        border-radius: 0rem;
        border-top: #ff0000 5px solid !important;
    }   
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="card mb-3" style="">

      <div class="d-flex justify-content-between p-2 px-4 pt-3 border-bottom ">
        <h4 class="fs-22 fw-800 mb-0">     Gardens by Varieties</h4>
        <a href="{{ admin_url('gardens') }}" class="btn btn-link text-primary fw-600 text-decoration-underline">
            View All Gardens <i class="bi bi-arrow-right"></i>
        </a>
    </div>
 
    <div class="card-body py-3 mb-3 ">
        <div style="display: flex; justify-content: center; align-items: center; height: 400px; margin: auto;">
            {{-- Chart.js canvas --}}
            <canvas id="gardens-by-varieties"></canvas>
        </div>
    </div>
</div>




<script>
    // Sample data for pest and disease severity in different areas of the farm


    new Chart(document.getElementById('gardens-by-varieties').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: JSON.parse('<?php echo json_encode($lables); ?>'),
            datasets: [{
                label: 'Pests & Diseases',
                data: {{ json_encode($counts) }},
                backgroundColor: [
                    'rgba(0, 0, 255, 0.7)',
                    'rgba(255, 69, 0, 0.7)',
                    'rgba(255, 102, 0, 0.7)',
                    'rgba(218, 112, 214, 0.7)',
                    'rgba(0, 128, 128, 0.7)',
                    'rgba(0, 255, 0, 0.7)',
                    'rgba(65, 105, 225, 0.7)',
                    'rgba(255, 0, 0, 0.7)',
                    'rgba(255, 255, 0, 0.7)',
                ],
            }]
        },
    });
</script>
