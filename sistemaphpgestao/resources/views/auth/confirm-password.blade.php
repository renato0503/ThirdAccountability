<x-guest-layout>
    <h5 class="fw-bold mb-1 text-center">Confirmar Senha</h5>
    <p class="text-muted text-center mb-4" style="font-size:.85rem">Esta é uma área segura. Confirme sua senha para continuar.</p>

    @if($errors->any())
    <div class="alert alert-danger py-2 mb-3" style="font-size:.875rem">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-4">
            <label for="password" class="form-label fw-semibold">Senha</label>
            <input id="password" type="password" name="password" class="form-control form-control-lg" required autocomplete="current-password">
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                <i class="bi bi-shield-check me-2"></i>Confirmar
            </button>
        </div>
    </form>
</x-guest-layout>
