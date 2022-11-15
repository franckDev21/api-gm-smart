<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Mail\ContactMail;
use App\Mail\RegisterUserInfoMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\TotalCash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportUser;
use App\Exports\ExportUser;
use App\Imports\ImportProduct;
use App\Models\AdminUser;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->role === 'SUPER') {
            return UserResource::collection(User::with('company')->where('role', 'ENTREPRISE')->get());
        }
        // return UserResource::collection(User::all());
    }

    public function getAdminUsers(Request $request)
    {
        if (!$request->user()->hasRole('super')) {
            return response([
                'error' => 'No'
            ], 403);
        }

        return AdminUser::with(['companies'])->latest()->get();
    }

    public function getAdminUser(Request $request, AdminUser $adminUser)
    {
        if (!$request->user()->hasRole('super')) {
            return response([
                'error' => 'No'
            ], 403);
        }

        return AdminUser::where('id', $adminUser->id)->with(['companies'])->first();
    }


    public function dashboard(Request $request)
    {

        $totalUser = User::where('company_id', $request->user()->company_id)
            ->where('id', '!=', $request->user()->id)
            ->get()->count();

        $caisse = TotalCash::where('company_id', $request->user()->company_id)->first();
        if (!$caisse) {
            $caisse = TotalCash::create([
                'montant' => 0,
                'company_id' => $request->user()->company_id
            ]);
        }

        $totalCash = $request->user()->company_id ? TotalCash::where('company_id', $request->user()->company_id)->first()->montant : 0;

        $totalCustomer =  $request->user()->company_id ?  Customer::where('company_id', $request->user()->company_id)->count() : 0;
        $totalOrder =  $request->user()->company_id ?  Order::where('company_id', $request->user()->company_id)->count() : 0;
        $totalProduct =  $request->user()->company_id ?  Product::where('company_id', $request->user()->company_id)->count() : 0;

        return response([
            'totalCash' => $totalCash,
            'totalCustomer' => $totalCustomer,
            'totalProduct' => $totalProduct,
            'totalUser' => $totalUser,
            'totalOrder' => $totalOrder
        ], 201);
    }

    public function dashboardAdmin(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            return response([
                'error' => 'No'
            ], 403);
        }
        // $totalUsers =  User::where('admin_user_id', $request->user()->id)->count();

        $totalUsers = 0;

        $companiesUser = Company::with('users')->where('admin_user_id', $request->user()->id)->get();
        foreach ($companiesUser as $c) {
            $totalUsers += count($c->users);
        }
        $totalCompany =  Company::where('admin_user_id', $request->user()->id)->count();

        return response([
            'totalCompany' => $totalCompany,
            'totalUsers' => $totalUsers,
        ], 201);
    }

    public function dashboardSuper(Request $request)
    {
        if (!$request->user()->hasRole('super')) {
            return response([
                'error' => 'No'
            ], 403);
        }

        return AdminUser::all()->count();
    }

    public function dashboardCompany(Request $request, Company $company)
    {
        $totalUser = 0;

        $companiesUser = Company::with('users')->where('id', $company->id)->get();
        foreach ($companiesUser as $c) {
            $totalUser += count($c->users);
        }

        $caisse = TotalCash::where('company_id', $company->id)->first();
        if (!$caisse) {
            $caisse = TotalCash::create([
                'montant' => 0,
                'company_id' => $company->id
            ]);
        }

        $totalCash = TotalCash::where('company_id', $company->id)->first()->montant;

        $totalCustomer = Customer::where('company_id', $company->id)->count();
        $totalOrder = Order::where('company_id', $company->id)->count();
        $totalProduct = Product::where('company_id', $company->id)->count();

        return response([
            'totalCash' => $totalCash,
            'totalCustomer' => $totalCustomer,
            'totalProduct' => $totalProduct,
            'totalUser' => $totalUser,
            'totalOrder' => $totalOrder
        ], 201);
    }

    public function getUsers(Request $request)
    {
        if (!$request->user()->hasRole('gerant') && !$request->user()->hasRole('admin')) {
            return response([
                'error' => 'No'
            ], 403);
        }

        if (!$request->user()->hasRole('admin')) {
            $userTabls = [];
            $companiesUser = Company::with('users')
                ->where('id', $request->user()->company_id)->get();
            foreach ($companiesUser as $c) {
                foreach ($c->users as $user) {
                    if ($user->id !== $request->user()->id) {
                        $user->roles;
                        $userTabls[] = $user;
                    }
                }
            }

            return $userTabls;
            // return UserResource::collection(
            //     User::with(['company', 'roles'])
            //         ->where('admin_user_id', $request->user()->id)
            //         ->where('id', $request->user()->id)
            //         ->get()
            // );
        } else {
            $userTabls = [];
            $companiesUser = Company::with('users')->where('admin_user_id', $request->user()->id)->get();
            foreach ($companiesUser as $c) {
                foreach ($c->users as $user) {
                    $user->roles;
                    $userTabls[] = $user;
                }
            }

            return $userTabls;

            // return UserResource::collection(
            //     User::with(['company', 'roles'])
            //         ->where('role', 'USER')
            //         ->where('admin_user_id', $request->user()->id)
            //         ->where('id', '!=', $request->user()->id)
            //         ->get()
            // );
        }
    }

    public function updateUserInfo(Request $request)
    {
        $request->validate([
            'firstname'  => 'required|string|min:3|max:200',
            'lastname'  => 'required|string|min:3|max:200',
            'email' => 'required|email',
        ]);

        $request->user()->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
        ]);

        $user = User::findOrFail($request->user()->id);

        return response($user, 201);
    }

    public function updateUserPicture(Request $request)
    {

        $request->validate([
            'image' => 'required|mimes:png,jpg,jpeg,PNG,JPG,JPEG,jfif,JFIF|max:4000'
        ]);

        $filename = time() . '.' . $request->image->extension();
        $path = $request->image->storeAs('img/users', $filename, 'public');

        $request->user()->update([
            'photo' => $path
        ]);

        return response([
            'message' => 'Your profile picture has been successfully updated ! ',
            'path'    => $path
        ], 201);
    }

    public function toggleActiveUserCompany(User $user)
    {
        $user->update([
            'active' => !$user->active
        ]);

        $res = $user->active ? 'activated' : 'deactivated';

        return response([
            'message' => "The company has been {$res}"
        ], 201);
    }

    public function toggleActiveAdminUser(AdminUser $adminUser)
    {
        $adminUser->update([
            'active' => !$adminUser->active
        ]);

        $res = $adminUser->active ? 'activé' : 'désactivé';

        return response([
            'message' => "Cette administrateur a bien été {$res}"
        ], 201);
    }

    public function toggleActiveUser(User $user)
    {
        $user->update([
            'active' => !$user->active
        ]);

        $res = $user->active ? 'activé ' : 'désactivé';

        return response([
            'message' => "Cette utilisateur a bien été {$res}"
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Company $company)
    {
        if (!$request->user()->hasRole('admin') && !$request->user()->hasRole('gerant')) {
            return response([
                'error' => 'No'
            ], 403);
        }

        $request->validate([
            'firstname' => 'required|string|min:2|max:250',
            'lastname' => 'required|string|min:2|max:250',
            'email' => 'required|string|min:2|max:250|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        $active = $request->active ? true : false;

        $user = User::create([
            'firstname'  => $request->firstname,
            'lastname'   => $request->lastname,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'company_id' => $request->company_id ?? $request->user()->company_id,
            'active'     => $active,
        ]);

        $role = ($request->role === 'GERANT' ?
            'gerant' : ($request->role === 'CAISSIER' ?
                'caissier' : ($request->role === 'USER' ?
                    'user' : 'gerany'))
        ) ?? 'user';

        $role = $role === 'gerant' ? 'user' : $role;

        $user->attachRole($role);

        Mail::to($request->email)
            ->send(new RegisterUserInfoMail($user, $request->password));

        return response([
            'message' => 'Votre utilisateur a été crée avec succès !'
        ], 201);
    }

    public function storeAdminUser(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|min:2|max:250',
            'lastname' => 'required|string|min:2|max:250',
            'email' => 'required|string|min:2|max:250|email|unique:admin_users',
            'password' => 'required|string|confirmed'
        ]);

        $active = $request->active ? true : false;

        $user = AdminUser::create([
            'firstname'  => $request->firstname,
            'lastname'   => $request->lastname,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'active'     => $active,
        ]);

        $user->attachRole('admin');

        Mail::to($request->email)
            ->send(new RegisterUserInfoMail($user, $request->password));

        return response([
            'message' => 'Votre administrateur a été crée avec succès !'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return UserResource::make($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }

    public function deleteUserCompany(User $user)
    {
        $user->delete();

        return response([
            'message' => "L'entreprise '{$user->company->name}'  a été supprimé avec succès !"
        ], 201);
    }

    public function deleteAdminUser(AdminUser $adminUser)
    {
        $adminUser->delete();

        return response([
            'message' => "L'administrateur '{$adminUser->firstname} {$adminUser->lastname}'  a été supprimé avec succès !"
        ], 201);
    }

    public function contact(Request $request)
    {
        Mail::to('tiomelafranck724@gmail.com')
            ->send(new ContactMail([
                'email' => $request->email,
                'name' => $request->name,
                'tel' => $request->tel,
                'content' => $request->content,
            ]));

        return response([
            'message' => 'votre a été envoyer avec succès !'
        ], 201);
    }



    public function importView(Request $request)
    {
        return view('importFile');
    }

    public function importProduct(Request $request)
    {
        $path = $request->file->store('files');
        Excel::import(new ImportProduct($request->user()->company_id), $path);
        Storage::delete($path);
        return response(['message' => "Votre liste des produits a été ajouté avec succès !"]);
    }

    public function exportUsers(Request $request)
    {
        return Excel::download(new ExportUser, 'users.xlsx');
    }
}
