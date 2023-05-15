<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

class DoSpacesController extends Controller
{
    public function upload(Request $request)
    {
        // request file dari frontend
        $file = $request->file;
        // extensi file berbahaya yg tidak boleh diupload
        $danger = ['exe', 'php', 'bin', 'sh', 'html', 'htaccess', 'bin', 'ini', 'bat', 'js', 'css', 'sql', 'json'];
        // melihat jenis file yang dikirim dari frontend
        $jenis = explode(',', $request->jenis);
        // proses file
        $file_ori_name = $file->getClientOriginalName();
        $file_path = realpath($file);
        $file_name = explode('.',$file_ori_name)[0];
        $file_extension = $file->getClientOriginalExtension();
        // buat nama file dengan tambahan tanggal dan jam saat ini
        $file_slug = Str::slug($file_name, '_')."_".date('Y-m-d')."_".date('H-i-s').".".$file_extension;
        // username gunanya menyimpan file ke dalam direktori berdasarkan nama user tsb
        $username = auth()->user()->name;
        $username_slug = Str::slug($username, '_');
        // cek jika file yang diunggah memiliki extensi yang berbahaya maka return error
        if (in_array($file_extension, $danger)) {
            return response()->json('Error', 403);
            die('Error');
        }
        // jika extensi file sesuai dengan jenis yang diizinkan lanjutkan proses
        if (in_array($file_extension, $jenis)) {
            $role = auth()->user()->role;
            $storage_path = '';
            if (($role === 'superadmin') || ($role === 'admin')) {
              $storage_path =  date('Y').'/00doc';
            } else {
              $storage_path =  date('Y').'/'.$username_slug;
            }

            $path = Storage::disk('do')->putFileAs($storage_path, $file, $file_slug, 'public');
            $path = Storage::disk('do')->url($path);

            return response()->json([
                'message' => 'File berhasil diunggah',
                'type' => 'do',
                'path' => $path
            ], 200);
        } else {
            return response()->json([
                'info' => 'Jenis file tidak diizinkan, pastikan file anda sesuai'
            ], 422);
        }
    }
}