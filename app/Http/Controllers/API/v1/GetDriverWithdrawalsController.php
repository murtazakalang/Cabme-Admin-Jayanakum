<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Validator;

class GetDriverWithdrawalsController extends Controller
{
    public function withdrawalsList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_driver' => 'required|integer|exists:conducteur,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 422,
                'message' => $validator->errors()->first(),
                'data'    => null,
            ]);
        }

        $id_driver = $request->get('id_driver');

        $withdrawals = Withdrawal::where('id_conducteur', $id_driver)
            ->orderBy('creer', 'desc')
            ->get();

        if ($withdrawals->isEmpty()) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 404,
                'message' => 'No withdrawals found for this driver.',
                'data'    => null,
            ]);
        }

        $driver = Driver::find($id_driver);

        $output = $withdrawals->map(function ($withdrawal) use ($driver) {
            return [
                'id'            => (string) $withdrawal->id,
                'amount'        => $withdrawal->amount,
                'creer'         => $withdrawal->creer,
                'modifier'      => $withdrawal->modifier,
                'statut'        => $withdrawal->statut,
                'note'          => $withdrawal->note,
                'id_conducteur' => $driver->id,
                'bank_name'     => $driver->bank_name,
                'branch_name'   => $driver->branch_name,
                'account_no'    => $driver->account_no,
                'other_info'    => $driver->other_info,
                'ifsc_code'     => $driver->ifsc_code,
            ];
        });

        return response()->json([
            'success' => 'success',
            'code'    => 200,
            'message' => 'Withdrawals fetched successfully.',
            'data'    => $output,
        ]);
    }
}
