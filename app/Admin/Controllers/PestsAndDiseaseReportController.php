<?php

namespace App\Admin\Controllers;

use App\Models\PestsAndDiseaseReport;
use App\Models\PestsAndDisease;
use App\Models\Garden;
use App\Models\Crop;
use App\Models\User;
use App\Models\District;
use App\Models\Subcounty;
use App\Models\Parish;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PestsAndDiseaseReportController extends AdminController
{
    protected $title = 'Pests And Disease Reports';

    protected function grid()
    {
        $grid = new Grid(new PestsAndDiseaseReport());

        // Eager load relationships for performance
        $grid->model()->with(['pestsAndDisease', 'garden', 'crop', 'user', 'district', 'subcounty', 'parish'])
                    ->orderBy('id', 'desc');

        // Filters
        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->between('created_at', 'Date')->datetime();
            $filter->equal('pests_and_disease_id', 'Pest/Disease')
                   ->select(PestsAndDisease::pluck('category', 'id'));
            $filter->equal('garden_id', 'Garden')
                   ->select(Garden::pluck('name', 'id'));
            $filter->equal('crop_id', 'Crop')
                   ->select(Crop::pluck('name', 'id'));
            $filter->equal('user_id', 'Reporter')
                   ->select(User::pluck('name', 'id'));
            $filter->equal('district_id', 'District')
                   ->select(District::pluck('name', 'id'));
        });

        // Columns
        $grid->column('id', 'Sn')->sortable();
        $grid->column('created_at', 'Date')->sortable()
             ->display(fn($dt) => Utils::my_date($dt));
        $grid->column('pestsAndDisease.category', 'Pest/Disease')->sortable();
        $grid->column('garden.name', 'Garden')->sortable();
        $grid->column('crop.name', 'Crop')->sortable();
        $grid->column('user.name', 'Reporter')->sortable();
        $grid->column('district.name', 'District')->sortable();
        $grid->column('subcounty.name', 'Subcounty')->sortable();
        $grid->column('parish.name', 'Parish')->sortable();
        $grid->column('description', 'Description')->limit(50);
        $grid->column('photo', 'Photo')
             ->lightbox(['width' => 100, 'height' => 100]);
        $grid->column('gps_lati', 'Latitude');
        $grid->column('gps_longi', 'Longitude');

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(PestsAndDiseaseReport::findOrFail($id));
        $show->panel()->tools(fn($tools) => $tools->disableDelete()->disableEdit());

        $show->field('id', 'ID');
        $show->field('created_at', 'Reported On')
             ->as(fn($dt) => Utils::my_date($dt));
        $show->field('pests_and_disease_id', 'Pest/Disease')
             ->as(fn($id) => optional(PestsAndDisease::find($id))->category);
        $show->field('garden_id', 'Garden')
             ->as(fn($id) => optional(Garden::find($id))->name);
        $show->field('crop_id', 'Crop')
             ->as(fn($id) => optional(Crop::find($id))->name);
        $show->field('user_id', 'Reporter')
             ->as(fn($id) => optional(User::find($id))->name);
        $show->field('description', 'Description');
        $show->field('photo', 'Photo')
             ->as(fn($photo) => $photo ? '<img src="'.asset('storage/'.$photo).'" width="200"/>' : 'None')
             ->unescape();
        $show->field('gps_lati', 'Latitude');
        $show->field('gps_longi', 'Longitude');

        return $show;
    }

    protected function form()
    {
        $form = new Form(new PestsAndDiseaseReport());

        $form->select('pests_and_disease_id', 'Pest/Disease')
             ->options(PestsAndDisease::pluck('category', 'id'))
             ->rules('required');
        $form->select('garden_id', 'Garden')
             ->options(Garden::pluck('name', 'id'))
             ->rules('required');
        $form->select('crop_id', 'Crop')
             ->options(Crop::pluck('name', 'id'))
             ->rules('required');
        $form->select('user_id', 'Reporter')
             ->options(User::pluck('name', 'id'))
             ->rules('required');
        $form->select('district_id', 'District')
             ->options(District::pluck('name', 'id'));
        $form->select('subcounty_id', 'Subcounty')
             ->options(Subcounty::pluck('name', 'id'));
        $form->select('parish_id', 'Parish')
             ->options(Parish::pluck('name', 'id'));

        $form->textarea('description', 'Description')->rows(3);
        $form->image('photo', 'Photo');
        $form->decimal('gps_lati', 'GPS Latitude', 10, 6);
        $form->decimal('gps_longi', 'GPS Longitude', 10, 6);

        return $form;
    }
}
