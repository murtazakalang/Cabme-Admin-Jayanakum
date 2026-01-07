<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Transaction;
use App\Models\Driver;
use Braintree\Test\Transaction as TestTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index(Request $request, $id = '')
  {

    $transaction = Transaction::join('user_app', 'transactions.user_id', '=', 'user_app.id')
      ->join('payment_method', 'payment_method.libelle', '=', 'transactions.payment_method')
      ->select('user_app.id as userId', 'user_app.nom as lastname', 'user_app.prenom as firstname')
      ->addSelect('transactions.*', 'payment_method.image')
      ->where('transactions.user_type', '=', 'customer');
    if ($id) {
      $transaction->where('transactions.user_id', '=', $id);
    }
    if ($request->has('search') && $request->search != '') {
      $search = $request->input('search');
      if ($request->selected_search == 'transaction_id') {
        $transaction->where('transactions.id', 'LIKE', '%' . $search . '%');
      } else if ($request->selected_search == 'username') {
        $transaction->where('user_app.prenom', 'LIKE', '%' . $search . '%')
          ->orwhere('user_app.nom', 'LIKE', '%' . $search . '%')
          ->orWhere(DB::raw('CONCAT(user_app.prenom, " ",user_app.nom)'), 'LIKE', '%' . $search . '%');
      }
    }
    $transaction->orderBy('transactions.id', 'desc');

    $totalLength = count($transaction->get());
    $perPage = $request->input('per_page', 20);
    $transaction = $transaction->paginate($perPage)->appends($request->all());
    $currency = Currency::where('statut', 'yes')->first();
    return view("transactions.index", compact('id', 'transaction', 'currency', 'totalLength','perPage'));
  }
  public function driverWallet(Request $request, $id = '')
  {
      $transaction = Transaction::join('conducteur', 'transactions.user_id', '=', 'conducteur.id')
        ->join('payment_method', 'payment_method.libelle', '=', 'transactions.payment_method')
        ->select('conducteur.id as userId', 'conducteur.nom as lastname', 'conducteur.prenom as firstname','conducteur.role')
        ->addSelect('transactions.*', 'payment_method.image')
        ->where('transactions.user_type', '=', 'driver');

      if ($request->has('search') && $request->search != ''){
        $search = $request->input('search');
        if($request->selected_search == 'transaction_id') {
          $transaction->where('transactions.id', 'LIKE', '%'.$search.'%');
        }
        else if ($request->selected_search == 'username') {
          $transaction->where('conducteur.prenom', 'LIKE', '%' . $search . '%')
            ->orwhere('conducteur.nom', 'LIKE', '%' . $search . '%')
            ->orWhere(DB::raw('CONCAT(conducteur.prenom, " ",conducteur.nom)'), 'LIKE', '%' . $search . '%');
        }
      } 

      if ($id) {
        $transaction->where('transactions.user_id', $id);
        
        $userRole = Driver::where('id', $id)->value('role');
      } else {
        $userRole = null;
      }

      $transaction->orderBy('transactions.id', 'desc');
      $totalLength = $transaction->count();
      $perPage = $request->input('per_page', 20);
      $transaction = $transaction->paginate($perPage)->appends($request->all());
      $currency = Currency::where('statut', 'yes')->first();

      return view("transactions.driver_wallet", compact(
          'transaction', 'currency', 'id', 'totalLength', 'perPage', 'userRole'
      ));
  }

}
