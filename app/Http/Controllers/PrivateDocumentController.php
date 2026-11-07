<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrivateDocumentController extends Controller
{
    public function document(Request $request, Tenant $tenant, string $side)
    {
        abort_unless($request->user()->hasRole('admin') || $tenant->user_id === $request->user()->id, 403);
        $path = $side === 'front' ? $tenant->cccd_front_path : $tenant->cccd_back_path;

        return $this->image($path, 'tenants/cccd/'.$tenant->id.'/');
    }

    public function signature(Request $request, Contract $contract)
    {
        abort_unless($request->user()->hasRole('admin') || $contract->tenant->user_id === $request->user()->id, 403);

        return $this->image($contract->signature_path, 'contracts/signatures/'.$contract->id.'/');
    }

    private function image(?string $path, string $prefix)
    {
        abort_unless($path && str_starts_with($path, $prefix) && ! str_contains($path, '..'), 404);
        abort_unless(Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
