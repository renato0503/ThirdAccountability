<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller {
    public function index(Request $req) {
        if (!auth()->user()->isAdmin()) abort(403);
        $q = AuditLog::with('user')->orderBy('created_at','desc');
        if ($s = $req->search) {
            $q->where(fn($x)=>$x->where('entidade','like',"%$s%")->orWhere('acao','like',"%$s%")->orWhere('entidade_id','like',"%$s%"));
        }
        $logs = $q->paginate(30)->withQueryString();
        return view('audit.index', compact('logs'));
    }
}
