<?php

namespace App\Admin\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\Subcounty;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\Auth;

class ProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Products & Services';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        // Disable filter/column selector to simplify UI
        $grid->disableFilter();
        $grid->disableColumnSelector();

        // Quick search
        $grid->quickSearch('name')->placeholder('Search by name or details');

        // Columns
        $grid->column('photo', 'Photo')
             ->lightbox(['width' => 80, 'height' => 80])
             ->sortable();
        $grid->column('name', 'Name')->sortable();
        $grid->column('type', 'Type')->sortable()
             ->label(['Product' => 'primary', 'Service' => 'info']);
        $grid->column('state', 'Condition')->sortable()
             ->label(['New' => 'success', 'Used but like new' => 'warning', 'Used' => 'default']);
        $grid->column('offer_type', 'Offer')->sortable()
             ->label(['For sale' => 'success', 'For hire' => 'warning']);
        $grid->column('price', 'Price')->sortable()->display(function ($price) {
            return 'UGX ' . number_format($price);
        });
        $grid->column('administrator_id', 'Provider')->sortable()->display(function ($id) {
            $admin = Administrator::find($id);
            return $admin ? $admin->name : 'Unknown';
        });
        $grid->column('subcounty_id', 'Location')->sortable()->display(function ($id) {
            $loc = Subcounty::find($id);
            return $loc ? $loc->name : 'N/A';
        });
        $grid->column('details', 'Details')->limit(50);

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
        $show = new Show(Product::findOrFail($id));

        $show->field('photo', 'Photo')->lightbox(['width' => 200, 'height' => 200]);
        $show->field('name', 'Name');
        $show->field('type', 'Type');
        $show->field('state', 'Condition');
        $show->field('offer_type', 'Offer Type');
        $show->field('price', 'Price')->as(function ($price) {
            return 'UGX ' . number_format($price);
        });
        $show->field('administrator_id', 'Provider')->as(function ($id) {
            $admin = Administrator::find($id);
            return $admin ? $admin->name : 'Unknown';
        });
        $show->field('subcounty_id', 'Location')->as(function ($id) {
            $loc = Location::find($id);
            return $loc ? $loc->name : 'N/A';
        });
        $show->field('details', 'Details')->unescape();

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product());

        // Provider selection with AJAX for staff/admin, readonly otherwise
        if (Admin::user()->isRole('staff') || Admin::user()->isRole('admin')) {
            $ajaxUrl = url('/api/ajax?search_by_1=name&search_by_2=id&model=Administrator');
            $form->select('administrator_id', 'Provider')
                 ->ajax($ajaxUrl)
                 ->rules('required');
        } else {
            $form->select('administrator_id', 'Provider')
                 ->options([Auth::user()->id => Auth::user()->name])
                 ->default(Auth::user()->id)
                 ->readOnly()
                 ->rules('required');
        }

        $form->radio('type', 'Item Type')
             ->options(['Product' => 'Product', 'Service' => 'Service'])
             ->rules('required');
        $form->text('name', 'Name')
             ->rules('required|max:255');
        $form->image('photo', 'Photo')
             ->uniqueName()
             ->rules('required|image');
        $form->radio('state', 'Condition')
             ->options(['New' => 'New', 'Used but like new' => 'Used but like new', 'Used' => 'Used'])
             ->rules('required');
        $form->radio('offer_type', 'Offer Type')
             ->options(['For sale' => 'For sale', 'For hire' => 'For hire'])
             ->rules('required');
        $form->currency('price', 'Price (UGX)')
             ->symbol('UGX')
             ->rules('required|numeric|min:0');
        $form->select('subcounty_id', 'Location')
             ->options(Subcounty::pluck('name', 'id'))
             ->rules('required');
        $form->textarea('details', 'Details')
             ->rules('required|max:500');

        return $form;
    }
}
