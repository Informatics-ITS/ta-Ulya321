<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::all();
        return view('content.vendors.index', compact('vendors'));
    }

    public function saveVendor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string',
            'qris_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'is_active']);
        $data['is_active'] = $request->has('is_active') && $request->is_active == '1' ? true : false;

        if ($request->hasFile('qris_image')) {
            $qrisDir = public_path('assets/qris');
            if (!is_dir($qrisDir)) {
                mkdir($qrisDir, 0755, true);
            }

            $qrisImage = $request->file('qris_image');
            $imageName = time() . '_' . uniqid() . '.' . $qrisImage->getClientOriginalExtension();
            $qrisImage->move($qrisDir, $imageName);
            $data['qris_image'] = $imageName;
        }

        if ($request->vendor_id) {
            $vendor = Vendor::find($request->vendor_id);
            
            if ($request->hasFile('qris_image') && $vendor->qris_image) {
                $oldImagePath = public_path('assets/qris/' . $vendor->qris_image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            $vendor->update($data);
            $message = 'Vendor updated successfully';
        } else {
            Vendor::create($data);
            $message = 'Vendor created successfully';
        }

        return redirect()->route('vendors.index')->with('success', $message);
    }

    public function deleteVendor($id)
    {
        $vendor = Vendor::find($id);
        if ($vendor) {
            if ($vendor->qris_image) {
                $imagePath = public_path('assets/qris/' . $vendor->qris_image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $vendor->delete();
            return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully');
        }
        return redirect()->route('vendors.index')->with('error', 'Vendor not found');
    }
}