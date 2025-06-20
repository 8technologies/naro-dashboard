<?php

namespace App\Admin\Controllers;

use App\Models\Crop;
use App\Models\District;
use App\Models\Garden;
use App\Models\Parish;
use App\Models\Subcounty;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class GardenController extends AdminController
{
    /**
     * Title for current resource.
     * @var string
     */
    protected $title = 'Gardens';

    /**
     * Make a grid builder.
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Garden());

        // Eager load relationships for massive performance improvement
        $grid->model()->with(['user', 'crop', 'parish']);

        // ======== FILTERS & SEARCH ========
        $grid->quickSearch(function ($model, $query) {
            $model->where('name', 'like', "%{$query}%")
                ->orWhereHas('user', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->orWhereHas('crop', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                });
        })->placeholder('Search by Garden Name, Owner, or Crop...');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('name', 'Garden Name');
            $filter->equal('user_id', 'Owner')->select(User::all()->pluck('name', 'id'));
            $filter->equal('crop_id', 'Crop Type')->select(Crop::all()->pluck('name', 'id'));
            $filter->equal('status', 'Garden Status')->select(
                ['Active' => 'Active', 'Inactive' => 'Inactive', 'Harvested' => 'Harvested', 'Growing' => 'Growing', 'Fallow' => 'Fallow']
            );
            $filter->equal('production_scale', 'Production Scale')->select(
                ['Small scale' => 'Small scale', 'Medium scale' => 'Medium scale', 'Large scale' => 'Large scale']
            );
            $filter->between('created_at', 'Date Created')->date();
        });


        // ======== GRID COLUMNS ========
        $grid->column('id', __('ID'))->sortable();
        $grid->column('photo', __('Photo'))->lightbox(['width' => 80, 'height' => 60]);
        $grid->column('name', __('Garden Name'))->sortable();

        $grid->column('user.name', __('Owner'))->sortable();
        $grid->column('crop.name', __('Crop Planted'))->sortable();

        $grid->column('land_occupied', __('Land (Acres)'))->sortable();

        $grid->column('planting_date', __('Planted On'))->display(function ($date) {
            return $date ? date('M d, Y', strtotime($date)) : 'N/A';
        })->sortable();

        $grid->column('production_scale', __('Production Scale'))->sortable();
        $grid->column('created_at', __('Created At'))->display(function ($date) {
            return $date ? date('M d, Y', strtotime($date)) : 'N/A';
        })->sortable();

        $grid->column('status', __('Status'))->label([
            'Active' => 'success',
            'Growing' => 'info',
            'Harvested' => 'primary',
            'Fallow' => 'warning',
            'Inactive' => 'danger',
        ])->sortable();

        return $grid;
    }

    /**
     * Make a show builder.
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Garden::findOrFail($id));

        $show->panel()->title("Details for " . $show->getModel()->name);

        $show->divider('Garden Overview');
        $show->field('photo', __('Garden Photo'))->image();
        $show->field('id', __('Garden ID'));
        $show->field('name', __('Garden Name'));
        $show->field('user.name', __('Owner'));
        $show->field('crop.name', __('Crop Planted'));
        $show->field('status', __('Status'))->label();
        $show->field('production_scale', __('Production Scale'));
        $show->field('land_occupied', __('Land Size (Acres)'));

        $show->divider('Location Details');
        $show->field('district.name', __('District'));
        $show->field('subcounty.name', __('Subcounty'));
        $show->field('parish.name', __('Parish'));
        $show->field('gps_lati', __('GPS Latitude'));
        $show->field('gps_longi', __('GPS Longitude'));

        $show->divider('Planting & Harvest Information');
        $show->field('planting_date', __('Planting Date'));
        $show->field('harvest_date', __('Expected Harvest Date'));
        $show->field('quantity_planted', __('Quantity Planted'));
        $show->field('is_harvested', __('Is Harvested?'))->using(['Yes' => 'Yes', 'No' => 'No'])->label();

        $show->field('harvest_quality', __('Harvest Quality'));
        $show->field('quantity_harvested', __('Quantity Harvested'));
        $show->field('harvest_notes', __('Harvest Notes'));

        $show->divider('Financials');
        $show->field('income', __('Total Income'))->as(fn($income) => number_format($income));
        $show->field('expense', __('Total Expense'))->as(fn($expense) => number_format($expense));
        $show->field('profit', __('Profit/Loss'))->as(fn($profit) => number_format($profit));

        $show->divider('Timestamps');
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder.
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Garden());

        $form->tab('Basic Information', function ($form) {
            $form->text('name', __('Garden Name'))->rules('required|min:3');

            $form->select('user_id', 'Farm Owner')
                ->options(User::all()->pluck('name', 'id'))
                ->rules('required');

            $form->select('crop_id', __('Crop Planted'))
                ->options(Crop::all()->pluck('name', 'id'))
                ->rules('required');

            $form->image('photo', __('Garden Photo'))->uniqueName()->move('gardens');

            $form->textarea('details', __('Garden Details & Notes'));
        });

        $form->tab('Location', function ($form) {
            $form->select('district_id', 'District')->options(District::all()->pluck('name', 'id'))->load('subcounty_id', '/api/subcounties');
            $form->select('subcounty_id', 'Subcounty')->options(function ($id) {
                return Subcounty::where('id', $id)->pluck('name', 'id');
            })->load('parish_id', '/api/parishes');
            $form->select('parish_id', 'Parish')->options(function ($id) {
                return Parish::where('id', $id)->pluck('name', 'id');
            });

            $form->text('gps_lati', __('GPS latitude'));
            $form->text('gps_longi', __('GPS longitude'));
        });

        $form->tab('Planting & Harvest', function ($form) {
            $form->radio('production_scale', __('Production Scale'))
                ->options(['Small scale' => 'Small scale', 'Medium scale' => 'Medium scale', 'Large scale' => 'Large scale'])
                ->rules('required')->default('Small scale');

            $form->text('land_occupied', __('Land Size (e.g., 2.5 Acres)'))->rules('required');
            $form->text('quantity_planted', __('Quantity Planted (e.g., 10 kgs)'));

            $form->date('planting_date', __('Planting Date'))->rules('required');
            $form->date('harvest_date', __('Expected Harvest Date'));

            $form->divider('Harvest Details');

            $form->radio('is_harvested', __('Is the garden harvested?'))
                ->options(['Yes' => 'Yes', 'No' => 'No'])
                ->default('No')
                ->when('Yes', function (Form $form) {
                    $form->select('harvest_quality', __('Harvest Quality'))
                        ->options(['Excellent' => 'Excellent', 'Good' => 'Good', 'Average' => 'Average', 'Poor' => 'Poor']);
                    $form->text('quantity_harvested', __('Quantity Harvested (e.g., 15 bags)'));
                    $form->textarea('harvest_notes', __('Harvest Notes'));
                });
        });

        return $form;
    }
}
