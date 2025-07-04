<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChannelStatus;
use App\Enums\HashTagEnum;
use App\Enums\LiveStatusEnum;
use App\Enums\RegionEnum;
use App\Enums\StreamTypeEnum;
use App\Http\Requests\ChannelCreateRequest;
use App\Http\Requests\ChannelUpdateRequest;
use App\Jobs\CreateStreamKeyJob;
use App\Jobs\DownloadVideoJob;
use App\Jobs\EndLiveStreamJob;
use App\Jobs\StartLiveStreamJob;
use App\Models\Channel;
use App\Traits\CrudPermissionTrait;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Class ChannelCrudController
 *
 * @property-read CrudPanel $crud
 */
class ChannelCrudController extends CrudController
{
    use CreateOperation;
    use CrudPermissionTrait;
    use DeleteOperation;
    use ListOperation;
    use ShowOperation;
    use UpdateOperation { update as traitUpdate; }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Channel::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/channel');
        CRUD::setEntityNameStrings('channel', 'channels');
        $this->setAccessUsingPermissions();

        $user = backpack_user();

        if ($user->hasRole('admin')) {
            $this->crud->allowAccess(['list', 'create', 'update', 'delete']);
        }

        if ($user->hasRole('user')) {
            $this->crud->allowAccess(['list', 'create', 'update', 'delete']);
            $this->crud->addClause('where', 'user_id', '=', $user->id);
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
        CRUD::column([
            'name' => 'id',
            'label' => '#',
        ]);

        CRUD::column([
            'name' => 'username',
            'label' => 'Username',
            'type' => 'text',
        ]);

        CRUD::column('proxy')->label('Proxy');

        CRUD::column([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'enum',
            'options' => ChannelStatus::asSelectArray(),
        ]);

        CRUD::column([
            'name' => 'user_id',
            'label' => 'User',
            'type' => 'select',
            'entity' => 'user',
            'attribute' => 'name',
            'model' => 'App\Models\User',
        ]);

        CRUD::column([
            'name' => 'live_status',
            'label' => 'Live Status',
            'type' => 'enum',
            'options' => LiveStatusEnum::asSelectArray(),
        ]);

        // remove show button
        $this->crud->removeButton('show');

        // add generate tiktok ids button
        $this->crud->button('generate')->stack('line')->view('crud::buttons.quick')->meta([
            'access' => true,
            'label' => 'Generate ID',
            // 'icon' => 'la la-refresh',
            'wrapper' => [
                'element' => 'a',
                'class' => 'btn btn-primary',
            ],
            'ajax' => [
                'refreshCrudTable' => true,
            ],
        ]);

        // add generate tiktok ids button
        $this->crud->button('generate')->stack('line')->view('crud::buttons.quick')->meta([
            'access' => true,
            'label' => 'Generate ID',
            // 'icon' => 'la la-refresh',
            'wrapper' => [
                'element' => 'a',
                'class' => 'btn btn-primary',
            ],
            'ajax' => [
                'refreshCrudTable' => true,
            ],
        ]);

        // add start button
        $this->crud->button('start')->stack('line')->view('crud::buttons.quick')->meta([
            'label' => 'Start',
            // 'icon' => 'la la-play',
            'wrapper' => [
                'element' => 'a',
                'class' => 'btn btn-success',
            ],
            'ajax' => [
                'refreshCrudTable' => true,
            ],
        ]);

        // add stop button
        $this->crud->button('stop')->stack('line')->view('crud::buttons.quick')->meta([
            'label' => 'Stop',
            'wrapper' => [
                'element' => 'a',
                'class' => 'btn btn-danger',
            ],
            'ajax' => [
                'refreshCrudTable' => true,
            ],
        ]);

        // add edit button
        $this->crud->button('update')->stack('line')->view('crud::buttons.quick')->meta([
            'label' => 'Edit',
            'wrapper' => [
                'href' => function ($entry, $crud) {
                    return backpack_url("channel/$entry->id/edit");
                },
                'class' => 'btn btn-info',
            ],
        ]);

        // order buttons
        $this->crud->orderButtons('line', ['generate', 'start', 'stop', 'update']);
        $this->crud->removeButtons(['delete', 'show']);

        // set access
        $this->crud->setAccessCondition('start', function ($entry) {
            if (backpack_user()->hasRole('admin')) {
                return true;
            }
            if ($entry->user_id === backpack_user()->id) {
                return true;
            }
            if ($entry->live_status === LiveStatusEnum::OFFLINE || $entry->live_status === LiveStatusEnum::ERROR) {
                return true;
            }

            return false;
        });
        $this->crud->setAccessCondition('stop', function ($entry) {
            if (backpack_user()->hasRole('admin')) {
                return true;
            }
            if ($entry->user_id === backpack_user()->id) {
                return true;
            }

            return ! ($entry->live_status === LiveStatusEnum::OFFLINE || $entry->live_status === LiveStatusEnum::ERROR);
        });
        $this->crud->setAccessCondition('update', function ($entry) {
            if (backpack_user()->hasRole('admin')) {
                return true;
            }
            if ($entry->user_id === backpack_user()->id) {
                return true;
            }

            return backpack_user()->hasRole('admin') || $entry->user_id === backpack_user()->id;
        });
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
        CRUD::setValidation(ChannelCreateRequest::class);
        // CRUD::setFromDb(); // set fields from db columns.

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
        CRUD::field([
            'name' => 'username',
            'label' => 'Username',
            'type' => 'text',
            'hint' => 'Username of the channel.',
        ]);
        CRUD::field([
            'access' => backpack_user()->hasRole('admin'),
            'name' => 'user_id',
            'label' => 'User',
            'type' => 'select',
            'entity' => 'user',
            'attribute' => 'email',
            'model' => 'App\Models\User',
            'hint' => 'User who owns this channel.',
            'attributes' => [
                'required' => true,
            ],
        ]);
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
        $this->crud->setOperationSetting('showDeleteButton', true);
        $this->crud->setValidation(ChannelUpdateRequest::class);

        CRUD::field([
            'name' => 'username',
            'label' => 'Username',
            'type' => 'text',
            'hint' => 'Username of the channel.',
        ]);

        CRUD::field([
            'name' => 'cookies',
            'label' => 'Cookies',
            'type' => 'textarea',
            'hint' => 'Format: key1=value1; key2=value2; ...',
            'attributes' => [
                'rows' => 5,
            ],
        ]);
        CRUD::field([
            'name' => 'proxy',
            'label' => 'Proxy',
            'type' => 'text',
            'hint' => 'Format: http://username:password@ip:port',
        ]);
        CRUD::field([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'radio',
            'options' => ChannelStatus::asSelectArray(),
            'inline' => true,
            'default' => ChannelStatus::ACTIVE,
        ]);
        CRUD::field([
            'name' => 'install_id',
            'label' => 'Install ID',
            'type' => 'text',
            'hint' => 'Install ID of the channel.',
            'attributes' => [
                'readonly' => true,
            ],
        ]);
        CRUD::field([
            'name' => 'device_id',
            'label' => 'Device ID',
            'type' => 'text',
            'hint' => 'Device ID of the channel.',
            'attributes' => [
                'readonly' => true,
            ],
        ]);
        CRUD::field([
            'access' => backpack_user()->hasRole('admin'),
            'name' => 'user_id',
            'label' => 'User',
            'type' => 'select',
            'entity' => 'user',
            'attribute' => 'email',
            'model' => 'App\Models\User',
            'hint' => 'User who owns this channel.',
            'attributes' => [
                'required' => true,
            ],
        ]);
        CRUD::field([
            'name' => 'video_url',
            'label' => 'Video URL',
            'type' => 'text',
            'hint' => 'URL of the video to be played.',
        ]);
        CRUD::field([
            'name' => 'title',
            'label' => 'Title',
            'type' => 'text',
            'hint' => 'Title of the video.',
        ]);
        CRUD::field([
            'name' => 'hash_tag_id',
            'label' => 'Hash Tag ID',
            'type' => 'enum',
            'hint' => 'Hash Tag ID of the video.',
            'options' => HashTagEnum::asSelectArray(),
        ]);
        CRUD::field([
            'name' => 'message',
            'label' => 'Message',
            'type' => 'textarea',
            'hint' => 'Message to be displayed.',
            'attributes' => [
                'rows' => 5,
            ],
            'readonly' => true,
        ]);
        CRUD::field([
            'name' => 'region',
            'label' => 'Region',
            'type' => 'select_from_array',
            'options' => RegionEnum::asSelectArray(),
            'hint' => 'Region of the channel.',
        ]);
        CRUD::field([
            'name' => 'stream_type',
            'label' => 'Stream Type',
            'type' => 'radio',
            'options' => StreamTypeEnum::asSelectArray()
        ]);
    }

