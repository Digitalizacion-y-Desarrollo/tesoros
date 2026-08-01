<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<section class="py-5">
    <div class="container page-gutter">
        <div class="mx-auto legal-content">
            <p class="eyebrow mb-2">Información legal</p>
            <h1 class="display-5 text-wine"><?= esc($document['title']) ?></h1>
            <p class="text-secondary">Versión <?= esc($document['version']) ?> · Vigente desde <?= esc($document['effective_at']) ?></p>
            <div class="admin-panel mt-4">
                <?php foreach (preg_split('/\R{2,}/', trim((string) $document['content'])) as $paragraph): ?>
                    <p><?= nl2br(esc($paragraph)) ?></p>
                <?php endforeach ?>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
