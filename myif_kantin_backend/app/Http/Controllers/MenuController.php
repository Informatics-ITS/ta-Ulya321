<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('vendor')->get();
        $vendors = Vendor::where('is_active', true)->get();
        return view('content.menus.index', compact('menus', 'vendors'));
    }

    public function saveMenu(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,vendor_id',
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'menu_image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['vendor_id', 'name', 'description', 'price', 'is_available']);
        $data['is_available'] = $request->has('is_available') ? true : false;

        if ($request->hasFile('menu_image')) {
            // Create upload directory if it doesn't exist
            $uploadDir = public_path('upload');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $image = $request->file('menu_image');
            $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
            $image->move($uploadDir, $imageName);
            $data['photo_url'] = '/upload/' . $imageName;
        }

        if ($request->menu_id) {
            $menu = Menu::find($request->menu_id);
            
            // If there's a new image and the menu already has an image, delete the old one
            if ($request->hasFile('menu_image') && $menu->photo_url) {
                $oldImagePath = public_path(ltrim($menu->photo_url, '/'));
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            $menu->update($data);
            $message = 'Menu updated successfully';
        } else {
            Menu::create($data);
            $message = 'Menu created successfully';
        }

        return redirect()->route('menus.index')->with('success', $message);
    }

    public function deleteMenu($id)
    {
        $menu = Menu::find($id);
        if ($menu) {
            // Delete the menu image if exists
            if ($menu->photo_url) {
                $imagePath = public_path(ltrim($menu->photo_url, '/'));
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $menu->delete();
            return redirect()->route('menus.index')->with('success', 'Menu deleted successfully');
        }
        return redirect()->route('menus.index')->with('error', 'Menu not found');
    }
}