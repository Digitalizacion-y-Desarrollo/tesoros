<?php

namespace App\Controllers\Participant;

use App\Controllers\ParticipantController;
use App\Exceptions\AccessRateLimitException;
use App\Services\DraftApplicationService;
use App\Services\ParticipantAccessService;
use App\Services\RegistrationNotificationService;
use App\Services\ConvocationSchedule;
use App\Services\AuditLogService;
use CodeIgniter\Exceptions\PageNotFoundException;
use DomainException;
use Throwable;

class AccessController extends ParticipantController
{
    public function index()
    {
        if ($this->session->get('participant_authenticated')) {
            return redirect()->route('participant.application');
        }

        return view('participant/access', [
            'title' => 'Consulta tu participación',
        ]);
    }

    public function requestCode()
    {
        $email = (string) $this->request->getPost('email');
        $folio = (string) $this->request->getPost('folio');

        try {
            $challenge = $this->issueCode($email, $folio);
            $this->storeChallenge($email, $folio, $challenge);
        } catch (AccessRateLimitException $exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        } catch (Throwable) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No fue posible procesar la solicitud. Inténtalo más tarde.');
        }

        return redirect()->route('participant.access.code')->with(
            'message',
            'Si los datos son correctos, enviamos un código al correo registrado.',
        );
    }

    public function code()
    {
        if (! $this->session->get('access_pending')) {
            return redirect()->route('participant.access');
        }

        return view('participant/access_code', [
            'title' => 'Código temporal',
            'errors' => session('validation_errors') ?? [],
            'retrySeconds' => max(0, (int) $this->session->get('access_resend_available_at') - time()),
            'attemptsRemaining' => max(
                0,
                config('ParticipantAccess')->maxAttempts - (int) $this->session->get('access_pending_attempts'),
            ),
        ]);
    }

    public function verify()
    {
        if (! $this->session->get('access_pending')) {
            return redirect()->route('participant.access');
        }

        $code = trim((string) $this->request->getPost('code'));
        $attempts = (int) $this->session->get('access_pending_attempts');
        $expired = (string) $this->session->get('access_expires_at') < date('Y-m-d H:i:s');
        if (! preg_match('/^\d{6}$/', $code)
            || $expired
            || $attempts >= config('ParticipantAccess')->maxAttempts
        ) {
            return $this->failedVerification($attempts + 1);
        }

        $fakeHash = $this->session->get('access_fake_hash');
        if ($this->session->get('access_application_id') === null && is_string($fakeHash)) {
            password_verify($code, $fakeHash);
        }

        try {
            $participantSession = (new ParticipantAccessService())->verifyCode(
                $this->nullableInt($this->session->get('access_code_id')),
                $this->nullableInt($this->session->get('access_application_id')),
                $code,
                $this->request->getIPAddress(),
                (string) $this->session->session_id,
                $this->request->getUserAgent()->getAgentString(),
            );
        } catch (Throwable) {
            return redirect()->route('participant.access.code')
                ->with('error', 'No fue posible verificar el código. Inténtalo más tarde.');
        }

        if ($participantSession === null) {
            return $this->failedVerification($attempts + 1);
        }

        $this->session->regenerate(true);
        $this->session->remove([
            'access_pending',
            'access_code_id',
            'access_application_id',
            'access_fake_hash',
            'access_expires_at',
            'access_pending_attempts',
            'access_pending_email',
            'access_pending_folio',
            'access_resend_available_at',
        ]);
        $this->session->set([
            'participant_authenticated' => true,
            'participant_application_id' => $participantSession['application_id'],
            'participant_access_scope' => 'temporary',
            'participant_session_id' => $participantSession['id'],
            'participant_session_token' => $participantSession['token'],
            'participant_session_expires_at' => $participantSession['expires_at'],
        ]);
        (new AuditLogService())->record(
            'participant',
            'participant_access_granted',
            (int) $participantSession['application_id'],
            null,
            $this->request->getIPAddress(),
            $this->request->getUserAgent()->getAgentString(),
        );

        return redirect()->route('participant.application')->with('message', 'Acceso verificado correctamente.');
    }

    public function resend()
    {
        if (! $this->session->get('access_pending')) {
            return redirect()->route('participant.access');
        }

        $email = (string) $this->session->get('access_pending_email');
        $folio = (string) $this->session->get('access_pending_folio');

        try {
            $challenge = $this->issueCode($email, $folio);
            $this->storeChallenge($email, $folio, $challenge);
        } catch (AccessRateLimitException $exception) {
            return redirect()->route('participant.access.code')->with('error', $exception->getMessage());
        } catch (Throwable) {
            return redirect()->route('participant.access.code')
                ->with('error', 'No fue posible reenviar el código. Inténtalo más tarde.');
        }

        return redirect()->route('participant.access.code')->with(
            'message',
            'Si los datos son correctos, enviamos un nuevo código al correo registrado.',
        );
    }

    public function logout()
    {
        $applicationId = $this->nullableInt($this->session->get('participant_application_id'));
        (new ParticipantAccessService())->revokeSession(
            $this->nullableInt($this->session->get('participant_session_id')),
            $this->nullableInt($this->session->get('participant_application_id')),
        );
        if ($applicationId !== null) {
            (new AuditLogService())->record(
                'participant',
                'participant_logout',
                $applicationId,
                null,
                $this->request->getIPAddress(),
                $this->request->getUserAgent()->getAgentString(),
            );
        }
        $this->session->destroy();

        return redirect()->route('participant.access')->with('message', 'La sesión se cerró correctamente.');
    }

    public function register(string $slug): string
    {
        $categories = config('Portal')->categories;

        if (! isset($categories[$slug])) {
            throw PageNotFoundException::forPageNotFound();
        }

        $schedule = new ConvocationSchedule();

        return view('participant/register', [
            'category' => $categories[$slug],
            'slug' => $slug,
            'title' => 'Registro · ' . $categories[$slug]['name'],
            'privacyVersion' => config('ApplicationForms')->privacyVersion,
            'convocationClosed' => $schedule->isClosed(),
            'convocationCloseAt' => $schedule->closeAt(),
        ]);
    }

    public function create(string $slug)
    {
        $categories = config('Portal')->categories;
        if (! isset($categories[$slug])) {
            throw PageNotFoundException::forPageNotFound();
        }
        $throttleKey = 'registration-' . hash(
            'sha256',
            $this->request->getIPAddress() . '|' . (string) $this->session->session_id,
        );
        if (! service('throttler')->check($throttleKey, 10, 900)) {
            (new AuditLogService())->record(
                'anonymous',
                'registration_rate_limited',
                null,
                null,
                $this->request->getIPAddress(),
                $this->request->getUserAgent()->getAgentString(),
            );
            return redirect()->back()
                ->withInput()
                ->with('error', 'Se alcanzó el límite temporal de registros. Inténtalo más tarde.');
        }

        $errors = [];
        if ((string) $this->request->getPost('accept_privacy') !== '1') {
            $errors['accept_privacy'] = 'Debes aceptar el aviso de privacidad provisional para crear el borrador.';
        }

        if ($errors !== []) {
            return redirect()->back()->withInput()->with('validation_errors', $errors);
        }

        $participants = $this->request->getPost('participants');
        try {
            $result = (new DraftApplicationService())->create(
                $slug,
                (string) $this->request->getPost('email'),
                is_array($participants) ? array_values($participants) : [],
                [
                    'document_version' => config('ApplicationForms')->privacyVersion,
                    'ip_address' => $this->request->getIPAddress(),
                    'user_agent' => $this->request->getUserAgent()->getAgentString(),
                ],
            );
        } catch (DomainException $exception) {
            return redirect()->back()
                ->withInput()
                ->with('validation_errors', ['registration' => $exception->getMessage()]);
        }

        $this->session->regenerate(true);
        $this->session->set([
            'participant_authenticated' => true,
            'participant_application_id' => $result['id'],
            'participant_folio' => $result['folio'],
            'participant_access_scope' => 'draft_owner',
        ]);
        (new AuditLogService())->record(
            'participant',
            'draft_created',
            (int) $result['id'],
            null,
            $this->request->getIPAddress(),
            $this->request->getUserAgent()->getAgentString(),
            ['category' => $slug],
        );

        $registrationMailSent = false;
        try {
            $registrationMailSent = (new RegistrationNotificationService())->send($result['id']);
        } catch (Throwable $exception) {
            log_message('error', 'No fue posible registrar el resultado del correo inicial: {type}', [
                'type' => $exception::class,
            ]);
        }

        $message = $registrationMailSent
            ? "Registro exitoso. Enviamos la confirmación y tu folio {$result['folio']} al correo registrado."
            : "Registro exitoso. Tu folio es {$result['folio']}; no fue posible enviar la confirmación por correo.";

        return redirect()->route('participant.draft')
            ->with('message', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function issueCode(string $email, string $folio): array
    {
        return (new ParticipantAccessService())->requestCode(
            $email,
            $folio,
            $this->request->getIPAddress(),
            (string) $this->session->session_id,
            $this->request->getUserAgent()->getAgentString(),
        );
    }

    /**
     * @param array<string, mixed> $challenge
     */
    private function storeChallenge(string $email, string $folio, array $challenge): void
    {
        $this->session->set([
            'access_pending' => true,
            'access_code_id' => $challenge['code_id'],
            'access_application_id' => $challenge['application_id'],
            'access_fake_hash' => $challenge['fake_hash'],
            'access_expires_at' => $challenge['expires_at'],
            'access_pending_attempts' => 0,
            'access_pending_email' => trim($email),
            'access_pending_folio' => strtoupper(trim($folio)),
            'access_resend_available_at' => time() + config('ParticipantAccess')->resendCooldownSeconds,
        ]);
    }

    private function failedVerification(int $attempts)
    {
        $this->session->set('access_pending_attempts', min($attempts, config('ParticipantAccess')->maxAttempts));

        return redirect()->route('participant.access.code')
            ->with('validation_errors', ['code' => 'El código es inválido o expiró. Solicita uno nuevo si es necesario.']);
    }

    private function nullableInt(mixed $value): ?int
    {
        $integer = (int) $value;
        return $integer > 0 ? $integer : null;
    }
}
