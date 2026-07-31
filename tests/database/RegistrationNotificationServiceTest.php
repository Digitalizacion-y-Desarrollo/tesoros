<?php

use App\Database\Seeds\CategorySeeder;
use App\Services\DraftApplicationService;
use App\Services\EmailNotificationService;
use App\Services\EmailSenderInterface;
use App\Services\ParticipantRegistrationMailerInterface;
use App\Services\RegistrationNotificationService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class RegistrationNotificationServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $seed = CategorySeeder::class;

    public function testSendsARegistrationConfirmationWithoutAnAccessCode(): void
    {
        $draft = (new DraftApplicationService($this->db))->create(
            'joven-talento-gastronomia',
            'registro@example.test',
            [
                $this->participant('LOPE900202HMCDFR07', 'Carlos'),
                $this->participant('CARA880303MMCPLN06', 'Andrea'),
            ],
        );
        $mailer = new CapturingRegistrationMailer();

        $this->assertTrue((new RegistrationNotificationService($this->db, $mailer))->send($draft['id']));
        $this->assertCount(1, $mailer->messages);
        $this->assertSame('registro@example.test', $mailer->messages[0]['recipient']);
        $this->assertSame($draft['folio'], $mailer->messages[0]['folio']);
        $this->assertArrayNotHasKey('code', $mailer->messages[0]);
        $this->seeInDatabase('application_histories', [
            'application_id' => $draft['id'],
            'action' => 'registration_email_sent',
        ]);
        $this->dontSeeInDatabase('access_codes', ['application_id' => $draft['id']]);
    }

    public function testProductionPathQueuesRegistrationOnlyOnce(): void
    {
        $draft = (new DraftApplicationService($this->db))->create(
            'cocineras-cocineros-tradicionales',
            'cola-registro@example.test',
            [$this->participant('GODE561231HDFBCD09', 'María')],
        );
        $notifications = new EmailNotificationService($this->db, new RegistrationQueueSender());
        $service = new RegistrationNotificationService($this->db, null, $notifications);

        $this->assertTrue($service->send($draft['id']));
        $this->assertTrue($service->send($draft['id']));
        $this->assertSame(1, $this->db->table('email_queue')
            ->where('application_id', $draft['id'])
            ->where('event', 'registration_created')
            ->countAllResults());
    }

    /**
     * @return array{curp: string, first_name: string, last_name: string, second_last_name: string}
     */
    private function participant(string $curp, string $name): array
    {
        return [
            'curp' => $curp,
            'first_name' => $name,
            'last_name' => 'Prueba',
            'second_last_name' => 'Correo',
        ];
    }
}

final class CapturingRegistrationMailer implements ParticipantRegistrationMailerInterface
{
    /**
     * @var list<array{recipient: string, folio: string, category: string}>
     */
    public array $messages = [];

    public function send(string $recipient, string $folio, string $categoryName): bool
    {
        $this->messages[] = [
            'recipient' => $recipient,
            'folio' => $folio,
            'category' => $categoryName,
        ];

        return true;
    }
}

final class RegistrationQueueSender implements EmailSenderInterface
{
    public function send(string $recipient, string $subject, string $html, string $plainText): bool
    {
        return true;
    }
}
