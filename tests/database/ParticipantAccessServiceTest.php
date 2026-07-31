<?php

use App\Database\Seeds\CategorySeeder;
use App\Exceptions\AccessRateLimitException;
use App\Services\DraftApplicationService;
use App\Services\ParticipantAccessService;
use App\Services\ParticipantCodeMailerInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\ParticipantAccess;

/**
 * @internal
 */
final class ParticipantAccessServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $seed = CategorySeeder::class;

    public function testStoresOnlyAHashAndDoesNotEmailUnknownCombinations(): void
    {
        [$service, $mailer, $draft] = $this->serviceWithDraft();

        $challenge = $service->requestCode(
            'acceso@example.test',
            $draft['folio'],
            '127.0.0.1',
            'browser-session-1',
            'PHPUnit',
        );
        $this->assertTrue($challenge['mail_sent']);
        $this->assertCount(1, $mailer->messages);
        $code = $mailer->messages[0]['code'];
        $row = $this->db->table('access_codes')->where('id', $challenge['code_id'])->get()->getRowArray();
        $this->assertNotSame($code, $row['code_hash']);
        $this->assertTrue(password_verify($code, $row['code_hash']));

        $unknown = $service->requestCode(
            'desconocido@example.test',
            'TG-2026-CCT-999999',
            '127.0.0.2',
            'browser-session-2',
            'PHPUnit',
        );
        $this->assertNull($unknown['application_id']);
        $this->assertNotNull($unknown['fake_hash']);
        $this->assertCount(1, $mailer->messages);
        $this->seeInDatabase('participant_access_events', ['event' => 'code_request_unknown']);
    }

    public function testResendInvalidatesPreviousCodeAndRequiresSixtySeconds(): void
    {
        $now = new DateTimeImmutable('2026-07-30 12:00:00');
        [$service, $mailer, $draft] = $this->serviceWithDraft($now);
        $first = $service->requestCode(
            'acceso@example.test',
            $draft['folio'],
            '127.0.0.1',
            'browser-session',
            'PHPUnit',
        );

        try {
            $service->requestCode(
                'acceso@example.test',
                $draft['folio'],
                '127.0.0.1',
                'browser-session',
                'PHPUnit',
            );
            $this->fail('El reenvío inmediato debió ser limitado.');
        } catch (AccessRateLimitException $exception) {
            $this->assertSame(60, $exception->retryAfterSeconds());
        }

        $now = $now->modify('+61 seconds');
        $second = $service->requestCode(
            'acceso@example.test',
            $draft['folio'],
            '127.0.0.1',
            'browser-session',
            'PHPUnit',
        );

        $this->assertNotSame($first['code_id'], $second['code_id']);
        $this->assertCount(2, $mailer->messages);
        $this->assertNotNull(
            $this->db->table('access_codes')->where('id', $first['code_id'])->get()->getRowArray()['invalidated_at'],
        );
    }

    public function testFiveFailedAttemptsInvalidateTheCode(): void
    {
        [$service, $mailer, $draft] = $this->serviceWithDraft();
        $challenge = $service->requestCode(
            'acceso@example.test',
            $draft['folio'],
            '127.0.0.1',
            'browser-session',
            'PHPUnit',
        );
        $validCode = $mailer->messages[0]['code'];
        $invalidCode = $validCode === '000000' ? '111111' : '000000';

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->assertNull($service->verifyCode(
                $challenge['code_id'],
                $draft['id'],
                $invalidCode,
                '127.0.0.1',
                'browser-session',
                'PHPUnit',
            ));
        }

        $row = $this->db->table('access_codes')->where('id', $challenge['code_id'])->get()->getRowArray();
        $this->assertSame(5, (int) $row['attempts']);
        $this->assertNotNull($row['invalidated_at']);
        $this->assertNull($service->verifyCode(
            $challenge['code_id'],
            $draft['id'],
            $mailer->messages[0]['code'],
            '127.0.0.1',
            'browser-session',
            'PHPUnit',
        ));
    }

    public function testSuccessfulVerificationCreatesARevocableIsolatedSession(): void
    {
        [$service, $mailer, $draft] = $this->serviceWithDraft();
        $challenge = $service->requestCode(
            'acceso@example.test',
            $draft['folio'],
            '127.0.0.1',
            'browser-session',
            'PHPUnit',
        );
        $participantSession = $service->verifyCode(
            $challenge['code_id'],
            $draft['id'],
            $mailer->messages[0]['code'],
            '127.0.0.1',
            'browser-session',
            'PHPUnit',
        );

        $this->assertNotNull($participantSession);
        $this->assertTrue($service->validateSession(
            $participantSession['id'],
            $draft['id'],
            $participantSession['token'],
        ));
        $stored = $this->db->table('participant_sessions')
            ->where('id', $participantSession['id'])
            ->get()
            ->getRowArray();
        $this->assertNotSame($participantSession['token'], $stored['token_hash']);
        $this->assertSame(hash('sha256', $participantSession['token']), $stored['token_hash']);
        $this->assertFalse($service->validateSession(
            $participantSession['id'],
            $draft['id'] + 1,
            $participantSession['token'],
        ));

        $service->revokeSession($participantSession['id'], $draft['id']);
        $this->assertFalse($service->validateSession(
            $participantSession['id'],
            $draft['id'],
            $participantSession['token'],
        ));
        $usedCode = $this->db->table('access_codes')
            ->where('id', $challenge['code_id'])
            ->get()
            ->getRowArray();
        $this->assertNotNull($usedCode['used_at']);
        $this->assertNull($service->verifyCode(
            $challenge['code_id'],
            $draft['id'],
            $mailer->messages[0]['code'],
            '127.0.0.1',
            'browser-session',
            'PHPUnit',
        ));
    }

    public function testExpiredCodeCannotBeUsed(): void
    {
        $now = new DateTimeImmutable('2026-07-30 12:00:00');
        [$service, $mailer, $draft] = $this->serviceWithDraft($now);
        $challenge = $service->requestCode(
            'acceso@example.test',
            $draft['folio'],
            '127.0.0.1',
            'browser-session',
            'PHPUnit',
        );
        $now = $now->modify('+601 seconds');

        $this->assertNull($service->verifyCode(
            $challenge['code_id'],
            $draft['id'],
            $mailer->messages[0]['code'],
            '127.0.0.1',
            'browser-session',
            'PHPUnit',
        ));
    }

    public function testRateLimitsApplyIndependentlyToEmailFolioIpAndBrowserSession(): void
    {
        $now = new DateTimeImmutable('2026-07-30 12:00:00');
        $config = new ParticipantAccess();
        $config->maxRequestsPerIdentity = 2;
        $config->maxRequestsPerIp = 2;
        $config->maxRequestsPerSession = 2;
        [$service] = $this->serviceWithDraft($now, $config);

        $scenarios = [
            static fn (int $index): array => [
                'mismo@example.test',
                "TG-TEST-EMAIL-{$index}",
                "127.0.1.{$index}",
                "session-email-{$index}",
            ],
            static fn (int $index): array => [
                "folio-{$index}@example.test",
                'TG-TEST-FOLIO',
                "127.0.2.{$index}",
                "session-folio-{$index}",
            ],
            static fn (int $index): array => [
                "ip-{$index}@example.test",
                "TG-TEST-IP-{$index}",
                '127.0.3.1',
                "session-ip-{$index}",
            ],
            static fn (int $index): array => [
                "session-{$index}@example.test",
                "TG-TEST-SESSION-{$index}",
                "127.0.4.{$index}",
                'same-browser-session',
            ],
        ];

        foreach ($scenarios as $scenario) {
            $this->db->table('participant_access_events')->truncate();

            for ($index = 1; $index <= 2; $index++) {
                [$email, $folio, $ip, $browserSession] = $scenario($index);
                $service->requestCode($email, $folio, $ip, $browserSession, 'PHPUnit');
                $now = $now->modify('+61 seconds');
            }

            [$email, $folio, $ip, $browserSession] = $scenario(3);
            try {
                $service->requestCode($email, $folio, $ip, $browserSession, 'PHPUnit');
                $this->fail('El límite independiente debió bloquear la tercera solicitud.');
            } catch (AccessRateLimitException $exception) {
                $this->assertSame($config->rateWindowSeconds, $exception->retryAfterSeconds());
            }
        }
    }

    /**
     * @return array{ParticipantAccessService, CapturingParticipantCodeMailer, array{id: int, folio: string, status: string}}
     */
    private function serviceWithDraft(
        ?DateTimeImmutable &$now = null,
        ?ParticipantAccess $config = null,
    ): array
    {
        $now ??= new DateTimeImmutable('2026-07-30 12:00:00');
        $mailer = new CapturingParticipantCodeMailer();
        $clock = static function () use (&$now): DateTimeImmutable {
            return $now;
        };
        $service = new ParticipantAccessService($this->db, $mailer, $config, $clock);
        $draft = (new DraftApplicationService($this->db))->create(
            'cocineras-cocineros-tradicionales',
            'acceso@example.test',
            [[
                'curp' => 'GODE561231HDFBCD09',
                'first_name' => 'María',
                'last_name' => 'González',
                'second_last_name' => 'Ejemplo',
            ]],
        );

        return [$service, $mailer, $draft];
    }
}

final class CapturingParticipantCodeMailer implements ParticipantCodeMailerInterface
{
    /**
     * @var list<array{recipient: string, folio: string, code: string, expires_at: string}>
     */
    public array $messages = [];

    public function send(string $recipient, string $folio, string $code, string $expiresAt): bool
    {
        $this->messages[] = [
            'recipient' => $recipient,
            'folio' => $folio,
            'code' => $code,
            'expires_at' => $expiresAt,
        ];

        return true;
    }
}
