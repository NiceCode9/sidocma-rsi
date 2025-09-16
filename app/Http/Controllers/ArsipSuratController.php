<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArsipSuratController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Document::with('sharedLink')->where('is_latter', true)->get();
            return datatables()->of($data)
                ->addIndexColumn()
                ->editColumn('judul', function ($row) {
                    return $row->name;
                })
                ->editColumn('status', function ($row) {
                    return $row->sharedLink->is_read ? '<span class="badge badge-success">Dibaca</span>' : '<span class="badge badge-warning">Belum Dibuka</span>';
                })
                ->editColumn('read_at', function ($row) {
                    return $row->sharedLink->read_at ? Carbon::parse($row->sharedLink->read_at)->format('d-m-Y H:i:s') : '-';
                })
                ->editColumn('opened_by', function ($row) {
                    return $row->sharedLink->opened_by ? $row->sharedLink->opened_by : '-';
                })
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y H:i:s');
                })
                ->editColumn('file', function ($row) {
                    // $fileUrl = {{  }};
                    return '<a href="' . route('documents.download', $row->id) . '">' . $row->original_name . ' (' . number_format($row->file_size / 1024, 2) . ' KB)' . '</a>';
                })
                ->rawColumns(['status', 'file', 'read_at'])
                ->make(true);
        }
        return view('surat.index');
    }
}
