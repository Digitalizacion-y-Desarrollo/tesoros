<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setAutoRoute(false);

$routes->get('/', 'Home::index', ['as' => 'home']);
$routes->get('convocatorias/(:segment)', 'CategoryController::show/$1', ['as' => 'category.show']);
$routes->get('legal/(:segment)', 'LegalController::show/$1', ['as' => 'legal.show']);

$routes->group('participante', static function ($routes): void {
    $routes->get('acceso', 'Participant\AccessController::index', ['as' => 'participant.access']);
    $routes->post('acceso/solicitar', 'Participant\AccessController::requestCode', ['as' => 'participant.access.request']);
    $routes->get('acceso/codigo', 'Participant\AccessController::code', ['as' => 'participant.access.code']);
    $routes->post('acceso/verificar', 'Participant\AccessController::verify', ['as' => 'participant.access.verify']);
    $routes->post('acceso/reenviar', 'Participant\AccessController::resend', ['as' => 'participant.access.resend']);
    $routes->get('registro/(:segment)', 'Participant\AccessController::register/$1', ['as' => 'participant.register']);
    $routes->post('registro/(:segment)', 'Participant\AccessController::create/$1', ['as' => 'participant.create']);
    $routes->get('borrador', 'Participant\ApplicationController::edit', [
        'as'     => 'participant.draft',
        'filter' => 'participantAuth',
    ]);
    $routes->post('borrador/guardar', 'Participant\ApplicationController::save', [
        'as'     => 'participant.draft.save',
        'filter' => 'participantAuth',
    ]);
    $routes->get('borrador/resumen', 'Participant\ApplicationController::summary', [
        'as'     => 'participant.draft.summary',
        'filter' => 'participantAuth',
    ]);
    $routes->post('borrador/enviar', 'Participant\ApplicationController::submit', [
        'as'     => 'participant.draft.submit',
        'filter' => 'participantAuth',
    ]);
    $routes->post('documentos/(:segment)/corregir', 'Participant\ApplicationController::correctDocument/$1', [
        'as'     => 'participant.document.correct',
        'filter' => 'participantAuth',
    ]);
    $routes->post('solicitud/cancelar', 'Participant\ApplicationController::cancel', [
        'as'     => 'participant.application.cancel',
        'filter' => 'participantAuth',
    ]);
    $routes->get('solicitud', 'Participant\ApplicationController::show', [
        'as'     => 'participant.application',
        'filter' => 'participantAuth',
    ]);
    $routes->get('videos/(:num)', 'Participant\VideoController::show/$1', [
        'as'     => 'participant.video',
        'filter' => 'participantAuth',
    ]);
    $routes->get('documentos/(:num)', 'Participant\DocumentController::show/$1', [
        'as'     => 'participant.document',
        'filter' => 'participantAuth',
    ]);
    $routes->post('salir', 'Participant\AccessController::logout', [
        'as'     => 'participant.logout',
        'filter' => 'participantAuth',
    ]);
});

$routes->get('administracion/acceso-pendiente', 'Admin\DashboardController::unavailable', ['as' => 'admin.unavailable']);
$routes->get('administracion/acceso', 'Admin\AuthController::index', ['as' => 'admin.login']);
$routes->post('administracion/acceso', 'Admin\AuthController::login', ['as' => 'admin.login.submit']);
$routes->post('administracion/salir', 'Admin\AuthController::logout', ['as' => 'admin.logout']);
$routes->get('administracion/recuperar-acceso', 'Admin\AuthController::forgot', ['as' => 'admin.forgot']);
$routes->post('administracion/recuperar-acceso', 'Admin\AuthController::sendRecovery', ['as' => 'admin.forgot.submit']);
$routes->group('administracion', ['filter' => 'adminAuth'], static function ($routes): void {
    $routes->get('/', 'Admin\DashboardController::index', ['as' => 'admin.dashboard']);
    $routes->get('solicitudes', 'Admin\ApplicationController::index', ['as' => 'admin.applications']);
    $routes->get('solicitudes/exportar.csv', 'Admin\ApplicationController::exportCsv', ['as' => 'admin.applications.export']);
    $routes->get('solicitudes/(:num)', 'Admin\ApplicationController::show/$1', ['as' => 'admin.applications.show']);
    $routes->post('solicitudes/(:num)/datos-personales', 'Admin\ApplicationController::updatePersonalData/$1', ['as' => 'admin.applications.personal']);
    $routes->post('solicitudes/(:num)/comentarios', 'Admin\ApplicationController::addComment/$1', ['as' => 'admin.applications.comments']);
    $routes->post('solicitudes/(:num)/estado', 'Admin\ApplicationController::changeStatus/$1', ['as' => 'admin.applications.status']);
    $routes->post('solicitudes/(:num)/correccion', 'Admin\ApplicationController::requestCorrection/$1', ['as' => 'admin.applications.correction']);
    $routes->get('bitacora', 'Admin\AuditController::index', ['as' => 'admin.audit']);
    $routes->get('documentos/(:num)', 'Admin\DocumentController::show/$1', ['as' => 'admin.document']);
    $routes->get('videos/(:num)', 'Admin\VideoController::show/$1', ['as' => 'admin.video']);
});
