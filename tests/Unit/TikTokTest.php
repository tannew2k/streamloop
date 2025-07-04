<?php

namespace Tests\Unit;

use App\Models\Channel;
use App\Services\TikTokService;
use Tests\TestCase;

class TikTokTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_fetch_games(): void
    {
        $games = TikTokService::fetchGameTags();
        $this->assertNotEmpty($games);
    }

    public function test_get_server_url(): void
    {
        $server = TikTokService::getServerUrl();
        $this->assertNotEmpty($server);
        $this->assertStringContainsString('https://', $server);
    }

    public function test_set_username(): void
    {
        $channel = Channel::first();
        $this->assertNotEmpty($channel);
        $tiktok = new TikTokService();
        $tiktok->setUsername($channel->username);
        $this->assertNotEmpty($tiktok->getUsername());
    }

    public function test_get_channel_info(): void
    {
        $channel = Channel::first();
        $this->assertNotEmpty($channel);
        $tiktok = new TikTokService();
        $tiktok->setUsername($channel->username);
        $tiktok->getChannelInfo();
        $this->assertNotEmpty($tiktok->getChannel());
    }

    public function test_live_permission(): void
    {
        $channel = Channel::first();
        $this->assertNotEmpty($channel);
        $tiktok = new TikTokService();
        $tiktok->setUsername($channel->username);
        $tiktok->getChannelInfo();
        $permission = $tiktok->postLivePermission();
        $this->assertNotFalse($permission);
    }

    public function test_about_me(): void
    {
        $channel = Channel::first();
        $this->assertNotEmpty($channel);
        $tiktok = new TikTokService();
        $tiktok->setUsername($channel->username);
        $tiktok->getChannelInfo();
        $about_me = $tiktok->postAboutMe();
        $this->assertNotFalse($about_me);
    }

    public function test_update_screenshot_cover()
    {
        $channel = Channel::first();
        $this->assertNotEmpty($channel);
        $tiktok = new TikTokService();
        $tiktok->setUsername($channel->username);
        $tiktok->getChannelInfo();
        $cover = $tiktok->postScreenshotCover();
        $this->assertNotFalse($cover);
    }

    public function test_create_streamkey()
    {
        $this->markTestSkipped();
        $channel = Channel::first();
        $this->assertNotEmpty($channel);
        $tiktok = new TikTokService();
        $tiktok->setUsername($channel->username);
        $tiktok->getChannelInfo();
        $streamkey = $tiktok->postCreateStreamKey();
        $this->assertNotFalse($streamkey);
    }
}
