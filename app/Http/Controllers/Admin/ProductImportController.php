<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ProductCsvImporter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * One-off bulk import of products from a Shopify-format product-export CSV.
 *
 * Runs synchronously in the request — the reference export is a few hundred
 * rows, well inside a request's time budget once `set_time_limit` is raised
 * to cover the per-row image downloads.
 */
class ProductImportController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Admin/ProductImport');
    }

    public function store(Request $request): Response
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ], [], ['file' => 'berkas CSV']);

        set_time_limit(300);

        $summary = (new ProductCsvImporter)->import($request->file('file')->getRealPath());

        $request->session()->flash(
            'success',
            "Impor selesai: {$summary['created']} produk baru, {$summary['updated']} diperbarui.",
        );

        return Inertia::render('Admin/ProductImport', ['result' => $summary]);
    }
}
