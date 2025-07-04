<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserLevel;
use App\Enums\UserType;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Traits\CrudPermissionTrait;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class UserCrudController
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use CrudPermissionTrait;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\User::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/user');
        CRUD::setEntityNameStrings('user', 'users');
        $this->setAccessUsingPermissions();

        if (backpack_user()->hasRole('admin')) {
            $this->crud->allowAccess(['list', 'create', 'update', 'delete']);
        }
        if (backpack_user()->hasRole('user')) {
            $this->crud->allowAccess(['list', 'update', 'delete']);
            $this->crud->addClause('where', 'id', '=', backpack_user()->id);
        }
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     *
     * @return void
     */
    protected function setupListOperation()
    {
        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
        CRUD::column('id')->label('#');
        CRUD::column('name')->label('Name');
        CRUD::column('email')->label('Email');
        $options = UserLevel::asArray();
        $options = array_flip($options);
        CRUD::column([
            'name' => 'level',
            'label' => 'User Level',
            'type' => 'select_from_array',
            'options' => $options,
        ]);
        CRUD::column('credit')->type('number')->label('Credit');
        CRUD::column('subscription_ends_at')->label('Subscription Ends At');
        if (backpack_user()->hasRole('admin')) {
            CRUD::column([
                // two interconnected entities
                'label' => trans('backpack::permissionmanager.user_role_permission'),
                'field_unique_name' => 'user_role_permission',
                'type' => 'checklist_dependency',
                'name' => 'roles_permissions',
                'subfields' => [
                    'primary' => [
                        'label' => trans('backpack::permissionmanager.role'),
                        'name' => 'roles', // the method that defines the relationship in your Model
                        'entity' => 'roles', // the method that defines the relationship in your Model
                        'entity_secondary' => 'permissions', // the method that defines the relationship in your Model
                        'attribute' => 'name', // foreign key attribute that is shown to user
                        'model' => config('permission.models.role'), // foreign key model
                    ],
                    'secondary' => [
                        'label' => mb_ucfirst(trans('backpack::permissionmanager.permission_singular')),
                        'name' => 'permissions', // the method that defines the relationship in your Model
                        'entity' => 'permissions', // the method that defines the relationship in your Model
                        'entity_primary' => 'roles', // the method that defines the relationship in your Model
                        'attribute' => 'name', // foreign key attribute that is shown to user
                        'model' => config('permission.models.permission'), // foreign key model,
                    ],
                ],
            ]);

            CRUD::addField([
                'name' => 'custom_js',
                'type' => 'custom_html',
                'value' => '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const roleCheckboxes = document.querySelectorAll("input[name=\'roles_show[]\']");
                        roleCheckboxes.forEach(checkbox => {
                            checkbox.addEventListener("change", function() {
                                if (this.checked) {
                                    roleCheckboxes.forEach(otherCheckbox => {
                                        if (otherCheckbox !== this) {
                                            otherCheckbox.checked = false;
                                        }
                                    });
                                }
                            });
                        });
                    });
                </script>',
            ]);
        }

        $this->crud->removeButton('show');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     *
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->addUserFields();
        CRUD::setValidation(UserRequest::class);
        /** @disregard */
        CRUD::setFromDb();

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     *
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->addUserFields();
        CRUD::setValidation(UserRequest::class);
        $this->setupCreateOperation();
    }

    public function store()
    {
        /** @disregard */
        CRUD::setRequest(CRUD::validateRequest());
        // Use the default store method to create the user
        $user = $this->crud->create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => request('password'),
            'user_type' => UserType::USER,
            'subscription_ends_at' => request('subscription_ends_at'),
        ]);

        if (backpack_user()->hasRole('admin') && ! empty(request('roles', [])[0])) {
            $role = request('roles', [])[0];
            $roleName = '';

            switch ($role) {
                case UserType::ADMIN:
                    $roleName = 'admin';
                    break;
                case UserType::MANAGER:
                    $roleName = 'manager';
                    break;
                case UserType::USER:
                    $roleName = 'user';
                    break;
            }

            if ($roleName) {
                // Sync the user role, which will remove any old roles and assign the new one
                $user->syncRoles([$roleName]);
            }
        }

        return redirect(backpack_url('user'));
    }

    public function update()
    {
        /** @disregard */
        $user = CRUD::update(CRUD::getCurrentEntry()->id, [
            'name' => request('name'),
            'email' => request('email'),
            'level' => request('level'),
            'credit' => request('credit'),
            'subscription_ends_at' => request('subscription_ends_at'),
        ]);
        if (backpack_user()->hasRole('admin') && ! empty(request('roles', [])[0])) {
            $role = request('roles', [])[0];
            $roleName = '';

            switch ($role) {
                case UserType::ADMIN:
                    $roleName = 'admin';
                    break;
                case UserType::MANAGER:
                    $roleName = 'manager';
                    break;
                case UserType::USER:
                    $roleName = 'user';
                    break;
            }

            if ($roleName) {
                // Sync the user role, which will remove any old roles and assign the new one
                $user->syncRoles([$roleName]);
            }
        }

        return redirect(backpack_url('user'));
    }

    protected function addUserFields()
    {
        CRUD::addFields([
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Name',
            ],
            [
                'name' => 'email',
                'type' => 'email',
                'label' => 'Email',
            ],
            [
                'name' => 'password',
                'type' => 'password',
                'label' => 'Password',
            ],
            [
                'name' => 'password_confirmation',
                'label' => 'Confirm Password',
                'type' => 'password',
            ],
            [
                'name' => 'level',
                'type' => 'select_from_array',
                'options' => UserLevel::asArray(),
                'label' => 'User Level',
            ],
            [
                'name' => 'credit',
                'type' => 'number',
                'label' => 'Credit',
            ],
            [
                'name' => 'subscription_ends_at',
                'label' => 'Subscription Ends At',
            ],
        ]);
        if (backpack_user()->hasRole('admin')) {
            CRUD::addField([
                'label' => trans('backpack::permissionmanager.user_role_permission'),
                'field_unique_name' => 'user_role_permission',
                'type' => 'checklist_dependency',
                'name' => 'roles, permissions',
                'subfields' => [
                    'primary' => [
                        'label' => trans('backpack::permissionmanager.roles'),
                        'name' => 'roles',
                        'entity' => 'roles',
                        'entity_secondary' => 'permissions',
                        'attribute' => 'name',
                        'model' => config('permission.models.role'),
                        'pivot' => true,
                        'number_columns' => 3,
                    ],
                    'secondary' => [
                        'label' => mb_ucfirst(trans('backpack::permissionmanager.permission_plural')),
                        'name' => 'permissions',
                        'entity' => 'permissions',
                        'entity_primary' => 'roles',
                        'attribute' => 'name',
                        'model' => config('permission.models.permission'),
                        'pivot' => true,
                        'number_columns' => 3,
                    ],
                ],
            ]);

            // Include custom JavaScript for single role selection
            CRUD::addField([
                'name' => 'custom_js',
                'type' => 'custom_html',
                'value' => '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const roleCheckboxes = document.querySelectorAll("input[name=\'roles_show[]\']");
                        roleCheckboxes.forEach(checkbox => {
                            checkbox.addEventListener("change", function() {
                                if (this.checked) {
                                    roleCheckboxes.forEach(otherCheckbox => {
                                        if (otherCheckbox !== this) {
                                            otherCheckbox.checked = false;
                                        }
                                    });
                                }
                            });
                        });
                    });
                </script>',
            ]);
        }

    }
}
