<?php

namespace App\Http\Controllers;

use App\Models\ParcelCategory;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Image;
use Validator;

class ParcelCategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        if ($request->has('search') && $request->search != '' && $request->selected_search == 'title') {
            $search = $request->input('search');
            $parcelCategory = DB::table('parcel_category')
                ->where('parcel_category.title', 'LIKE', '%' . $search . '%');
            $totalLength = count($parcelCategory->get());
            $parcelCategory = $parcelCategory->paginate($perPage)->appends($request->all());
        } else {
            $totalLength = count(ParcelCategory::get());
            $parcelCategory = ParcelCategory::paginate($perPage)->appends($request->all());
        }
        return view("parcel_category.index",compact('parcelCategory', 'totalLength', 'perPage'));
    }

    public function create()
    {
        return view("parcel_category.create");
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), $rules = [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|mimes:jpeg,jpg,png',

        ], $messages = [
            'title.required' => trans('lang.title_required'),
            'image.required' => trans('lang.the_image_field_is_required'),
        ]);

        if ($validator->fails()) {
            return redirect('parcel-category/create')
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }
        $filename = '';
        if ($request->hasfile('image')) {
            $file = $request->file('image');
            $extenstion = $file->getClientOriginalExtension();
            $time = time() . '.' . $extenstion;
            $filename = 'parcel_category_' . $time;
            $path = public_path('assets/images/parcel_category/') . $filename;
            if (!file_exists(public_path('assets/images/parcel_category/'))) {
                mkdir(public_path('assets/images/parcel_category/'), 0777, true);
            }
            Image::make($file->getRealPath())->resize(100, 100)->save($path);
        }

        ParcelCategory::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => $request->input('status') ? 'yes' : 'no',
            'image' => $filename

        ]);

        return redirect('parcel-category')->with('message', trans('lang.parcel_category_created'));

    }


    public function edit($id)
    {
        $parcelCategory = ParcelCategory::where('id', "=", $id)->first();
        return view("parcel_category.edit")->with("parcelCategory", $parcelCategory);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $rules = [
            'title' => 'required',
            'description' => 'required',
            'image' => ($request->hasfile('image')) ? 'required|mimes:jpeg,jpg,png' : "",
        ], $messages = [
            'title.required' => trans('lang.title_required'),
            'image.required' => trans('lang.the_image_field_is_required'),
        ]);

        if ($validator->fails()) {
            return redirect('parcel-category/edit/' . $id)
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }
        $parcelCategory = ParcelCategory::find($id);
        $filename = $parcelCategory->image;

        $title = $request->input('title');
        $description = $request->input('description');
        $status = $request->input('status') ? 'yes' : 'no';
        if ($request->hasfile('image')) {
            $destination = public_path('assets/images/parcel_category/' . $parcelCategory->image);
            if (File::exists($destination)) {
                File::delete($destination);
            }
            $file = $request->file('image');
            $extenstion = $file->getClientOriginalExtension();
            $filename = 'parcel_category_' . $id . '.' . $extenstion;
            $path = public_path('assets/images/parcel_category/') . $filename;
            if (!file_exists(public_path('assets/images/parcel_category/'))) {
                mkdir(public_path('assets/images/parcel_category/'), 0777, true);
            }
            Image::make($file->getRealPath())->resize(100, 100)->save($path);
        }
        if ($parcelCategory) {
            $parcelCategory->title = $title;
            $parcelCategory->description = $description;
            $parcelCategory->status = $status;
            $parcelCategory->image = $filename;
            $parcelCategory->save();
        }

        return redirect('parcel-category')->with('message', trans('lang.parcel_category_updated'));
    }

    public function delete($id)
    {

        if ($id != "") {

            $id = json_decode($id);

            if (is_array($id)) {

                for ($i = 0; $i < count($id); $i++) {
                    $parcelCategory = ParcelCategory::find($id[$i]);
                    
                    $destination = public_path('assets/images/parcel_category/' . $parcelCategory->image);
                    if (File::exists($destination)) {
                        File::delete($destination);
                    }

                    $parcelCategory->delete();
                }

            } else {
                
                $parcelCategory = ParcelCategory::find($id);
                
                $destination = public_path('assets/images/parcel_category/' . $parcelCategory->image);
                if (File::exists($destination)) {
                    File::delete($destination);
                }

                $parcelCategory->delete();
            }

        }

        return redirect()->back();
    }

    public function changeStatus($id)
    {
        $parcelCategory = parcelCategory::find($id);
        if ($parcelCategory->status == 'no') {
            $parcelCategory->status = 'yes';
        } else {
            $parcelCategory->status = 'no';
        }

        $parcelCategory->save();
        return redirect()->back();

    }

    public function toggalSwitch(Request $request)
    {
        $ischeck = $request->input('ischeck');
        $id = $request->input('id');
        $parcelCategory = ParcelCategory::find($id);

        if ($ischeck == "true") {
            $parcelCategory->status = 'yes';
        } else {
            $parcelCategory->status = 'no';
        }
        $parcelCategory->save();

    }


}
