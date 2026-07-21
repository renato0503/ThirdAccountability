<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    @if(session('status') === 'password-updated')
    <div class="alert alert-success py-2 mb-3" style="font-size:.875rem">Senha atualizada com sucesso.</div>
    @endif

    <div class="mb-3">
        <label for="update_password_current_password" class="form-label">Senha Atual</label>
        <input id="update_password_current_password" type="password" name="current_password" class="form-control" autocomplete="current-password">
        @error('current_password', 'updatePassword')<div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="update_password_password" class="form-label">Nova Senha</label>
        <input id="update_password_password" type="password" name="password" class="form-control" autocomplete="new-password">
        @error('password', 'updatePassword')<div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>@enderror
    </div>

    <div class="mb-4">
        <label for="update_password_password_confirmation" class="form-label">Confirmar Nova Senha</label>
        <input id="update_password_password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
        @error('password_confirmation', 'updatePassword')<div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-primary">Atualizar Senha</button>
</form>
