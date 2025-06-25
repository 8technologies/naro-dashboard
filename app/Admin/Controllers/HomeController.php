<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PestAndDiseaseController;
use App\Models\Garden;
use App\Models\GardenActivity;
use App\Models\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\QuestionController;
use App\Models\Farmer;
use App\Models\FinancialRecord;
use App\Models\GroundnutVariety;
use App\Models\PestsAndDisease;
use App\Models\PestsAndDiseaseReport;
use App\Models\Product;
use App\Models\ServiceProvider;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Layout\Row;
use Illuminate\Support\Facades\Auth;


class HomeController extends Controller
{


    public function providers_map(Content $content)
    {
        $content->title('Service Providers Map')
            ->description('Interactive map of all registered service providers.');

        $content->row(function (Row $row) {
            $row->column(12, function (Column $column) {
                // Eager-load nothing, but get all providers
                $providers = ServiceProvider::all();

                $markers = [];
                foreach ($providers as $sp) {
                    if (empty($sp->gps_lat) || empty($sp->gps_long)) {
                        continue;
                    }

                    // Build popup HTML
                    $popup  = '<div class="sp-popup">';
                    $popup .= '<h3>' . e($sp->provider_name) . '</h3>';
                    $popup .= '<p><strong>Business:</strong> ' . e($sp->business_name) . '</p>';
                    $popup .= '<p><strong>Services:</strong> ' . e($sp->services_offered) . '</p>';
                    $popup .= '<p><strong>Details:</strong> ' . e($sp->details) . '</p>';
                    $popup .= '<p><strong>Contact:</strong><br>'
                        . e($sp->phone_number)
                        . ($sp->phone_number_2 ? ' / ' . e($sp->phone_number_2) : '')
                        . '</p>';
                    if ($sp->email) {
                        $popup .= '<p><strong>Email:</strong> ' . e($sp->email) . '</p>';
                    }
                    if ($sp->photo) {
                        $popup .= '<div class="popup-image">'
                            . '<img src="' . asset('storage/' . $sp->photo) . '" alt="Photo" />'
                            . '</div>';
                    }
                    $popup .= '<div class="popup-actions">'
                        . '<a href="' . admin_url("service-providers/{$sp->id}") . '" class="popup-link">View Details</a>'
                        . '</div></div>';

                    $markers[] = [
                        'lat'   => (float) $sp->gps_lat,
                        'long'  => (float) $sp->gps_long,
                        'popup' => $popup,
                    ];
                }

                $column->append(view('maps.service_providers_leaflet_map', [
                    'markers' => $markers,
                ]));
            });
        });

        return $content;
    }


    public function pests_map(Content $content)
    {
        $content->title('Pest Reports Map')
            ->description('Interactive map of all pest & disease reports.');

        $content->row(function (Row $row) {
            $row->column(12, function (Column $column) {
                // Eager-load related data
                $reports = PestsAndDiseaseReport::with([
                    'pestsAndDisease',
                    'garden.user',
                    'garden.district'
                ])->get();

                $markers = [];

                foreach ($reports as $rep) {
                    if (empty($rep->gps_lati) || empty($rep->gps_longi)) {
                        continue;
                    }

                    // Build popup HTML
                    $popup  = '<div class="report-popup">';
                    $popup .= '<h3>' . e($rep->pestsAndDisease->category ?? 'Unknown') . '</h3>';
                    $popup .= '<p><strong>Date:</strong> ' . e(Utils::my_date($rep->created_at)) . '</p>';
                    $popup .= '<p><strong>Garden:</strong> ' . e($rep->garden->name ?? 'N/A') . '</p>';
                    $popup .= '<p><strong>District:</strong> ' . e($rep->garden->district->name ?? 'N/A') . '</p>';
                    $popup .= '<p><strong>Reporter:</strong> ' . e($rep->garden->user->name ?? 'N/A') . '</p>';
                    $popup .= '<p>' . nl2br(e($rep->description)) . '</p>';
                    if ($rep->photo) {
                        $popup .= '<div class="popup-image">'
                            . '<img src="' . asset('storage/' . $rep->photo)
                            . '" alt="Report photo" />'
                            . '</div>';
                    }
                    $popup .= '<div class="popup-actions">'
                        . '<a href="' . admin_url("pests-and-disease-reports/{$rep->id}")
                        . '" class="popup-link">View Details</a>'
                        . '</div></div>';

                    $markers[] = [
                        'lat'   => (float) $rep->gps_lati,
                        'long'  => (float) $rep->gps_longi,
                        'popup' => $popup,
                    ];
                }

                $column->append(view('maps.pest_reports_leaflet_map', [
                    'markers' => $markers,
                ]));
            });
        });

        return $content;
    }

    //function called gardens_map that will return a map of all gardens on map  
    // In your GardenController.php...


