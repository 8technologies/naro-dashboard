{{-- resources/views/widgets/recent-farmers.blade.php --}}
<style>
    .farmers-card {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        overflow: hidden;
    }
    .farmers-card .card-header {
        background-color: #38a169;
        color: #fff;
        border-bottom: none;
        padding: 1rem 1.5rem;
    }
    .farmers-card .card-title {
        margin: 0;
        font-weight: 700;
        font-size: 1.25rem;
    }
    .farmers-table {
        width: 100%;
        border-collapse: collapse;
    }
    .farmers-table th,
    .farmers-table td {
        border: 1px solid #c6f6d5;
        padding: 0.75rem 1rem;
        vertical-align: middle;
    }
    .farmers-table thead th {
        background-color: #48bb78;
        color: #fff;
        text-align: left;
    }
    .farmers-table tbody tr:nth-child(odd) {
        background-color: #f0fff4;
    }
    .farmers-table tbody tr:nth-child(even) {
        background-color: #e6ffed;
    }
</style>

<div class="card mb-4 mb-md-5 border-0 farmers-card border-0" style="border-radius: 0rem;">
    <div class="card-header p-0 px-4 py-2">
        <h4 class="fs-22 fw-800"  style="line-height: 1; margrin: 0; ">Recently Registered Farmers</h4>
    </div>
    <div class="card-body py-0 p-0 py-md-0">
        @if($farmers->isEmpty())
            <div class="text-center">
                <h5 class="text-dark">No farmers registered yet.</h5>
                <hr>
                <p>Open the app and register a farmer to get started.</p>
            </div>
        @else
            <table class="farmers-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Marital Status</th>
                        <th>Livestock</th>
                        <th>Has Smartphone</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($farmers as $farmer)
                        <tr>
                            <td>{{ $farmer->first_name }} {{ $farmer->last_name }}</td>
                            <td>{{ $farmer->phone_number ?? $farmer->phone }}</td>
                            <td>{{ $farmer->gender }}</td>
                            <td>{{ $farmer->marital_status }}</td>
                            <td>{{ $farmer->livestock }}</td>
                            <td>{{ $farmer->has_smart_phone }}</td>
                            <td>{{ $farmer->created_at->format('d M, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
