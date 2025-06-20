<?php

namespace App\Admin\Controllers;

use App\Models\Garden;
use App\Models\GardenActivity;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Carbon;

class GardenActivityController extends AdminController
{
    /**
     * Title for current resource.
     * @var string
     */
    protected $title = 'Garden Activities';

    /**
     * Make a grid builder.
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GardenActivity());
        
        // Eager load relationships for massive performance improvement
        $grid->model()->with(['user', 'garden'])->orderBy('activity_due_date', 'desc');

        // ======== FILTERS & SEARCH ========
        $grid->quickSearch(function ($model, $query) {
            $model->where('activity_name', 'like', "%{$query}%")
                ->orWhereHas('user', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->orWhereHas('garden', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                });
        })->placeholder('Search Activity, Garden, or Owner...');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('activity_name', 'Activity Name');
            $filter->equal('garden_id', 'Garden')->select(Garden::all()->pluck('name', 'id'));
            $filter->equal('user_id', 'Owner')->select(User::all()->pluck('name', 'id'));
         
            $filter->between('activity_due_date', 'Due Date')->date();
        });

        // ======== GRID COLUMNS ========
        $grid->column('id', __('ID'))->sortable();
        
        $grid->column('photo', __('Photo'))->lightbox(['width' => 80, 'height' => 60]); 

        $grid->column('activity_name', __('Activity'))->expand(function ($model) {
            return "<div style='padding:15px;'><h4>Description</h4><p>" . e($model->activity_description) . "</p></div>";
        })->sortable();

        $grid->column('garden.name', __('Garden'))->sortable();
        $grid->column('user.name', __('Owner'))->sortable();
        
        $grid->column('Timeline')->display(function () {
            $dueDate = Carbon::parse($this->activity_due_date);
            $now = Carbon::now();
            
            // Use a more readable format for the difference
            $diff = $dueDate->diffForHumans($now, ['syntax' => Carbon::DIFF_ABSOLUTE, 'parts' => 2]);
            $dateFormatted = $dueDate->format('M d, Y');
            
            if ($this->farmer_has_submitted === 'Yes') {
                return "{$dateFormatted}<br><span class='text-muted' style='font-size:12px;'>Submitted</span>";
            }
            
            if ($dueDate->isPast()) {
                return "{$dateFormatted}<br><strong style='color:red; font-size:12px;'>Overdue by {$diff}</strong>";
            } else {
                return "{$dateFormatted}<br><span style='color:blue; font-size:12px;'>Due in {$diff}</span>";
            }
        })->sortable('activity_due_date');

        $grid->column('farmer_activity_status','Overall Status')->display(function () {
            if ($this->agent_activity_status === 'Verified') {
                return "<span class='label label-success'>Verified</span>";
            }
            if ($this->farmer_has_submitted === 'Yes') {
                return "<span class='label label-primary'>Done</span>";
            }
            if (Carbon::parse($this->activity_due_date)->isPast()) {
                return "<span class='label label-danger'>Missed</span>";
            }
            return "<span class='label label-warning'>Pending</span>";
        })->sortable()->filter([
            'Pending' => 'Pending',
            'Done' => 'Done',
            'Missed' => 'Missed',
            'Verified' => 'Verified',
        ]); 
        
        $grid->column('is_compulsory', __('Compulsory'))->bool()->sortable();

        // Disable the "Create" button, as activities are likely generated elsewhere
        $grid->disableCreateButton();

        return $grid;
    }

    /**
     * Make a show builder.
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(GardenActivity::findOrFail($id));

        $show->panel()->title("Details for '" . $show->getModel()->activity_name . "'");

        $show->divider('Activity Overview');
        $show->field('id', __('Activity ID'));
        $show->field('activity_name', __('Activity Name'));
        $show->field('activity_description', __('Description'));
        $show->field('garden.name', __('Associated Garden'));
        $show->field('user.name', __('Assigned To (Owner)'));
        $show->field('activity_date_to_be_done', __('Scheduled Date'));
        $show->field('activity_due_date', __('Due Date'));

        $show->divider("Farmer's Submission");
        $show->field('farmer_has_submitted', __('Submitted?'))->using(['Yes' => 'Yes', 'No' => 'No'])->label();
        $show->field('farmer_activity_status', __('Status'))->label();
        $show->field('activity_date_done', __('Actual Date Done'));
        $show->field('farmer_submission_date', __('Submission Date'));
        $show->field('farmer_comment', __('Farmer Comments'));
        $show->field('photo', __('Submitted Photo'))->image();

        $show->divider("Agent's Verification");
        $show->field('agent_names', __('Verifying Agent'));
        $show->field('agent_has_submitted', __('Verified?'))->using(['Yes' => 'Yes', 'No' => 'No'])->label();
        $show->field('agent_activity_status', __('Verification Status'))->label();
        $show->field('agent_comment', __('Agent Comments'));
        $show->field('agent_submission_date', __('Verification Date'));

        return $show;
    }

    /**
     * Make a form builder.
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new GardenActivity());

        $form->tab('Activity Details & Farmer Submission', function ($form) {
            $form->header('Activity Information');
            $form->select('garden_id', __('Garden'))->options(Garden::all()->pluck('name', 'id'))->rules('required');
            $form->text('activity_name', __('Activity Name'))->rules('required');
            $form->textarea('activity_description', __('Description'));
            $form->date('activity_date_to_be_done', __('Scheduled Date'))->rules('required');
            $form->date('activity_due_date', __('Due Date'))->rules('required');
            $form->switch('is_compulsory', __('Is Compulsory?'))->default(1);

            $form->divider("Farmer's Submission Details");
            $form->radio('farmer_has_submitted', __('Has Farmer Submitted?'))
                 ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');
            $form->select('farmer_activity_status', __('Farmer Status'))
                 ->options(['Pending' => 'Pending', 'Done' => 'Done', 'Missed' => 'Missed']);
            $form->date('activity_date_done', __('Date Activity Was Done'));
            $form->textarea('farmer_comment', __('Farmer Comments'));
            $form->image('photo', __('Submitted Photo'))->uniqueName()->move('activity_photos');
        });

        $form->tab('Agent Verification', function ($form) {
            $form->header('Verification by Extension Agent');
            $form->text('agent_names', __('Verifying Agent Name'));
            $form->radio('agent_has_submitted', __('Has Agent Verified?'))
                 ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');
            $form->select('agent_activity_status', __('Verification Status'))
                 ->options(['Pending Verification' => 'Pending Verification', 'Verified' => 'Verified', 'Rejected' => 'Rejected']);
            $form->textarea('agent_comment', __('Agent Comments'));
            $form->datetime('agent_submission_date', __('Verification Date'))->default(date('Y-m-d H:i:s'));
        });

        // Assign the user_id from the garden automatically
        $form->saving(function (Form $form) {
            if ($form->garden_id) {
                $garden = Garden::find($form->garden_id);
                if ($garden) {
                    $form->model()->user_id = $garden->user_id;
                }
            }
        });

        return $form;
    }
}
