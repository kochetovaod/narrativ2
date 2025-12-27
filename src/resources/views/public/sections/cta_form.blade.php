<section class="section">
    <div class="card">
        @if(!empty($section['settings']['title']))
            <h2 style="margin-top: 0;">{{ $section['settings']['title'] }}</h2>
        @endif

        @if(!empty($section['settings']['description']))
            <p class="meta">{{ $section['settings']['description'] }}</p>
        @endif

        <p style="margin-top: 1rem;">Здесь будет форма заявки. Код формы задается в админке и обрабатывается контроллером форм.</p>
        @if(!empty($section['settings']['contact_type']))
            <p class="meta">Предпочтительный способ связи: {{ $section['settings']['contact_type'] }}</p>
        @endif
    </div>
</section>
