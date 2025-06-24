<?php

namespace App\Admin\Controllers;

use App\Models\ServiceProvider;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Illuminate\Html\HtmlFacade as Html;

class ServiceProviderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Service Providers';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ServiceProvider());

        // Default ordering & search
        $grid->model()->orderBy('id', 'desc');
        $grid->quickSearch('provider_name', 'business_name');

        // Filters
        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('provider_name', 'Provider Name');
            $filter->like('business_name', 'Business Name');
            $filter->equal('services_offered', 'Service')->select(
                ServiceProvider::pluck('services_offered', 'services_offered')->unique()
            );
        });

        // Columns
        $grid->column('provider_name', __('Provider Name'))->sortable();
        $grid->column('business_name', __('Business Name'))->sortable();
        $grid->column('services_offered', __('Services Offered'))
             ->display(function ($tags) {
                return explode(', ', $tags);
            })->label()
            ->sortable();
        $grid->column('photo', __('Photo'))
            ->lightbox(['width' => 100, 'height' => 100])
            ->sortable();

        $grid->column('gps_lat', __('GPS'))
            ->display(function ($lat) {
                // BOTTH latitude and longitude are displayed in the same column
                return number_format($lat, 6) . ', ' . number_format($this->gps_long, 6);
            })->sortable();  
        $grid->column('phone_number', __('Contact'))->sortable();
        $grid->column('phone_number_2', __('Alt Phone'))->hide();
        $grid->column('email', __('Email'))->hide();

        // Hide verbose fields by default
        $grid->column('details', __('Details'))->hide();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(ServiceProvider::findOrFail($id));

        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableDelete();
            });

        $show->field('id', __('ID'));
        $show->field('provider_name', __('Provider Name'));
        $show->field('business_name', __('Business Name'));
        $show->field('services_offered', __('Services Offered'))->as(function ($tags) {
            return implode(', ', $tags);
        })->label();
        $show->field('details', __('Details'))->unescape();

        $show->divider();
        $show->field('photo', __('Photo'))->as(function ($photo) {
            return $photo
                ? Html::image(asset('storage/' . $photo), 'Photo', ['width' => '100'])
                : '<span>No Image Provided</span>';
        })->unescape();

        $show->divider();
        $show->field('gps_lat', __('Latitude'));
        $show->field('gps_long', __('Longitude'));
        $show->map(['gps_lat', 'gps_long'], __('Location'))->height('300px');

        $show->divider();
        $show->field('phone_number', __('Phone'));
        $show->field('phone_number_2', __('Alternate Phone'));
        $show->field('email', __('Email'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ServiceProvider());

        // Basic info
        $form->text('provider_name', __('Provider Name'))
            ->rules('required|string|max:255');
        $form->text('business_name', __('Business Name'))
            ->rules('required|string|max:255');
        $form->tags('services_offered', __('Services Offered'))
            ->help('Add multiple services separated by comma');
        $form->quill('details', __('Details'));

        // Location
        $form->decimal('gps_lat', __('GPS Latitude'), 10, 6)
            ->rules('nullable|numeric|min:-90|max:90');
        $form->decimal('gps_long', __('GPS Longitude'), 10, 6)
            ->rules('nullable|numeric|min:-180|max:180');
        $form->text('gps_lat', __('Latitude'))
            ->default(0)
            ->rules('nullable|numeric|min:-90|max:90');
        $form->text('gps_long', __('Longitude'))
            ->default(0)
            ->rules('nullable|numeric|min:-180|max:180');

        // Media
        $form->image('photo', __('Business Photo'))
            ->uniqueName()
            ->move('service_providers/photos');

        // Contacts
        $form->mobile('phone_number', __('Phone Number'))
            ->options(['mask' => '+\############'])
            ->rules('nullable|regex:/^[\d+\-\s]+$/');
        $form->mobile('phone_number_2', __('Alternate Phone'))
            ->options(['mask' => '+\############']);
        $form->email('email', __('Email'))
            ->rules('nullable|email');

        return $form;
    }
}
