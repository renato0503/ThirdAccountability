<p class="text-muted mb-4" style="font-size:13px">
    Uma vez que sua conta for excluída, todos os dados serão permanentemente removidos. Esta ação não pode ser desfeita.
</p>

<button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
    <i class="bi bi-trash me-1"></i>Excluir Conta
</button>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão de Conta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="modal-body">
                    <p style="font-size:13px">Esta ação é irreversível. Digite sua senha para confirmar a exclusão da conta.</p>
                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Senha Atual</label>
                        <input id="delete_password" type="password" name="password" class="form-control" placeholder="Sua senha atual">
                        @error('password', 'userDeletion')<div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Excluir Conta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
