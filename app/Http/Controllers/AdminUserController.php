<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Helpers\Helper;
use Validator;

class AdminUserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $users = User::where('users.id', '!=', 1)->paginate($perPage)->appends($request->all());
        $totalLength = count($users);

        $users->map(function($user) {
            if(!empty($user->email)){
                $user->email = Helper::shortEmail($user->email);
            }
            if(!empty($user->phone)){
                $user->phone = Helper::shortNumber($user->phone);
            }
            return $user;
        });
        return view('admin_users.index', compact(['users','totalLength','perPage']));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin_users.create', compact(['roles']));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            return redirect()->back()->with(['message' => $errorMessage])->withInput();
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $role = Role::find($request->input('role_id'));
        if ($role) {
            $user->syncRoles([$role->id]);
        }
        $user->save();

        return redirect('admin-users')->with('message', trans('lang.admin_user_created_successfully'));
    }

    public function edit($id)
    {
        $user = User::find($id);
        $roleId = $user->roles->first()->id ?? null;
        $roles = Role::all();
        return view('admin_users.edit', compact(['user', 'roles', 'roleId']));
    }

    public function update(Request $request, $id)
    {
        $name = $request->input('name');
        $password = $request->input('password');
        $old_password = $request->input('old_password');
        $email = $request->input('email');
        
        if ($password == '') {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:255',
                'email' => 'required|email'
            ]);
        } else {
            $user = User::find($id);
            if (password_verify($old_password, $user->password)) {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:255',
                    'password' => 'required|min:8',
                    'confirm_password' => 'required|same:password',
                    'email' => 'required|email'
                ]);
            } else {
                return Redirect()->back()->with(['message' => trans('lang.please_enter_correct_old_password')]);
            }
        }

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return Redirect()->back()->with(['message' => $error]);
        }

        $user = User::find($id);
        if ($user) {
            $user->name = $name;
            $user->email = $email;
            if ($password != '') {
                $user->password = Hash::make($password);
            }
            $role = Role::find($request->input('role_id'));
            if ($role) {
                $user->syncRoles([$role->id]);
            }
            $user->save();
        }

        return redirect('admin-users')->with('message', trans('lang.admin_user_updated_successfully'));
    }

    public function delete($id)
    {
        $id = json_decode($id);

        if (is_array($id)) {

            for ($i = 0; $i < count($id); $i++) {
                $users = User::find($id[$i]);
                $users->delete();
            }
        } else {
            $user = User::find($id);
            $user->delete();
        }

        return redirect()->back();
    }

}
