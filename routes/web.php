<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\MainController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\Gen;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\PestAndDiseaseController;
use App\Models\Crop;
use App\Models\District;
use App\Models\Farmer;
use App\Models\Garden;
use App\Models\GardenActivity;
use App\Models\ServiceProvider;
use App\Models\Subcounty;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

Route::get('create-dummy', function () {
    // --- Data Configuration for Realistic Activities ---

    $activities = GardenActivity::all();
    foreach ($activities as $activity) {
        $activity->photo = 'images/activity-' . rand(1, 5) . '.jpg'; // Assign a random photo from 1 to 5
        $activity->save();
        echo "Updated Activity ID: {$activity->id} with photo {$activity->photo}<br>";
    }
    dd('Updated all activities to be non-guide and set crop_activity_id to 1.');

    $groundnutActivities = [
        ['name' => 'Land Preparation', 'description' => 'First ploughing and harrowing of the garden.', 'days_offset' => -14, 'is_compulsory' => 'Yes'],
        ['name' => 'Planting', 'description' => 'Planting selected groundnut variety (e.g., Serenut 5R) in rows.', 'days_offset' => 0, 'is_compulsory' => 'Yes'],
        ['name' => 'First Weeding', 'description' => 'Manual or chemical weeding to control early-stage weeds.', 'days_offset' => 21, 'is_compulsory' => 'Yes'],
        ['name' => 'Pest & Disease Scouting', 'description' => 'Check for signs of pests like aphids or diseases like Groundnut Rosette.', 'days_offset' => 35, 'is_compulsory' => 'Yes'],
        ['name' => 'Applying Gypsum', 'description' => 'Applying gypsum (calcium sulfate) at the pegging stage to improve pod filling.', 'days_offset' => 45, 'is_compulsory' => 'No'],
        ['name' => 'Second Weeding', 'description' => 'Final weeding before the canopy closes.', 'days_offset' => 50, 'is_compulsory' => 'Yes'],
        ['name' => 'Harvesting Readiness Check', 'description' => 'Check a few plants for maturity. Pods should be firm and seeds fully developed.', 'days_offset' => 95, 'is_compulsory' => 'No'],
        ['name' => 'Harvesting', 'description' => 'Uprooting the groundnut plants from the soil.', 'days_offset' => 110, 'is_compulsory' => 'Yes'],
        ['name' => 'Post-Harvest Drying', 'description' => 'Properly drying the harvested groundnuts to prevent aflatoxin contamination.', 'days_offset' => 112, 'is_compulsory' => 'Yes'],
    ];

    $agentNames = ['Mr. Okello', 'Ms. Aine', 'Mr. Ssentamu', 'Mrs. Nakato'];
    $farmerComments = ['Completed successfully.', 'Done, but faced some challenges with dry weather.', 'Could not complete on time.', ''];
    $agentComments = ['Good progress observed.', 'Farmer needs support with pest identification.', 'Excellent work.', 'Followed the guide perfectly.'];

    // --- Seeding Logic ---

    echo "<h1>Starting Garden Activity Seeding Process...</h1>";
    $allGardens = Garden::with('gardenActivities')->get(); // Eager load for performance

    foreach ($allGardens as $garden) {
        $existingActivityCount = count($garden->gardenActivities);
        $activitiesToCreate = 5 - $existingActivityCount;

        if ($activitiesToCreate <= 0) {
            echo "Garden ID: {$garden->id} ({$garden->name}) already has {$existingActivityCount} activities. Skipping.<br>";
            continue;
        }

        echo "Processing Garden ID: {$garden->id} ({$garden->name}). Creating {$activitiesToCreate} activities...<br>";

        // Shuffle the predefined activities to get a random set for each garden
        shuffle($groundnutActivities);
        $selectedActivities = array_slice($groundnutActivities, 0, $activitiesToCreate);

        foreach ($selectedActivities as $activityTemplate) {
            // Use Carbon for easy date manipulation
            $plantingDate = Carbon::parse($garden->planting_date);
            $dateToBeDone = $plantingDate->copy()->addDays($activityTemplate['days_offset']);
            $dueDate = $dateToBeDone->copy()->addDays(7); // Give a 1-week deadline

            $activity = new GardenActivity();

            $activity->garden_id = $garden->id;
            $activity->user_id = $garden->user_id;
            $activity->activity_name = $activityTemplate['name'];
            $activity->activity_description = $activityTemplate['description'];
            $activity->activity_date_to_be_done = $dateToBeDone->toDateString();
            $activity->activity_due_date = $dueDate->toDateString();
            $activity->is_compulsory = $activityTemplate['is_compulsory'];
            $activity->is_guide_activity = 'No'; // Assuming these are not from a guide
            $activity->crop_activity_id = 1; // Assuming these are not from a guide

            // --- Dynamic Status Logic ---
            $hasSubmitted = false;
            if ($dueDate->isPast()) { // If the due date has passed
                $hasSubmitted = (bool)rand(0, 1); // 50/50 chance of having submitted
                if ($hasSubmitted) {
                    $activity->farmer_has_submitted = 'Yes';
                    $activity->farmer_activity_status = 'Done';
                    $activity->activity_date_done = $dateToBeDone->copy()->addDays(rand(-1, 2))->toDateString();
                    $activity->farmer_submission_date = $activity->activity_date_done;
                    $activity->farmer_comment = $farmerComments[array_rand($farmerComments)];
                    $activity->photo = 'images/sample.jpg';

                    // Agent submission logic (optional)
                    if ((bool)rand(0, 1)) {
                        $activity->agent_has_submitted = 'Yes';
                        $activity->agent_activity_status = 'Verified';
                        $activity->agent_id = rand(1, 4);
                        $activity->agent_names = $agentNames[array_rand($agentNames)];
                        $activity->agent_comment = $agentComments[array_rand($agentComments)];
                        $activity->agent_submission_date = $dueDate->copy()->addDays(rand(1, 3))->toDateString();
                    }
                } else {
                    $activity->farmer_has_submitted = 'No';
                    $activity->farmer_activity_status = 'Missed';
                }
            } else { // If the due date is in the future
                $activity->farmer_has_submitted = 'No';
                $activity->farmer_activity_status = 'Pending';
            }

            $activity->save();
        }
        echo "<span style='color:green;'>---> Successfully created {$activitiesToCreate} activities for Garden ID: {$garden->id}</span><br><br>";
    }

    echo "<h2>...Garden activity seeding process completed!</h2>";
});


