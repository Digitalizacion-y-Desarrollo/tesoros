<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$registerUrl = url_to('participant.register', $slug);
$accentClass = 'theme-' . $category['accent'];
?>
<article class="category-page <?= esc($accentClass) ?>">
    <section class="category-hero">
        <div class="ornament-pattern" aria-hidden="true"></div>
        <div class="container-fluid p-0 position-relative">
            <div class="row g-0 align-items-stretch">
                <div class="col-lg-6 category-hero-content page-gutter">
                    <p class="eyebrow text-gold mb-3"><?= esc($category['eyebrow']) ?></p>
                    <h1><?= esc($category['title'] ?? $category['name']) ?></h1>
                    <p class="category-kicker"><?= esc($category['subtitle']) ?></p>
                    <p class="category-description"><?= esc($category['description']) ?></p>
                    <div class="d-flex flex-wrap gap-3">
                        <a class="btn btn-gold btn-lg" href="#requisitos">Consulta las bases</a>
                        <a class="btn btn-outline-light btn-lg" href="<?= esc($registerUrl) ?>">Iniciar registro</a>
                    </div>
                </div>
                <div class="col-lg-6 category-hero-image">
                    <img src="<?= base_url('assets/images/' . $category['image']) ?>" alt="Imagen representativa de <?= esc($category['name']) ?>">
                    <div class="hero-image-overlay" aria-hidden="true"></div>
                </div>
            </div>
        </div>
    </section>
    <div class="color-ribbon" aria-hidden="true"></div>

    <?php if (isset($category['notice'])): ?>
        <div class="container-fluid page-gutter pt-5">
            <div class="alert provisional-notice mb-0" role="status">
                <strong>Información provisional:</strong> <?= esc($category['notice']) ?>
            </div>
        </div>
    <?php endif ?>

    <section class="facts-section">
        <div class="container-fluid page-gutter">
            <div class="row g-3">
                <?php foreach ($category['facts'] as $index => $fact): ?>
                    <div class="col-md-6 col-xl-3">
                        <article class="fact-card h-100 <?= $index === count($category['facts']) - 1 ? 'fact-card-featured' : '' ?>">
                            <span class="fact-index"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <h2><?= esc($fact['title']) ?></h2>
                            <p><?= esc($fact['text']) ?></p>
                        </article>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </section>

    <section class="story-section">
        <div class="container-fluid page-gutter">
            <div class="row g-5 align-items-start">
                <div class="col-lg-5">
                    <p class="eyebrow mb-3">La propuesta</p>
                    <h2 class="section-title"><?= esc($category['introTitle']) ?></h2>
                    <p class="section-intro mt-4"><?= esc($category['intro']) ?></p>
                </div>
                <div class="col-lg-7">
                    <div class="process-panel">
                        <p class="eyebrow mb-3">Ruta de participación</p>
                        <h2 class="font-display mb-4">Proceso de selección</h2>
                        <div class="row g-4">
                            <?php foreach ($category['steps'] as $step): ?>
                                <div class="col-sm-6">
                                    <div class="process-step">
                                        <span><?= esc($step['number']) ?></span>
                                        <div>
                                            <h3><?= esc($step['title']) ?></h3>
                                            <p><?= esc($step['text']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="requisitos" class="requirements-section">
        <div class="container-fluid page-gutter">
            <div class="row g-5">
                <div class="col-lg-4">
                    <p class="eyebrow mb-3">Requisitos</p>
                    <h2 class="section-title">Podrán participar quienes</h2>
                    <p class="section-intro mt-3">Antes de iniciar, confirma que cumples las condiciones generales de esta categoría.</p>
                </div>
                <div class="col-lg-8">
                    <ol class="requirement-list">
                        <?php foreach ($category['requirements'] as $requirement): ?>
                            <li><span aria-hidden="true">✓</span><?= esc($requirement) ?></li>
                        <?php endforeach ?>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section id="documentacion" class="documents-section">
        <div class="container-fluid page-gutter">
            <div class="section-heading">
                <p class="eyebrow mb-3">Documentación</p>
                <h2 class="section-title">Expediente digital</h2>
                <p class="section-intro">Prepara archivos legibles en los formatos indicados. El máximo permitido será de 500 MB por archivo.</p>
            </div>
            <div class="row g-4">
                <?php foreach ($category['documents'] as $index => $document): ?>
                    <div class="col-md-6">
                        <article class="document-card h-100">
                            <span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <div>
                                <h3><?= esc($document['title']) ?></h3>
                                <p><?= esc($document['text']) ?></p>
                            </div>
                        </article>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </section>

    <?php foreach ($category['additionalSections'] ?? [] as $sectionIndex => $additionalSection): ?>
        <section id="detalle-<?= (int) $sectionIndex + 1 ?>" class="requirements-section">
            <div class="container-fluid page-gutter">
                <div class="row g-5">
                    <div class="col-lg-4">
                        <p class="eyebrow mb-3"><?= esc($additionalSection['eyebrow']) ?></p>
                        <h2 class="section-title"><?= esc($additionalSection['title']) ?></h2>
                        <p class="section-intro mt-3"><?= esc($additionalSection['intro']) ?></p>
                    </div>
                    <div class="col-lg-8">
                        <ol class="requirement-list">
                            <?php foreach ($additionalSection['items'] as $item): ?>
                                <li><span aria-hidden="true">✓</span><?= esc($item) ?></li>
                            <?php endforeach ?>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
    <?php endforeach ?>

    <section id="evaluacion" class="criteria-section">
        <div class="ornament-pattern" aria-hidden="true"></div>
        <div class="container-fluid page-gutter position-relative">
            <p class="eyebrow text-gold mb-3"><?= esc($category['criteriaEyebrow'] ?? 'Evaluación') ?></p>
            <h2 class="section-title text-white"><?= esc($category['criteriaTitle']) ?></h2>
            <div class="row g-4 mt-3">
                <?php foreach ($category['criteria'] as $index => $criterion): ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="criterion-card h-100">
                            <span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <h3><?= esc($criterion['title']) ?></h3>
                            <p><?= esc($criterion['text']) ?></p>
                        </article>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </section>

    <section class="benefits-section">
        <div class="container-fluid page-gutter">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <p class="eyebrow mb-3">Beneficios</p>
                    <h2 class="section-title">Lo que obtienen las personas seleccionadas</h2>
                </div>
                <div class="col-lg-7">
                    <div class="row g-3">
                        <?php foreach ($category['benefits'] as $index => $benefit): ?>
                            <div class="col-md-6">
                                <div class="benefit-item h-100">
                                    <strong><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></strong>
                                    <span><?= esc($benefit) ?></span>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="preguntas" class="faq-section">
        <div class="container-fluid page-gutter">
            <div class="row g-5">
                <div class="col-lg-4">
                    <p class="eyebrow mb-3">Preguntas frecuentes</p>
                    <h2 class="section-title">Dudas sobre el registro</h2>
                    <p class="section-intro mt-3">Consulta las respuestas antes de iniciar tu solicitud.</p>
                </div>
                <div class="col-lg-8">
                    <div class="accordion accordion-flush" id="faq<?= esc($category['number']) ?>">
                        <?php foreach ($category['faq'] as $index => $item): ?>
                            <?php $faqId = $category['number'] . '-' . $index; ?>
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faqAnswer<?= esc($faqId) ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="faqAnswer<?= esc($faqId) ?>">
                                        <?= esc($item['question']) ?>
                                    </button>
                                </h3>
                                <div id="faqAnswer<?= esc($faqId) ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#faq<?= esc($category['number']) ?>">
                                    <div class="accordion-body"><?= esc($item['answer']) ?></div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (isset($category['officialLegend'])): ?>
        <section class="story-section">
            <div class="container-fluid page-gutter">
                <div class="application-card mx-auto text-center">
                    <p class="eyebrow mb-3">Leyenda oficial de la convocatoria</p>
                    <blockquote class="section-intro mb-0">“<?= esc($category['officialLegend']) ?>”</blockquote>
                </div>
            </div>
        </section>
    <?php endif ?>

    <section class="category-cta">
        <div class="ornament-pattern" aria-hidden="true"></div>
        <div class="container-fluid page-gutter position-relative text-center">
            <p class="eyebrow text-gold mb-3">Convocatoria 2026</p>
            <h2 class="section-title text-white">Tu historia también forma parte de la cocina mexiquense</h2>
            <p class="mx-auto mb-4">Revisa las bases y prepara tu expediente antes de comenzar.</p>
            <a class="btn btn-gold btn-lg" href="<?= esc($registerUrl) ?>">Iniciar registro</a>
        </div>
    </section>
</article>

<?= $this->include('partials/partners') ?>
<?= $this->endSection() ?>
