<?php

declare(strict_types=1);

namespace App\Controller;

use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    use FixturesTrait;

    protected function setUp(): void
    {
        $this->loadFixtureFiles([
            __DIR__.'/../fixtures/Controller/UserControllerTest.yaml',
        ]);
    }

    public function testLogin()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/en/user/login');

        $form = $crawler->selectButton('Login')->form();
        $client->submit($form, [
            'email' => 'user@test.com',
            'password' => 'password',
        ]);
        $this->assertResponseRedirects('/en/');
    }

    public function testLogout()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/en/user/login');

        // use session instead of BASIC authed user because BASIC authed user also authed even after logout
        $form = $crawler->selectButton('Login')->form();
        $client->submit($form, [
            'email' => 'user@test.com',
            'password' => 'password',
        ]);
        $client->followRedirect();

        $client->request('GET', '/en/user/logout');

        // cannot access to inner page after logged out
        $client->request('GET', '/en/user/');
        $this->assertResponseRedirects('/en/user/login');
    }

    public function testProfileShow()
    {
        $client = self::createClient();
        $client->request('GET', '/en/user/profile');
        $this->assertResponseRedirects('/en/user/login');

        $client = self::createAuthorizedClient('user');
        $crawler = $client->request('GET', '/en/user/profile');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('user@test.com', $crawler->filter('#content th:contains("Email") + td')->text(null, true));
    }

    public function testProfileEdit()
    {
        $client = self::createClient();
        $client->request('GET', '/en/user/profile/edit');
        $this->assertResponseRedirects('/en/user/login');

        $client = self::createAuthorizedClient('user');
        $crawler = $client->request('GET', '/en/user/profile/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $client->submit($form, [
            'user_profile_edit[displayName]' => 'new name',
        ]);
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('successfully', $crawler->filter('#content .alert-success')->text(null, true));
        $this->assertStringContainsString('new name', $crawler->filter('#content th:contains("Display name") + td')->text(null, true));

    }

    public function testProfileChangePassword()
    {
        $client = self::createClient();
        $client->request('GET', '/en/user/profile/change_password');
        $this->assertResponseRedirects('/en/user/login');

        $client = self::createAuthorizedClient('user');
        $crawler = $client->request('GET', '/en/user/profile/change_password');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $crawler = $client->submit($form, [
            'user_change_password[oldPassword]' => 'wrong-password',
            'user_change_password[newPassword]' => 'password',
        ]);
        $this->assertStringContainsString('wrong', $crawler->filter('#content .form-error-message')->text(null, true));

        $form = $crawler->selectButton('Save')->form();
        $client->submit($form, [
            'user_change_password[oldPassword]' => 'password',
            'user_change_password[newPassword]' => 'password',
        ]);
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('successfully', $crawler->filter('#content .alert-success')->text(null, true));
    }

    public function testIndex()
    {
        $client = self::createClient();
        $client->request('GET', '/en/user/');
        $this->assertResponseRedirects('/en/user/login');

        $client = self::createAuthorizedClient('user');
        $crawler = $client->request('GET', '/en/user/');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(4, $crawler->filter('#content table tbody tr')->count());
        $this->assertEquals(0, $crawler->filter('#content a[href*="new"]')->count());
        $this->assertEquals(0, $crawler->filter('#content a[href*="edit"]')->count());

        $client = self::createAuthorizedClient('editor');
        $crawler = $client->request('GET', '/en/user/');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(4, $crawler->filter('#content table tbody tr')->count());
        $this->assertEquals(0, $crawler->filter('#content a[href*="new"]')->count());
        $this->assertEquals(0, $crawler->filter('#content a[href*="edit"]')->count());

        $client = self::createAuthorizedClient('userEditor');
        $crawler = $client->request('GET', '/en/user/');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(4, $crawler->filter('#content table tbody tr')->count());
        $this->assertEquals(1, $crawler->filter('#content a[href*="new"]')->count());
        $this->assertEquals(3, $crawler->filter('#content a[href*="edit"]')->count());

        $client = self::createAuthorizedClient('admin');
        $crawler = $client->request('GET', '/en/user/');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(4, $crawler->filter('#content table tbody tr')->count());
        $this->assertEquals(1, $crawler->filter('#content a[href*="new"]')->count());
        $this->assertEquals(4, $crawler->filter('#content a[href*="edit"]')->count());

        // can sort
        $client->request('GET', '/en/user/?sort=u.id');
        $this->assertResponseIsSuccessful();
        $client->request('GET', '/en/user/?sort=u.email');
        $this->assertResponseIsSuccessful();
        $client->request('GET', '/en/user/?sort=u.roles');
        $this->assertResponseIsSuccessful();
        $client->request('GET', '/en/user/?sort=u.displayName');
        $this->assertResponseIsSuccessful();
        $client->request('GET', '/en/user/?sort=u.lastLoggedInAt');
        $this->assertResponseIsSuccessful();

        // can search
        $crawler = $client->request('GET', '/en/user/?query=admin');
        $this->assertEquals(1, $crawler->filter('#content table tbody tr')->count());
        $this->assertStringNotContainsString('No data.', $crawler->filter('#content table tbody tr')->text(null, true));
        $crawler = $client->request('GET', '/en/user/?query=xxxxxxxxxx');
        $this->assertEquals(1, $crawler->filter('#content table tbody tr')->count());
        $this->assertStringContainsString('No data.', $crawler->filter('#content table tbody tr')->text(null, true));
    }

    public function testNew()
    {
        $client = self::createClient();
        $client->request('GET', '/en/user/new');
        $this->assertResponseRedirects('/en/user/login');

        $client = self::createAuthorizedClient('user');
        $client->request('GET', '/en/user/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client = self::createAuthorizedClient('editor');
        $client->request('GET', '/en/user/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client = self::createAuthorizedClient('userEditor');
        $crawler = $client->request('GET', '/en/user/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $client->submit($form, [
            'user[email]' => 'new_user@test.com',
            'user[plainPassword]' => 'password',
        ]);
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('successfully', $crawler->filter('#content .alert-success')->text(null, true));
        $this->assertEquals(5, $crawler->filter('#content table tbody tr')->count());

        $crawler = $client->request('GET', '/en/user/new');
        $form = $crawler->selectButton('Save')->form();
        $crawler = $client->submit($form, [
            'user[email]' => 'user@test.com',
            'user[plainPassword]' => 'password',
        ]);
        $this->assertStringContainsString('already used', $crawler->filter('#content .form-error-message')->text(null, true));
    }

    public function testShow()
    {
        $client = self::createClient();
        $client->request('GET', '/en/user/1');
        $this->assertResponseRedirects('/en/user/login');

        $client = self::createAuthorizedClient('user');
        $crawler = $client->request('GET', '/en/user/1');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(0, $crawler->filter('#content a[href*="edit"]')->count());

        $client = self::createAuthorizedClient('editor');
        $crawler = $client->request('GET', '/en/user/1');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(0, $crawler->filter('#content a[href*="edit"]')->count());

        // can edit other than admin
        $client = self::createAuthorizedClient('userEditor');
        $crawler = $client->request('GET', '/en/user/1');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(1, $crawler->filter('#content a[href*="edit"]')->count());

        // cannot edit admin
        $client = self::createAuthorizedClient('userEditor');
        $crawler = $client->request('GET', '/en/user/4');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(0, $crawler->filter('#content a[href*="edit"]')->count());

        // admin can edit admin
        $client = self::createAuthorizedClient('admin');
        $crawler = $client->request('GET', '/en/user/3');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(1, $crawler->filter('#content a[href*="edit"]')->count());
    }

    public function testEdit()
    {
        $client = self::createClient();
        $client->request('GET', '/en/user/1/edit');
        $this->assertResponseRedirects('/en/user/login');

        $client = self::createAuthorizedClient('user');
        $client->request('GET', '/en/user/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client = self::createAuthorizedClient('editor');
        $client->request('GET', '/en/user/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        // can edit other than admin
        $client = self::createAuthorizedClient('userEditor');
        $client->request('GET', '/en/user/1/edit');
        $this->assertResponseIsSuccessful();

        // cannot edit admin
        $client = self::createAuthorizedClient('userEditor');
        $client->request('GET', '/en/user/4/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        // admin can edit admin
        $client = self::createAuthorizedClient('admin');
        $crawler = $client->request('GET', '/en/user/3/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $client->submit($form, [
            'user_edit[displayName]' => 'new name',
        ]);
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('successfully', $crawler->filter('#content .alert-success')->text(null, true));
        $this->assertStringContainsString('new name', $crawler->filter('#content th:contains("Display name") + td')->text(null, true));
    }

    public function testChangePassword()
    {
        $client = self::createClient();
        $client->request('GET', '/en/user/1/change_password');
        $this->assertResponseRedirects('/en/user/login');

        $client = self::createAuthorizedClient('user');
        $client->request('GET', '/en/user/1/change_password');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client = self::createAuthorizedClient('editor');
        $client->request('GET', '/en/user/1/change_password');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        // can edit other than admin
        $client = self::createAuthorizedClient('userEditor');
        $client->request('GET', '/en/user/1/change_password');
        $this->assertResponseIsSuccessful();

        // cannot edit admin
        $client = self::createAuthorizedClient('userEditor');
        $client->request('GET', '/en/user/4/change_password');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        // admin can edit admin
        $client = self::createAuthorizedClient('admin');
        $crawler = $client->request('GET', '/en/user/3/change_password'); // userEditor
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $crawler = $client->submit($form, [
            'user_change_password[oldPassword]' => 'wrong_password',
            'user_change_password[newPassword]' => 'new_password',
        ]);
        $this->assertStringContainsString('Current password is wrong', $crawler->filter('#content .form-error-message')->text(null, true));

        $form = $crawler->selectButton('Save')->form();
        $client->submit($form, [
            'user_change_password[oldPassword]' => 'password',
            'user_change_password[newPassword]' => 'new_password',
        ]);
        $client = self::createAuthorizedClient('userEditor', 'new_password');
        $client->request('GET', '/en/');
        $this->assertResponseIsSuccessful();
    }

    public function testDelete()
    {
        $client = self::createClient();
        $client->request('GET', '/en/user/delete/1');
        $this->assertResponseRedirects('/en/user/login');

        $client = self::createAuthorizedClient('user');
        $client->request('GET', '/en/user/delete/1');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client = self::createAuthorizedClient('editor');
        $client->request('GET', '/en/user/delete/1');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        // cannot delete admin
        $client = self::createAuthorizedClient('userEditor');
        $client->request('GET', '/en/user/delete/4');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        // cannot delete oneself
        $client = self::createAuthorizedClient('userEditor');
        $client->request('GET', '/en/user/delete/3');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        // can delete other than admin and oneself
        $client = self::createAuthorizedClient('userEditor');
        $client->request('GET', '/en/user/delete/1');
        $this->assertResponseIsSuccessful();

        // cannot delete without csrf token
        $client = self::createAuthorizedClient('userEditor');
        $client->request('DELETE', '/en/user/delete/1');
        $client->request('GET', '/en/user/1');
        $this->assertNotEquals(404, $client->getResponse()->getStatusCode());

        // can delete in right way
        $client = self::createAuthorizedClient('userEditor');
        $crawler = $client->request('GET', '/en/user/delete/1');
        $form = $crawler->selectButton('Do delete...')->form();
        $client->submit($form, [
            'user_delete[alternateUser]' => 2,
        ]);
        $client->request('GET', '/en/user/1');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        // @see https://stackoverflow.com/questions/59672899/symfony-4-4-deprecation-warning-for-multiple-clients-in-user-test-is-deprecated
        self::ensureKernelShutdown();

        return parent::createClient($options, $server);
    }

    private static function createAuthorizedClient(string $userType, string $password = 'password'): KernelBrowser
    {
        return static::createClient([], [
            'PHP_AUTH_USER' => $userType.'@test.com',
            'PHP_AUTH_PW' => $password,
        ]);
    }
}