    public function gardens_map(Content $content)
    {
        $content
            ->title('Gardens Map')
            ->description('Map of all registered gardens.');

        $content->row(function (Row $row) {
            $row->column(12, function (Column $column) {
                // THE FIX: Eager load relationships for performance
                $gardens = Garden::with('user', 'district')->get();
                $gardens_data = [];

                foreach ($gardens as $garden) {
                    if (empty($garden->gps_lati) || empty($garden->gps_longi)) {
                        continue;
                    }

                    // --- Build the new, detailed popup HTML ---

                    // Garden Name (Title)
                    $popupContent = "<h3>" . htmlspecialchars($garden->name) . "</h3>";

                    // Details Table
                    $popupContent .= "<table>";
                    $popupContent .= "<tr><td><strong>Crop:</strong></td><td>" . htmlspecialchars($garden->crop_name) . "</td></tr>";

                    // Add District, checking if it exists
                    $districtName = $garden->district ? htmlspecialchars($garden->district->name) : 'N/A';
                    $popupContent .= "<tr><td><strong>District:</strong></td><td>" . $districtName . "</td></tr>";

                    // Add Owner Name, checking if user relationship exists
                    $ownerName = $garden->user ? htmlspecialchars($garden->user->name) : 'N/A';
                    $popupContent .= "<tr><td><strong>Owner:</strong></td><td>" . $ownerName . "</td></tr>";

                    // Add Owner Contact, checking if user relationship exists
                    $ownerContact = $garden->user ? htmlspecialchars($garden->user->phone_number) : 'N/A';
                    $popupContent .= "<tr><td><strong>Contact:</strong></td><td>" . $ownerContact . "</td></tr>";
                    $popupContent .= "</table>";

                    // Links / Buttons
                    $popupContent .= "<div class='popup-actions'>";
                    $popupContent .= "<a href='" . url('/admin/gardens/' . $garden->id) . "' target='_blank' rel='noopener noreferrer' class='popup-link details'>View Details</a>";
                    $popupContent .= "<a href='https://www.google.com/maps/dir/?api=1&destination={$garden->gps_lati},{$garden->gps_longi}' target='_blank' rel='noopener noreferrer' class='popup-link directions'>Get Directions</a>";
                    $popupContent .= "</div>";


                    $gardens_data[] = [
                        'lat'   => (float) $garden->gps_lati,
                        'long'  => (float) $garden->gps_longi,
                        'popup' => $popupContent,
                    ];
                }

                $column->append(view('maps.raw_leaflet_map', [
                    'gardens' => $gardens_data,
                ]));
            });
        });

        return $content;
    }


    public function questions(Content $content)
    {

        $u = Auth::user();
        $content
            ->title('Farmers Forum');
        $content->row(function (Row $row) {
            $row->column(12, function (Column $column) {
                $column->append(QuestionController::get_questions());
            });
        });
        return $content;
    }

    public function answers(Content $content, $id)
    {
        $content
            ->title('Answers');
        $content->row(function (Row $row) use ($id) {
            $row->column(12, function (Column $column) use ($id) {
                $column->append(QuestionController::question_answers($id));
            });
        });
        return $content;
    }

    public function pestsAndDiseases(Content $content)
    {

        $u = Auth::user();
        $content
            ->title('Ask the expert');
        $content->row(function (Row $row) {
            $row->column(12, function (Column $column) {
                $column->append(PestAndDiseaseController::index());
            });
        });
        return $content;
    }