    public function update()
    {
        // do something before validation, before save, before everything; for example:
        // $this->crud->addField(['type' => 'hidden', 'name' => 'author_id']);
        // $this->crud->removeField('password_confirmation');

        // Note: By default Backpack ONLY saves the inputs that were added on page using Backpack fields.
        // This is done by stripping the request of all inputs that do NOT match Backpack fields for this
        // particular operation. This is an added security layer, to protect your database from malicious
        // users who could theoretically add inputs using DeveloperTools or JavaScript. If you're not properly
        // using $guarded or $fillable on your model, malicious inputs could get you into trouble.

        // However, if you know you have proper $guarded or $fillable on your model, and you want to manipulate
        // the request directly to add or remove request parameters, you can also do that.
        // We have a config value you can set, either inside your operation in `config/backpack/crud.php` if
        // you want it to apply to all CRUDs, or inside a particular CrudController:
        // $this->crud->setOperationSetting('saveAllInputsExcept', ['username']);
        // The above will make Backpack store all inputs EXCEPT for the ones it uses for various features.
        // So you can manipulate the request and add any request variable you'd like.
        // $this->crud->getRequest()->request->add(['author_id'=> backpack_user()->id]);
        // $this->crud->getRequest()->request->remove('password_confirmation');
        // $this->crud->getRequest()->request->add(['author_id'=> backpack_user()->id]);
        $this->crud->getRequest()->request->remove('username');
        $this->crud->getRequest()->request->remove('message');

        $response = $this->traitUpdate();

        // do something after save
        return $response;
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();
    }

