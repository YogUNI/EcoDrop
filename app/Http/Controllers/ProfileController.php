<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // ← METHOD BARU: Upload foto profile
    public function updatePhoto(Request $request): RedirectResponse
    {
        if (!$request->filled('cropped_photo') && !$request->hasFile('profile_photo')) {
            return back()->withErrors(['profile_photo' => 'Pilih foto terlebih dahulu']);
        }

        if ($request->filled('cropped_photo')) {
            $request->validate(['cropped_photo' => 'required|string']);
        } else {
            $request->validate([
                'profile_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            ], [
                'profile_photo.required' => 'Pilih foto terlebih dahulu',
                'profile_photo.image'    => 'File harus berupa gambar',
                'profile_photo.mimes'    => 'Format foto harus jpg, jpeg, png, atau webp',
                'profile_photo.max'      => 'Ukuran foto maksimal 4MB',
            ]);
        }

        $user = $request->user();
        $newPhotoPath = null;

        if ($request->filled('cropped_photo')) {
            $imageData = $request->input('cropped_photo');

            if (!preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $imageData)) {
                return back()->withErrors(['profile_photo' => 'Format crop foto tidak valid']);
            }

            $base64Data = substr($imageData, strpos($imageData, ',') + 1);
            $binaryData = base64_decode($base64Data, true);

            if ($binaryData === false || strlen($binaryData) > 4 * 1024 * 1024) {
                return back()->withErrors(['profile_photo' => 'Foto hasil crop tidak valid atau terlalu besar']);
            }

            $imageInfo = @getimagesizefromstring($binaryData);
            if ($imageInfo === false || !in_array($imageInfo['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
                return back()->withErrors(['profile_photo' => 'Foto hasil crop tidak valid']);
            }

            $extension = match ($imageInfo['mime']) {
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };

            $newPhotoPath = 'profile-photos/profile-' . $user->id . '-' . now()->format('YmdHis') . '.' . $extension;
            Storage::disk('public')->put($newPhotoPath, $binaryData);
        } else {
            $newPhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        // Hapus foto lama kalau ada
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $user->update(['profile_photo' => $newPhotoPath]);

        return Redirect::route('profile.edit')->with('status', 'photo-updated');
    }

    // ← METHOD BARU: Hapus foto profile
    public function deletePhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
            $user->update(['profile_photo' => null]);
        }

        return Redirect::route('profile.edit')->with('status', 'photo-deleted');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Hapus foto profile kalau ada
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
