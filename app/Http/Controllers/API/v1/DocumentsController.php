<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use App\Models\DriversDocuments;
use App\Helpers\Helper;
use Validator;

class DocumentsController extends Controller
{
    public function __construct()
    {
        $this->limit = 20;
    }
    
    public function updateDriverDocuments(Request $request)
    {
        $driver_id = $request->get('driver_id');
        $document_id = $request->get('document_id');
        $attachment = $request->file('attachment');
        if (empty($document_id) || $document_id == 0) {
            $response['success'] = 'Failed';
            $response['error'] = 'Document Id Not Found';
        } else if (empty($driver_id) || $driver_id == 0) {
            $response['success'] = 'Failed';
            $response['error'] = 'Driver Id Not Found';
        } else if (empty($attachment)) {
            $response['success'] = 'Failed';
            $response['error'] = 'Attachment Not Found';
        } else {
            $file = $request->file('attachment');
            $extenstion = $file->getClientOriginalExtension();
            $document_name = DB::table('admin_documents')->where('id', $document_id)->first();
            $filename = str_replace(' ', '_', $document_name->title) . '_' . time() . '.' . $extenstion;
            Helper::compressFile($file->getPathName(), public_path('assets/images/driver/documents') . '/' . $filename, 8);
            $get_driver_document = DB::table('driver_document')->where('document_id', $document_id)->where('driver_id', $driver_id)->first();
            if ($get_driver_document) {
                if (file_exists(public_path('assets/images/driver/documents' . '/' . $get_driver_document->document_path))) {
                    unlink(public_path('assets/images/driver/documents' . '/' . $get_driver_document->document_path));
                }
                $driver_document = DriversDocuments::find($get_driver_document->id);
                $driver_document->document_path = $filename;
                $driver_document->document_status = 'Pending';
                $driver_document->save();
            } else {
                $driver_document = new DriversDocuments;
                $driver_document->driver_id = $driver_id;
                $driver_document->document_id = $document_id;
                $driver_document->document_path = $filename;
                $driver_document->document_status = 'Pending';
                $driver_document->save();
            }
            $get_driver_document = DB::table('driver_document')->where('document_id', $document_id)->where('driver_id', $driver_id)->first();
            if ($get_driver_document) {
                $get_driver_document->document_path = url('assets/images/driver/documents/' . $get_driver_document->document_path);
                $get_driver_document->document_name = $document_name->title;
                $get_driver_document->id = $get_driver_document->document_id;
                unset($get_driver_document->document_id);
                $response['success'] = 'Success';
                $response['error'] = null;
                $response['message'] = $document_name->title . ' Updated';
                $response['data'] = $get_driver_document;
            } else {
                $response['success'] = 'Failed';
                $response['error'] = $document_name->title . ' Not Updated';
            }
        }
        return response()->json($response);
    }
    
    public function getDriverDocuments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_id'    => 'required|integer|exists:conducteur,id',
            'type' => 'required|in:driver,owner',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error'   => $validator->errors()->first(),
            ]);
        }

        $driver_id = $request->get('driver_id');
        $type = $request->get('type');

        $admin_documents = DB::table('admin_documents')->where('is_enabled', '=', 'Yes')->where('type', '=', $type)->get();
        
        if (!empty($admin_documents)) {

            foreach ($admin_documents as $key => $document) {
                $id = $document->id;
                $get_driver_document = DB::table('driver_document')->where('document_id', $document->id)->where('driver_id', $driver_id)->first();
                $document->id = (string)$id;
                if ($get_driver_document) {
                    $document->document_path = url('assets/images/driver/documents/' . $get_driver_document->document_path);
                    $document->document_status = $get_driver_document->document_status;
                    $document->comment = $get_driver_document->comment;
                } else {
                    $document->document_path = '';
                    $document->document_status = 'Pending';
                    $document->comment = '';
                }
                $document->document_name = $document->title;
                $admin_documents[$key] = $document;
            }

            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Documents successfully fetch';
            $response['data'] = $admin_documents;
        } else {
            $response['success'] = 'Failed';
            $response['error'] = 'Failed to fetch data';
            $response['message'] = 'No documents found';
        }

        return response()->json($response);
    }
}
