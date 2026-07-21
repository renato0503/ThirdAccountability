<x-guest-layout>
    <h5 class="fw-bold mb-1 text-center">Verificar E-mail</h5>

    @if(session('status') == 'verification-link-sent')
    <div class="alert alert-success py-2 mb-3" style="font-size:.875rem">
        Um novo link de verificação foi enviado para o seu e-mail.
    </div>
    @endif

    <p class="text-muted mb-4" style="font-size:.875rem">
        Obrigado por se cadastrar! Antes de começar, verifique seu e-mail clicando no link que enviamos.
        Se não recebeu, podemos reenviar.
    </p>

    <form method="POST" action="{{ route('verification.send') }}" class="d-grid mb-3">
        @csrf
        <button type="submit" class="btn btn-primary btn-lg fw-semibold">
            <i class="bi bi-envelope me-2"></i>Reenviar E-mail de Verificação
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="text-center">
        @csrf
        <button type="submit" class="btn btn-link text-decoration-none text-muted" style="font-size:.875rem">Sair</button>
    </form>
</x-guest-layout>
