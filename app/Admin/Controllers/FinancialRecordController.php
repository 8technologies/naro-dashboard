<?php

namespace App\Admin\Controllers;

use App\Models\FinancialRecord;
use App\Models\Garden;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FinancialRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Financial Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FinancialRecord());

        // Eager load relations
        $grid->model()->with(['garden', 'user'])->orderBy('date', 'desc');

        // Filters
        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->between('date', 'Date')->date();
            $filter->equal('garden_id', 'Garden')->select(Garden::pluck('name', 'id')); 
            $filter->equal('category', 'Type')->select([ 'Income' => 'Income', 'Expense' => 'Expense' ]);
            $filter->like('description', 'Description');
        });

        // Columns
        $grid->column('id', 'Sn')->sortable();
        $grid->column('created_at', 'Created')->display(function ($dt) {
            return Utils::my_date_time($dt);
        })->sortable();

        $grid->column('date', 'Date')->display(function ($d) {
            return Utils::my_date($d);
        })->sortable();

        $grid->column('garden.name', 'Garden')->sortable();

        $grid->column('category', 'Type')
             ->using(['Income' => 'Income', 'Expense' => 'Expense'])
             ->dot(['Income' => 'success', 'Expense' => 'danger'])
             ->sortable();

        $grid->column('amount', 'Amount')->sortable()->display(function ($amount) {
            $prefix = $this->category === 'Income' ? '+' : '-';
            $class = $this->category === 'Income' ? 'text-success' : 'text-danger';
            return "<span class='${class}'>${prefix}UGX " . number_format(abs($amount)) . "</span>";
        });

        $grid->column('description', 'Particulars')->sortable();

        $grid->column('payment_method', 'Payment Method')->hide();
        $grid->column('recipient', 'Recipient')->hide();

        $grid->column('receipt', 'Receipt')->display(function ($path) {
            if ($path) {
                return "<a href='" . asset('storage/' . $path) . "' target='_blank'>Download</a>";
            }
            return '-';
        });

        $grid->column('user.name', 'Created By')->sortable();

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
        $show = new Show(FinancialRecord::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('created_at', 'Created At')->as(function ($dt) {
            return Utils::my_date_time($dt);
        });
        $show->field('date', 'Transaction Date')->as(function ($d) {
            return Utils::my_date($d);
        });

        $show->field('garden.name', 'Garden');
        $show->field('user.name', 'Recorded By');

        $show->field('category', 'Type')->using(['Income' => 'Income', 'Expense' => 'Expense']);
        $show->field('amount', 'Amount')->as(function ($amount) {
            return 'UGX ' . number_format($amount);
        });

        $show->field('payment_method', 'Payment Method');
        $show->field('recipient', 'Recipient');
        $show->field('description', 'Description');

        $show->field('quantity', 'Quantity');

        $show->field('receipt', 'Receipt')->as(function ($path) {
            if ($path) {
                return "<a href='" . asset('storage/' . $path) . "' target='_blank'>View Receipt</a>";
            }
            return '-';
        })->unescape();

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FinancialRecord());

        $form->select('garden_id', 'Garden')
             ->options(Garden::pluck('name', 'id'))
             ->rules('required');

        $form->select('user_id', 'Recorded By')
             ->options(User::pluck('name', 'id'))
             ->default(Admin::user()->id)
             ->readonly();

        $form->radio('category', 'Type')
             ->options(['Income' => 'Income', 'Expense' => 'Expense'])
             ->rules('required');

        $form->currency('amount', 'Amount')->symbol('UGX')->rules('required|numeric|min:0');

        $form->date('date', 'Transaction Date')
             ->default(date('Y-m-d'))
             ->rules('required|date');

        $form->select('payment_method', 'Payment Method')
             ->options(['Cash' => 'Cash', 'Mobile Money' => 'Mobile Money', 'Bank Transfer' => 'Bank Transfer', 'Cheque' => 'Cheque'])
             ->rules('required');

        $form->text('recipient', 'Recipient')->rules('required|string|max:255');

        $form->textarea('description', 'Description')->rows(3);

        $form->file('receipt', 'Upload Receipt')
             ->uniqueName()
             ->move('receipts')
             ->rules('nullable|file|max:2048');

        $form->number('quantity', 'Quantity')
             ->min(1)
             ->default(1);

        return $form;
    }
}
