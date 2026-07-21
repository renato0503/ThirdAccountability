<x-guest-layout>
    @if(session('status'))
    <div class="alert alert-success py-2 mb-3" style="font-size:.875rem">{{ session('status') }}</div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger py-2 mb-3" style="font-size:.875rem">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
    @endif

    <h5 class="fw-bold mb-1 text-center">Recuperar Senha</h5>
    <p class="text-muted text-center mb-4" style="font-size:.85rem">Informe seu e-mail para receber o link de redefinição.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label for="email" class="form-label fw-semibold">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg" required autofocus>
        </div>
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                <i class="bi bi-envelope me-2"></i>Enviar Link de Redefinição
            </button>
        </div>
        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none" style="font-size:.875rem">Voltar ao login</a>
        </div>
    </form>
</x-guest-layout>
