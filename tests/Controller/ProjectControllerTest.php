<?php

declare(strict_types=1);

namespace App\Controller;

use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectControllerTest extends WebTestCase
{
    use FixturesTrait;

    protected function setUp(): void
    {
        $this->loadFixtureFiles([
            __DIR__.'/../fixtures/Controller/ProjectControllerTest.yaml',
        ]);
    }

    public function testIndex()
    {
        $client = self::createClient();
        $client->request('GET', '/en/project/');
        $this->assertResponseRedirects('/en/user/login');

        $client = self::createAuthorizedClient('user');
        $crawler = $client->request('GET', '/en/project/');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(1, $crawler->filter('#content table tbody tr')->count());
        $this->assertStringNotContainsString('No data.', $crawler->filter('#content table tbody tr')->text(null, true));
        $this->assertEquals(0, $crawler->filter('#content a[href*="new"]')->count());
        $this->assertEquals(0, $crawler->filter('#content a[href*="edit"]')->count());

        $client = self::createAuthorizedClient('editor');
        $crawler = $client->request('GET', '/en/project/');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(1, $crawler->filter('#content table tbody tr')->count());
        $this->assertStringNotContainsString('No data.', $crawler->filter('#content table tbody tr')->text(null, true));
        $this->assertEquals(1, $crawler->filter('#content a[href*="new"]')->count());
        $this->assertEquals(1, $crawler->filter('#content a[href*="edit"]')->count());

        // can sort
        $client->request('GET', '/en/project/?sort=p.id');
        $this->assertResponseIsSuccessful();
        $client->request('GET', '/en/project/?sort=c.id');
        $this->assertResponseIsSuccessful();
        $client->request('GET', '/en/project/?sort=p.state');
        $this->assertResponseIsSuccessful();
        $client->request('GET', '/en/project/?sort=p.name');
        $this->assertResponseIsSuccessful();
        $client->request('GET', '/en/project/?sort=u.id');
        $this->assertResponseIsSuccessful();

        // can search
        $crawler = $client->request('GET', '/en/project/?query=test&states[]=ProjectConstant.STATE_INITIAL&customers[]=1&users[]=');
        $this->assertEquals(1, $crawler->filter('#content table tbody tr')->count());
        $this->assertStringNotContainsString('No data.', $crawler->filter('#content table tbody tr')->text(null, true));
        $crawler = $client->request('GET', '/en/project/?query=xxxxxxxxxx');
        $this->assertEquals(1, $crawler->filter('#content table tbody tr')->count());
        $this->assertStringContainsString('No data.', $crawler->filter('#content table tbody tr')->text(null, true));
    }

    public function testNew()
    {
        $client = self::createClient();
        $client->request('GET', '/en/project/new');
        $this->assertResponseRedirects('/en/user/login');

        $client = self::createAuthorizedClient('user');
        $client->request('GET', '/en/project/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client = self::createAuthorizedClient('editor');
        $crawler = $client->request('GET', '/en/project/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $client->submit($form, [
            'project[customer]' => 1,
            'project[state]' => 'ProjectConstant.STATE_INITIAL',
            'project[name]' => 'test',
        ]);
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('successfully', $crawler->filter('#content .alert-success')->text(null, true));
        $this->assertEquals(2, $crawler->filter('#content table tbody tr')->count());
    }

    public function testShow()
    {
        $client = self::createClient();
        $client->request('GET', '/en/project/1');
        $this->assertResponseRedirects('/en/user/login');

        $client = self::createAuthorizedClient('user');
        $crawler = $client->request('GET', '/en/project/1');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(0, $crawler->filter('#content a[href*="edit"]')->count());

        $client = self::createAuthorizedClient('editor');
        $crawler = $client->request('GET', '/en/project/1');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(1, $crawler->filter('#content a[href*="edit"]')->count());
    }

    public function testEdit()
    {
        $client = self::createClient();
        $client->request('GET', '/en/project/1/edit');
        $this->assertResponseRedirects('/en/user/login');

        $client = self::createAuthorizedClient('user');
        $client->request('GET', '/en/project/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client = self::createAuthorizedClient('editor');
        $crawler = $client->request('GET', '/en/project/1/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $client->submit($form, [
            'project[name]' => 'new name',
        ]);
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('successfully', $crawler->filter('#content .alert-success')->text(null, true));
        $this->assertStringContainsString('new name', $crawler->filter('#content th:contains("Project name") + td')->text(null, true));
    }

    public function testDelete()
    {
        $client = self::createClient();
        $client->request('DELETE', '/en/project/1');
        $this->assertResponseRedirects('/en/user/login');

        $client = $this->createAuthorizedClient('user');
        $client->request('DELETE', '/en/project/1');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        // cannot delete without csrf token
        $client = $this->createAuthorizedClient('editor');
        $client->request('DELETE', '/en/project/1');
        $client->request('GET', '/en/project/1');
        $this->assertNotEquals(404, $client->getResponse()->getStatusCode());

        // can delete in right way
        $client = $this->createAuthorizedClient('editor');
        $crawler = $client->request('GET', '/en/project/1/edit');
        $form = $crawler->selectButton('Delete...')->form();
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('successfully', $crawler->filter('#content .alert-success')->text(null, true));
        $client->request('GET', '/en/project/1');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        // @see https://stackoverflow.com/questions/59672899/symfony-4-4-deprecation-warning-for-multiple-clients-in-project-test-is-deprecated
        self::ensureKernelShutdown();

        return parent::createClient($options, $server);
    }

    private static function createAuthorizedClient(string $projectType, string $password = 'password'): KernelBrowser
    {
        return static::createClient([], [
            'PHP_AUTH_USER' => $projectType.'@test.com',
            'PHP_AUTH_PW' => $password,
        ]);
    }
}