    public function index(Content $content)
    {

        //return $content;
        $u = Auth::user();
        $content
            ->title('NaRO - Dashboard')
            ->description('Hello ' . $u->name . "!");



        $u = Admin::user();


        $content->row(function (Row $row) {
            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'Registered Farmers',
                    'sub_title' => 'All registered farmers.',
                    'number' => number_format(Farmer::count()),
                    'link' => admin_url('users')
                ]));
            });

            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'Service Providers',
                    'sub_title' => 'Total number of service providers.',
                    'number' => number_format(ServiceProvider::count()),
                    'link' => admin_url('service-providers')
                ]));
            });
            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'Registered Farms',
                    'sub_title' => 'Total number of registered farms.',
                    'number' => number_format(Garden::count()),
                    'link' => admin_url('gardens')
                ]));
            });
            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'Reported Pests & Diseases',
                    'sub_title' => 'Reported pests and diseases.',
                    'number' => number_format(PestsAndDisease::count()),
                    'link' => admin_url('pests-and-disease-reports')
                ]));
            });
        });


        $content->row(function (Row $row) {



            $row->column(6, function (Column $column) {
                $top_pests = Garden::selectRaw('count(*) as count, crop_id')
                    ->groupBy('crop_id')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get();
                $counts = [];
                $lables = [];
                foreach ($top_pests as $key => $value) {
                    $district = $value->variety;
                    $name = '';
                    if ($district != null) {
                        $name = $district->name;
                    }
                    $counts[] = $value->count;
                    $lables[] = $name . " (" . $value->count . ")";
                }

                $column->append(view('widgets.by-categories', [
                    'counts' => $counts,
                    'lables' => $lables
                ]));
            });


            // Recently registered gardens
            $row->column(6, function (Column $column) {
                $gardens = 
                Garden::orderBy('created_at', 'desc')->limit(200)
                    ->with('user', 'district')
                    ->get();

                $gardens_data = [];
                foreach ($gardens as $garden) {
                    if (empty($garden->gps_lati) || empty($garden->gps_longi)) {
                        continue;
                    }

                    $popup  = '<div class="report-popup">';
                    $popup .= '<h3>' . e($garden->name) . '</h3>';
                    $popup .= '<p><strong>Crop:</strong> ' . e($garden->crop_text) . '</p>';
                    $popup .= '<p><strong>District:</strong> ' . e($garden->district->name ?? 'N/A') . '</p>';
                    $popup .= '<p><strong>Owner:</strong> ' . e($garden->user->name ?? 'N/A') . '</p>';
                    $popup .= '</div>';

                    $gardens_data[] = [
                        'lat'   => (float) $garden->gps_lati,
                        'long'  => (float) $garden->gps_longi,
                        'popup' => $popup,
                    ];
                }

                $column->append(view('maps.recent_gardens_leaflet_map', [
                    'gardens' => $gardens_data,
                ]));
            });
        });

        $content->row(function (Row $row) {


            // Pest reports from last 30 days
            $row->column(6, function (Column $column) {
                $reports = PestsAndDiseaseReport::where('created_at', '>=', Carbon::now()->subDays(30))
                    ->with(['pestsAndDisease', 'garden.user', 'garden.district'])
                    ->get();

                $markers = [];
                foreach ($reports as $rep) {
                    if (empty($rep->gps_lati) || empty($rep->gps_longi)) {
                        continue;
                    }

                    $popup  = '<div class="report-popup">';
                    $popup .= '<h3>' . e($rep->pestsAndDisease->category ?? 'Unknown') . '</h3>';
                    $popup .= '<p><strong>Date:</strong> ' . e(Utils::my_date($rep->created_at)) . '</p>';
                    $popup .= '<p><strong>Garden:</strong> ' . e($rep->garden->name ?? 'N/A') . '</p>';
                    $popup .= '</div>';

                    $markers[] = [
                        'lat'   => (float) $rep->gps_lati,
                        'long'  => (float) $rep->gps_longi,
                        'popup' => $popup,
                    ];
                }

                $column->append(view('maps.recent_pests_leaflet_map', [
                    'markers' => $markers,
                ]));
            });

            $row->column(6, function (Column $column) {
                $pests = PestsAndDiseaseReport::where([])->orderBy('created_at', 'desc')->limit(6)->get();
                $column->append(view('widgets.pests-2', [
                    'data' => $pests,
                ]));
            });

            $row->column(6, function (Column $column) {
                $pests = PestsAndDiseaseReport::where([])->orderBy('created_at', 'desc')->limit(5)->get();

                //get pests count order by top district_id count
                $top_pests = PestsAndDiseaseReport::selectRaw('count(*) as count, district_id')
                    ->groupBy('district_id')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get();
                $counts = [];
                $lables = [];
                foreach ($top_pests as $key => $value) {
                    $district = $value->district;
                    $counts[] = $value->count;
                    $lables[] = $district->name . " (" . $value->count . ")";
                }

                $column->append(view('widgets.pests', [
                    'data' => $pests,
                    'counts' => $counts,
                    'lables' => $lables
                ]));
            });

            $row->column(6, function (Column $column) {
                $now = Carbon::now();
                //LAST 4 MONTHS
                $last_4_months = [];
                $incomes = [];
                $expenses = [];
                $profit = [];
                for ($i = 0; $i < 5; $i++) {
                    $start_date = $now->copy()->subMonths($i)->startOfMonth();
                    $end_date = $now->copy()->subMonths($i)->endOfMonth();
                    $moth_name = $start_date->format('F');
                    $last_4_months[] = $moth_name . " - " . $start_date->year;
                    $income = FinancialRecord::where('amount', '>', 0)
                        ->where('created_at', '>=', $start_date)
                        ->where('created_at', '<=', $end_date)
                        ->sum('amount');
                    $expense = FinancialRecord::where('amount', '<', 0)
                        ->where('created_at', '>=', $start_date)
                        ->where('created_at', '<=', $end_date)
                        ->sum('amount');
                    $profit[] = $income + $expense;
                    $incomes[] = $income;
                    $expenses[] = $expense;
                }



                $column->append(view('widgets.groundnut-market', [
                    'last_4_months' => $last_4_months,
                    'incomes' => $incomes,
                    'expenses' => $expenses,
                    'profit' => $profit
                ]));
            });
        });

        $content->row(function (Row $row) {

            $row->column(12, function (Column $column) {
                $recentFarmers = Farmer::orderBy('created_at', 'desc')->limit(10)->get();
                $column->append(view('widgets.products-services', [
                    'farmers' => $recentFarmers,
                ]));
            });
        });

        return $content;
        $content->row(function (Row $row) {
            $row->column(12, function (Column $column) {
                $column->append(view('widgets.weather', []));
            });
        });


        return $content;
    }
}
