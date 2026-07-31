<?php

use App\Database\Seeds\CategorySeeder;
use App\Services\DraftApplicationService;
use App\Services\EmailNotificationService;
use App\Services\EmailSenderInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class EmailNotificationServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $seed = CategorySeeder::class;

    public function testEnqueueIsIdempotentAndSuccessfulDispatchIsTraceable(): void
    {
        $draft = $this->draft('cola@example.test', 'GODE561231HDFBCD09');
        $sender = new CapturingEmailSender(true);
        $service = new EmailNotificationService($this->db, $sender);

        $first = $service->enqueueApplication(
            $draft['id'],
            'application_submitted',
            [],
            'submission:' . $draft['id'],
        );
        $second = $service->enqueueApplication(
            $draft['id'],
            'application_submitted',
            [],
            'submission:' . $draft['id'],
        );

        $this->assertSame($first, $second);
        $this->assertSame(1, $this->db->table('email_queue')->countAllResults());
        $this->assertTrue($service->attempt($first));
        $this->assertCount(1, $sender->messages);
        $this->seeInDatabase('email_queue', [
            'id' => $first,
            'status' => 'sent',
            'attempts' => 1,
        ]);

        $this->assertTrue($service->attempt($first));
        $this->assertCount(1, $sender->messages);
    }

    public function testFailedDeliveryRemainsAvailableForRetry(): void
    {
        $draft = $this->draft('reintento@example.test', 'CARA880303MMCPLN06');
        $sender = new CapturingEmailSender(false);
        $service = new EmailNotificationService($this->db, $sender);
        $queueId = $service->enqueueApplication($draft['id'], 'application_cancelled');

        $this->assertFalse($service->attempt($queueId));
        $this->seeInDatabase('email_queue', [
            'id' => $queueId,
            'status' => 'pending',
            'attempts' => 1,
            'last_error' => 'El servidor SMTP no confirmó el envío.',
        ]);
    }

    public function testAccessCodeTraceNeverStoresTheCode(): void
    {
        $draft = $this->draft('codigo@example.test', 'MARA850101MMCBCR08');
        $service = new EmailNotificationService($this->db, new CapturingEmailSender(true));
        $service->recordAccessCodeResult($draft['id'], 987, true, '2026-07-30 14:00:00');

        $row = $this->db->table('email_queue')->where('event', 'access_code')->get()->getRowArray();
        $this->assertNotNull($row);
        $this->assertStringNotContainsString('123456', (string) $row['payload']);
        $payload = json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($payload['sensitive_payload_stored']);
        $this->assertSame('sent', $row['status']);
    }

    private function draft(string $email, string $curp): array
    {
        return (new DraftApplicationService($this->db))->create(
            'cocineras-cocineros-tradicionales',
            $email,
            [[
                'curp' => $curp,
                'first_name' => 'Persona',
                'last_name' => 'Prueba',
                'second_last_name' => 'Correo',
            ]],
        );
    }
}

final class CapturingEmailSender implements EmailSenderInterface
{
    public array $messages = [];

    public function __construct(private bool $result)
    {
    }

    public function send(string $recipient, string $subject, string $html, string $plainText): bool
    {
        $this->messages[] = compact('recipient', 'subject', 'html', 'plainText');
        return $this->result;
    }
}
