<?php

namespace App\Tests;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\DataFixtures\TaskTestEditFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    use FixturesTrait;

    private $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient([], ['HTTPS' => true]);

        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger($em);
        $purger->purge();
    }

    public function testTasksListResponse()
    {
        $crawler = $this->client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
    }

    public function testNoTaskWhenStart()
    {
        $crawler = $this->client->request('GET', '/tasks');

        $nbElements = $crawler->filter('div.alert-warning')->count();

        $this->assertEquals($nbElements, 1);
    }

    public function testTaskSeenWhenCreated()
    {
        $crawler = $this->client->request('GET', '/tasks/create');

        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'titre première tache',
            'task[content]' => 'contenu première tache'
        ]);

        $this->client->submit($form);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('h4 a', 'titre première tache');
        $this->assertSelectorTextContains('h4+p', 'contenu première tache');
    }

    public function testTaskEdit()
    {
        self::bootKernel();

        $this->loadFixtures([TaskTestEditFixtures::class]);

        $repository = self::$container->get(TaskRepository::class);
        $task = $repository->findOneByTitle("viaFixtures");
        $id = $task->getId();

        $crawler = $this->client->request('GET', '/tasks/' . $id . '/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'titre modifié',
            'task[content]' => 'contenu modifié'
        ]);


        $this->client->submit($form);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('h4 a', 'titre modifié');
        $this->assertSelectorTextContains('h4+p', 'contenu modifié');
    }
    
}
