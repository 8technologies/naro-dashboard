<?php

namespace Encore\Admin\Controllers;

use App\Models\User; // It's better to use your actual User model.
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;

class UserController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return 'Users'; // Changed to a more generic title
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $userModel = config('admin.database.users_model', User::class);
        $grid = new Grid(new $userModel());
        $grid->model()->orderBy('id', 'desc');

        // ======== GRID SEARCH & FILTERS ========
        $grid->quickSearch(['name', 'username', 'email'])->placeholder('Search by Name, Username, or Email...');

        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();

            $filter->column(1 / 2, function ($filter) {
                $filter->like('name', 'Name');
                $filter->like('username', 'Username');
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->like('email', 'Email');
                $filter->equal('gender', 'Gender')->select([
                    'male'   => 'Male',
                    'female' => 'Female',
                ]);
            });
            
            $roleModel = config('admin.database.roles_model');
            $filter->where(function ($query) {
                $query->whereHas('roles', function ($query) {
                    $query->whereIn('id', $this->input);
                });
            }, 'Roles', 'roles')->multipleSelect($roleModel::all()->pluck('name', 'id'));


            $filter->between('created_at', 'Created At')->date();
        });


        // ======== GRID COLUMNS ========
        $grid->column('id', 'ID')->sortable()->width(50);

        // FIX: Removed the ->rounded() method call which was causing the error.
        $grid->column('avatar', 'Avatar')->image('', 60, 60);

        $grid->column('name', 'Full Name')->sortable();
        $grid->column('username', 'Username')->sortable();

        $grid->column('Contact Info')->display(function () {
            return "{$this->email}<br>{$this->phone_number}";
        });

        $grid->column('roles', 'Roles')->pluck('name')->label('primary');

        $grid->column('created_at', 'Joined At')->display(function ($createdAt) {
            return $createdAt ? date('M d, Y', strtotime($createdAt)) : 'N/A';
        })->sortable();


        // ======== GRID ACTIONS ========
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->getKey() == 1) {
                $actions->disableDelete();
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

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
        $userModel = config('admin.database.users_model', User::class);
        $show = new Show($userModel::findOrFail($id));

        $show->panel()->tools(function ($tools) {
            $tools->disableDelete();
        });

        $show->divider('User Profile');

        $show->field('avatar', 'Avatar')->image('', 120, 120);
        
        $show->field('id', 'User ID');
        $show->field('name', 'Full Name');
        $show->field('username', 'Username');
        $show->field('email', 'Email Address');
        $show->field('phone_number', 'Phone Number');
        $show->field('gender', 'Gender')->using(['male' => 'Male', 'female' => 'Female']);

        $show->divider('Authentication & Roles');

        $show->field('roles', 'Roles')->as(function ($roles) {
            return $roles->pluck('name');
        })->label('primary');

        $show->field('permissions', 'Permissions')->as(function ($permission) {
            return $permission->pluck('name');
        })->label();
        
        $show->divider('Timestamps');
        $show->field('created_at');
        $show->field('updated_at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $userModel = config('admin.database.users_model', User::class);
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $userModel());

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');

        $form->tab('User Profile', function ($form) use ($userTable, $connection) {
            $form->image('avatar', 'Avatar')->uniqueName()->move('avatars')->rules('image');
            
            $form->column(1/2, function ($form) {
                $form->text('first_name', 'First Name')->rules('required|min:2');
                $form->text('last_name', 'Last Name')->rules('required|min:2');
            });
            
            $form->column(1/2, function ($form) {
                 $form->text('username', 'Username')
                ->creationRules(['required', "unique:{$connection}.{$userTable}"])
                ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);
                
                 $form->radio('gender', 'Gender')->options(['male' => 'Male', 'female' => 'Female'])->rules('required')->default('male');
            });

            $form->divider();

            $form->email('email', 'Email Address')
                ->creationRules(['required', 'email', "unique:{$connection}.{$userTable}"])
                ->updateRules(['required', 'email', "unique:{$connection}.{$userTable},email,{{id}}"]);
            
            $form->text('phone_number', 'Phone Number')->rules('nullable|min:10');

        })->tab('Authentication', function ($form) use ($roleModel, $permissionModel) {
            
            $form->password('password', 'Password')->rules('nullable|min:6|confirmed');
            $form->password('password_confirmation', 'Confirm Password')->rules('nullable');

            $form->ignore(['password_confirmation']);

            $form->multipleSelect('roles', 'Roles')->options($roleModel::all()->pluck('name', 'id'))->rules('required');
            $form->multipleSelect('permissions', 'Permissions')->options($permissionModel::all()->pluck('name', 'id'));
        });

        // Callback to handle password hashing and name concatenation
        $form->saving(function (Form $form) {
            // Concatenate first and last name to create the full 'name'
            if ($form->first_name && $form->last_name) {
                $form->name = $form->first_name . ' ' . $form->last_name;
            }

            // Hash password if it is being changed
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
            
            // If password fields are empty on an update, don't change the password
            if (empty($form->password)) {
                $form->deleteInput('password');
            }
        });
        
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete(request('user') == 1);
        });

        return $form;
    }
}
