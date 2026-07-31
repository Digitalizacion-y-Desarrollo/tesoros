<div class="accessibility-widget">
    <button class="accessibility-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#accessibilityPanel" aria-controls="accessibilityPanel">
        <span aria-hidden="true">◉</span>
        <span>Accesibilidad</span>
    </button>
</div>
<aside class="offcanvas offcanvas-end" tabindex="-1" id="accessibilityPanel" aria-labelledby="accessibilityTitle">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title font-display fs-4" id="accessibilityTitle">Ayudas de accesibilidad</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <p class="text-secondary">Ajusta la presentación del sitio según tus necesidades.</p>
        <div class="d-grid gap-2">
            <div class="btn-group" role="group" aria-label="Tamaño de texto">
                <button class="btn btn-outline-secondary" type="button" data-a11y-action="font-decrease">A−</button>
                <button class="btn btn-outline-secondary" type="button" data-a11y-action="font-increase">A+</button>
            </div>
            <button class="btn btn-outline-secondary text-start" type="button" data-a11y-toggle="high-contrast">Alto contraste</button>
            <button class="btn btn-outline-secondary text-start" type="button" data-a11y-toggle="grayscale">Escala de grises</button>
            <button class="btn btn-outline-secondary text-start" type="button" data-a11y-toggle="underline-links">Subrayar enlaces</button>
            <button class="btn btn-outline-secondary text-start" type="button" data-a11y-toggle="readable-font">Tipografía legible</button>
            <button class="btn btn-outline-secondary text-start" type="button" data-a11y-toggle="reduce-motion">Reducir movimiento</button>
            <button class="btn btn-wine mt-2" type="button" data-a11y-action="reset">Restablecer</button>
        </div>
    </div>
</aside>