    /**
     * Start the channel.
     */
    public function startLiveStream(int $id): JsonResponse
    {
        $channel = Channel::whereId($id)->first();
        if (! $channel) {
            abort(404);
        }
        $channel->live_status = LiveStatusEnum::STARTING;
        $channel->save();
        $channelInfo = $channel->toArray();
        if (app()->environment('local')) {
            CreateStreamKeyJob::dispatch($channelInfo)
                ->onQueue('live-stream');
        } else {
            CreateStreamKeyJob::dispatch($channelInfo)
                ->onConnection('rabbitmq')
                ->onQueue('live-stream');
        }

        return response()->json([
            'status' => 1,
            'message' => 'Live stream started successfully',
        ]);
    }

    /**
     * Stop the channel.
     */
    public function stopLiveStream(int $id): JsonResponse
    {
        $channel = Channel::whereId($id)->first();
        if ($channel) {
            $channel->update([
                'live_status' => LiveStatusEnum::STOPPING,
            ]);
            EndLiveStreamJob::dispatch($channel->toArray());

            return response()->json([
                'status' => 1,
                'message' => 'Live stream stopped successfully',
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Channel not found',
            ])->setStatusCode(404);
        }
    }

    /**
     * Stop the channel.
     */
    public function generateIds(int $id): JsonResponse
    {
        $randomDeviceId = rand(7250000000000000000, 7351147085025500000);
        $randomInstallId = rand(7250000000000000000, 7351147085025500000);
        // if username exists, update the cookies
        $channel = Channel::find($id);
        if ($channel) {
            $channel->update([
                'install_id' => $randomInstallId,
                'device_id' => $randomDeviceId,
            ]);

            return response()->json([
                'status' => 1,
                'message' => 'Install ID and Device ID updated successfully',
                'data' => [
                    'username' => $channel->username,
                ],
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Channel not found',
            ]);
        }
    }

    /**
     * Update the status of the channel.
     * @param Request $request 
     * @param int $id 
     * @return JsonResponse|void 
     * @throws BindingResolutionException 
     * @throws InvalidArgumentException 
     */
    public function status(Request $request, int $id)
    {
        $statuses = LiveStatusEnum::asArray();
        $values = array_values($statuses);
        $validator = Validator::make($request->all(), [
            'live_status' => 'required|in:'.implode(',', $values)
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Invalid status',
            ])->setStatusCode(400);
        }
        $channel = Channel::whereId($id)->first();
        if (! $channel) {
            return response()->json([
                'status' => 0,
                'message' => 'Channel not found',
            ])->setStatusCode(404);
        }
        $channel->live_status = $request->get('live_status');
        $channel->save();
    }
}
