<?php

namespace Database\Seeders;

use App\Enums\ChannelStatus;
use App\Enums\LiveStatusEnum;
use App\Models\Channel;
use App\Models\User;
use Backpack\PermissionManager\app\Models\Permission;
use Backpack\PermissionManager\app\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database Permission seed.

     * Permissions are fixed in code and are seeded here.
     * use 'php artisan db:seed --class=PermissionSeeder --force' in production
     *
     * @return void
     */
    public function run()
    {
        // Create roles
        collect([
            'admin',
            'manager',
            'user',
        ])->each(
            fn (string $role) => Role::firstOrCreate(['name' => $role])
        );

        // Add all permissions to admin
        Role::findByName('admin')
            ->givePermissionTo(Permission::all());

        // Create permission for each combination of table.level
        collect(['users', 'channels', 'roles']) // tables
            ->crossJoin(['see', 'edit']) // levels
            ->each(
                fn (array $item) => Permission::firstOrCreate(['name' => implode('.', $item)])
            );

        // Create admin user assign admin role
        $randomPassword = "Hello@123";
        Artisan::call('backpack:user -E test@gmail.com -P '.$randomPassword.' -N Test');
        $firstUser = User::first();
        Role::findByName('admin')->users()->attach($firstUser->id);
        Log::debug('Email: '.$firstUser->email.' Password: '.$randomPassword);

        // Create channel
        Channel::create([
            'username' => 'cyrus_navas',
            'cookies' => 'tt_csrf_token=ei3AcKsM-_8V3MkBFubr9pLFpp8fem7_uuno; passport_csrf_token=099312326746ebdf4a88239b661dec07; passport_csrf_token_default=099312326746ebdf4a88239b661dec07; s_v_web_id=verify_lwwxptss_LHcowAFx_4sNL_4ZPG_8xob_xHpdmxBzyhMb; store-country-code-src=uid; tt_chain_token=EHzv35qrJDaaiF5F/P4OHQ==; _ttp=2hIzwOej0KyxrurJVNXirhsG7Rx; _ga=GA1.1.1950055854.1717296616; _fbp=fb.1.1717296630696.2038062068; i18next=en; d_ticket=4911db5ab609121333e83101994fc9e4a118d; multi_sids=238956417133748224%3A2cb92c55a3f8dc7da7fd31625a17c91e; cmpl_token=AgQQAPPdF-RO0tNuHz_gPHk5_DpCRuhT_5zZYNcjpg; sid_guard=2cb92c55a3f8dc7da7fd31625a17c91e%7C1717299019%7C15552000%7CFri%2C+29-Nov-2024+03%3A30%3A19+GMT; uid_tt=9409fc64524d99c9358eb6a62ce4887a5a69689b3928ef2c4d6c8c9349916bae; uid_tt_ss=9409fc64524d99c9358eb6a62ce4887a5a69689b3928ef2c4d6c8c9349916bae; sid_tt=2cb92c55a3f8dc7da7fd31625a17c91e; sessionid=2cb92c55a3f8dc7da7fd31625a17c91e; sessionid_ss=2cb92c55a3f8dc7da7fd31625a17c91e; sid_ucp_v1=1.0.0-KDg3OTMwZmQxNTNmNDY1YmI2ODUxZWU3YTc1YmIwYmJlNWQyY2Y4MWIKIAiAwLrAhrO8qAMQy87vsgYYswsgDDDe-6brBTgEQOoHEAQaB3VzZWFzdDUiIDJjYjkyYzU1YTNmOGRjN2RhN2ZkMzE2MjVhMTdjOTFl; ssid_ucp_v1=1.0.0-KDg3OTMwZmQxNTNmNDY1YmI2ODUxZWU3YTc1YmIwYmJlNWQyY2Y4MWIKIAiAwLrAhrO8qAMQy87vsgYYswsgDDDe-6brBTgEQOoHEAQaB3VzZWFzdDUiIDJjYjkyYzU1YTNmOGRjN2RhN2ZkMzE2MjVhMTdjOTFl; store-idc=useast5; store-country-code=us; tt-target-idc=useast8; tt-target-idc-sign=B7VQgSrIjSLxTNhIfoma4QlqYIlCLi0iddcltPHD73HbNtBBubPCFt8tXUeexg90tr7ETMFwHy2qfpQuaBEEwNAnBjAQ75ORjEdGo-5YtGgoVCi3mYzKv43m2BprXIF-1EMUs8HdMCjIPb0r2RghOQGpRbBteBBERGCJVpG2VIZm-1A0Pyui09OeJeIRBi0lVikiGtD1gA9K2jA67W40ivw__sGLMKR-8Bs8noXpgaw2zO5jb3bO6kQlnQWdsXN3pPfSdaW66ignGhddwtCtvhyEhCZYZzDgFjUKiXEyx_n4LX4p-hmbvnfWoE2QBIsmj_Oq9RHktpYID9_vKafzwJ3iuLzIc-CJvBIrTg6W7A_r_yQKxZ2ohOzQ-PDc9dkqn57TzJEwpjDrMOGuT1x0Hh0OCocYVlVytiAzNlzyXMWdE4boy3atBpSE9mXQXmfuFgvl5GtYWcBObAmsE3UArngeY0Fn-JpEIPKsHrUMaT_760amsLy44-Jt0Lj-DXHW; ttwid=1%7CXoUR9pLZCGY9nkiIr0u5ZvAHEi0I-y_NfGXJEioRec8%7C1717321977%7Cbadf1a6eb1828baa6b6c250d8714bbb8427878f45119228f7c7e87350d5c1541; msToken=IliMknOt2rNqjlNvi0w-H47Km4N78_2r3YuG9VzhXYreeemp_STK9rM6MKrUsNTfmxEj4pls_eGYoH1aqZUu6_5sZnPiWMkNVWmWH5dQCLxJmd3Jj8VfDll6QSggQnW9i-m3iRtON6Pu; _ga_BZBQ2QHQSP=GS1.1.1717321988.4.1.1717321988.0.0.0; _m4b_theme_=new; odin_tt=e19373768a939b3a5bdd2f86b038929c6ef6b615d9a89c4167a035d52c79d7cc3adad518721bbd9a84318fd217ce248701f6b55297937da494d757d6bca5c6b8',
            'proxy' => 'socks5://ZqvCIVwWsH:k0oBRUTxKo@139.171.175.79:8904',
            'status' => ChannelStatus::ACTIVE,
            'user_id' => $firstUser->id,
            'live_status' => LiveStatusEnum::OFFLINE,
            'install_id' => rand(7250000000000000000, 7351147085025500000),
            'device_id' => rand(7250000000000000000, 7351147085025500000),
            'video_url' => 'https://drive.google.com/file/d/12b1nOaAxxFdxoHCoyXcfnENqcP313zT-/view',
        ]);
    }
}
