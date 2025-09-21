<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $school = $user->school; // may be null
        return view('school.index', compact('school'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', School::class);

        $data = $this->validatedData($request);
        $data['user_id'] = auth()->id();

        $school = School::create($data);

        return redirect()->route('school.index')->with('status', 'Data sekolah berhasil disimpan.');
    }

    public function update(Request $request, School $school)
    {
        $this->authorize('update', $school);

        $data = $this->validatedData($request);
        $school->update($data);

        return redirect()->route('school.index')->with('status', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(School $school)
    {
        $this->authorize('delete', $school);
        $school->delete();
        return redirect()->route('school.index')->with('status', 'Data sekolah berhasil dihapus.');
    }

    protected function validatedData(Request $request): array
    {
        $schoolId = $request->route('school')?->id ?? null;
        return $request->validate([
            'nama_sekolah' => ['required', 'string', 'max:255'],
            'status_sekolah' => ['required', 'in:negeri,swasta'],
            'alamat_sekolah' => ['nullable', 'string'],
            'npsn' => ['nullable', 'string', 'max:20', Rule::unique('schools', 'npsn')->ignore($schoolId)],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kabupaten' => ['nullable', 'string', 'max:255'],
            'provinsi' => ['nullable', 'string', 'max:255'],
            'kepala_nama' => ['required', 'string', 'max:255'],
            'kepala_nip' => ['nullable', 'string', 'max:30'],
            'kepala_sk' => ['nullable', 'string', 'max:255'],
            'bendahara_nama' => ['required', 'string', 'max:255'],
            'bendahara_nip' => ['nullable', 'string', 'max:30'],
            'bendahara_sk' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