Route::get('import-data', function () {
    $file = public_path('storage/SERVICE-PROVIDES.csv');
    //check if file exists
    if (!file_exists($file)) {
        dd('File not found');
    }
    //check if file is readable
    if (!is_readable($file)) {
        dd('File not readable');
    }
    //check if file is empty
    if (filesize($file) == 0) {
        dd('File is empty');
    }

    //open the file
    $handle = fopen($file, 'r');
    //check if file is opened
    if (!$handle) {
        dd('File not opened');
    }
    //read the file
    $data = [];
    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
        $data[] = $row;
    }
    //close the file
    fclose($handle);
    //check if data is empty
    if (empty($data)) {
        dd('File is empty');
    }
    //check if data is valid
    if (!is_array($data)) {
        dd('File is not valid');
    }
    //check if data is valid
    if (count($data) < 2) {
        dd('File is not valid');
    }
    //check if data is valid
    if (count($data[0]) < 2) {
        dd('File is not valid');
    }

    foreach ($data as $key => $value) {
        //skip the first row
        if ($key == 0) {
            continue;
        }
        $phone_number = $value[5];
        $phone_number_1 = null;
        $phone_number_2 = null;
        if (str_contains($phone_number, '/')) {
            $phones = explode('/', $phone_number);
            if (count($phones) > 1) {
                $phone_number_1 = $phones[0];
                $phone_number_2 = $phones[1];
            } else {
                $phone_number_1 = $phone_number;
            }
        } else {
            $phone_number_1 = $phone_number;
        }

        if ($phone_number_1 != null) {
            $phone_number_1 = Utils::prepare_phone_number($phone_number_1);
        }
        if ($phone_number_2 != null) {
            $phone_number_2 = Utils::prepare_phone_number($phone_number_2);
        }
        $conds = [];
        if ($phone_number_1 != null) {
            $conds['phone_number'] = $phone_number_1;
        }
        $first_name = trim($value[1]);
        $last_name = trim($value[2]);

        if ($first_name != null && strlen($first_name) > 2) {
            $conds['provider_name'] = $first_name;
        }

        if ($last_name != null && strlen($last_name) > 2) {
            $conds['business_name'] = $last_name;
        }
        if ($phone_number_1 != null && strlen($phone_number_1) > 2) {
            $conds['phone_number'] = $phone_number_1;
        }
        if ($phone_number_2 != null && strlen($phone_number_2) > 2) {
            $conds['phone_number'] = $phone_number_2;
        }
        if ($phone_number_1 != null && strlen($phone_number_1) > 2) {
            $conds['phone_number'] = $phone_number_1;
        }

        $provider = ServiceProvider::where($conds)->first();

        if ($provider == null) {
            $provider = new ServiceProvider();
        }
        $provider->provider_name = $first_name;
        $provider->business_name = $last_name;
        $provider->phone_number = $phone_number_1;
        $provider->phone_number_2 = $phone_number_2;
        $provider->details = $value[7] . ', ' . $value[8] . '.';
        $provider->services_offered = $value[3];
        $provider->gps_lat = $value[4];
        $provider->gps_long = $value[5];
        $provider->services_offered = $value[6];
        $provider->photo = null;
        $provider->save();
        echo $provider->id . '. - ' . $provider->provider_name . ' ' . $provider->business_name . '<br>';
        continue;

        dd($provider);
        dd($phone_number_1);

        /*    
 
    "" => "images/logo.png"


    "" => "gona@mailinator.com"



      0 => "Omia Agribusinesss and Development Group Ltd"
  1 => "Omia Agribusiness"
  2 => "Agro Inputs, Extension Services"
  3 => ""
  4 => ""
  5 => "786584336"
  6 => "773628770"
  7 => "Arua"
  8 => "City, Town, Municipal"
  --
0 => "Service Provider Name"
1 => "Businness name"
2 => "Services Offered"
3 => "GPS Latitude"
4 => "GPS Longtude"
5 => "Phone No. 1"
6 => "Phone No. 2"
7 => "District"
8 => "Subcounty"
  
  */

        $gender = trim($value[3]);
        $education_level = trim($value[4]);
        $is_smartphone = trim($value[7]);
        $address = trim($value[8]);
        $marital_status = trim($value[9]);
        $district = trim($value[10]);
        $subcounty = trim($value[11]);
        $does_livestock = trim($value[12]);

        if ($first_name != null && strlen($first_name) > 2) {
            $conds['first_name'] = $first_name;
        }

        if ($last_name != null && strlen($last_name) > 2) {
            $conds['last_name'] = $last_name;
        }

        if (strtolower($gender) == 'm' || strtolower($gender) == 'male') {
            $gender = 'Male';
        }
        if (strtolower($gender) == 'f' || strtolower($gender) == 'female') {
            $gender = 'Female';
        }

        if ($gender != null && strlen($gender) > 2) {
            $conds['gender'] = $gender;
        }

        $farmer = Farmer::where($conds)->first();
        if ($farmer == null) {
            $farmer = new Farmer();
        }
        $farmer->first_name = $first_name;
        $farmer->last_name = $last_name;
        $farmer->gender = $gender;
        $farmer->education_level = $education_level;
        $farmer->has_smart_phone = $is_smartphone;
        $farmer->phone_number = $phone_number_1;
        $farmer->address = $address;
        $farmer->marital_status = $marital_status;
        $farmer->livestock = $does_livestock;
        if ($is_smartphone == null || $is_smartphone == '') {
            $is_smartphone = 'No';
        }

        $dis = District::where('name', 'like', '%' . $district . '%')->first();
        if ($dis != null) {
            $farmer->district_id = $dis->id;
        }

        $subs = Subcounty::where('name', 'like', '%' . $subcounty . '%')->get();
        $_sub = null;
        if ($subs != null && count($subs) > 0) {
            foreach ($subs as $sub) {
                $_sub = $sub;
                if ($sub->district_id == $dis->id) {
                    $$_sub = $sub;
                    break;
                }
            }
        }
        if ($_sub != null) {
            $farmer->subcounty_id = $_sub->id;
        }
        $farmer->farmer_group_id = null;
        $farmer->phone = $phone_number_1;
        $farmer->phone_number = $phone_number_1;
        $farmer->save();

        echo $farmer->id . '. - ' . $farmer->first_name . ' ' . $farmer->last_name . '<br>';
    }

    /* 
  0 => "1"
  1 => "Angelos"
  2 => "Ochen"
  3 => "M"
  4 => "Secondary"
  5 => "256785644204"
  6 => ""
  7 => "Yes"
  8 => "Aye Medo Ngeca LSB "
  9 => "Married"
  10 => "Dokolo"
  11 => "Amwoma"
  12 => "Yes"
*/
    dd('import-data');
});
Route::get('policy', function () {
    return view('policy');
});
Route::get('migrate', function () {
    //run laravel migrate command in code
    $RESP = Artisan::call('migrate');
    echo "<pre>";
    print_r($RESP);
    echo "</pre>";
    die();
});
Route::get('app', function () {
    //redirec to url('naro-v3.apk');
    return redirect(url('naro-v3.apk'));
});

//api generation
Route::get('generate-class', [MainController::class, 'generate_class']);
Route::get('/gen', function () {
    die(Gen::find($_GET['id'])->do_get());
})->name("register");
Route::get('/gen-form', function () {
    die(Gen::find($_GET['id'])->make_forms());
})->name("gen-form");

//farmers forum
Route::get('chat', [ChatController::class, 'index']);
Route::post('store', [QuestionController::class, 'store'])->name('store');
Route::post('answers', [QuestionController::class, 'answers'])->name('answers');

//pests and diseases
Route::get('pest-and-diseases', [PestAndDiseaseController::class, 'index'])->name('pest-and-diseases');

Auth::routes();

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
