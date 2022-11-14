<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\SuperUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        if ($request->type && $request->type === 'ENTREPRISE') {
            $request->validate([
                'firstname'  => 'required|string|min:3|max:200',
                'lastname'  => 'required|string|min:3|max:200',
                'email' => 'required|email|unique:users,email|min:3|max:200',
                'password' => 'required|string'
            ]);
        } else {
            $request->validate([
                'firstname'  => 'required|string|min:3|max:200',
                'lastname'  => 'required|string|min:3|max:200',
                'email' => 'required|email|unique:users,email|min:3|max:200',
                'password' => 'required|string|confirmed'
            ]);
        }

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname'  => $request->lastname,
            'email'     => $request->email,
            'password'  => Hash::make($request->password)
        ]);

        $token = $user->createToken('M2mwMYQ91JKNfw610oK53ze5uFJ8LocsInqzZL')->plainTextToken;

        $response = [
            'user'  => User::findOrFail($user->id),
            'token' => $token
        ];

        return response($response, 201);
    }

    private function getTabName(array $tabs)
    {
        $newTab = [];
        foreach ($tabs as $tab) {
            $newTab[] = $tab['name'];
        }

        return $newTab;
    }

    public function login(Request $request)
    {
        // $basic  = new \Vonage\Client\Credentials\Basic("88ca908d", "2pAn4AWNsGYl8xmc");
        // $client = new \Vonage\Client($basic);

        // $response = $client->sms()->send(
        //     new \Vonage\SMS\Message\SMS("237695426414","GM-SMART", 'Bonjour et bienvenu !  Mr(Mme) . Votre inscription s\'est effectuée avec succès et vous pouvez maintenant profiter de l’application.')
        // );

        // $message = $response->current();

        $role = 'USER';
        $prermissions = [];

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        // Check email
        $user = User::where('email', $request->email)->first();
        $adminUser = AdminUser::where('email', $request->email)->first();
        $superUser = SuperUser::where('email', $request->email)->first();

        // Check password
        if (
            (!$user || !Hash::check($request->password, $user->password)) &&
            (!$adminUser || !Hash::check($request->password, $adminUser->password)) &&
            (!$superUser || !Hash::check($request->password, $superUser->password))
        ) {
            return response([
                'message' => 'Incorrect identifier'
            ], 401);
        }

        if ($user) {
            $roles = $this->getTabName($user->roles->toArray());
            $token = $user->createToken('M2mwMYQ91JKfw5M2mwMYQM2mwMYQ91JKfw5uFocsInqzZL91JKfw5uFJ8LocsInqzZLuFJcsInqzZL')->plainTextToken;
            $prermissions = $this->getTabName($user->allPermissions()->toArray());
        } else if ($adminUser) {
            $roles = $this->getTabName($adminUser->roles->toArray());
            $token = $adminUser->createToken('M2mwMYQ91JKfw5M2mwMYQM2mwMYQ91JKfw5uFocsInqzZL91JKfw5uFJ8LocsInqzZLuFJcsInqzZL')->plainTextToken;
            $prermissions = $this->getTabName($adminUser->allPermissions()->toArray());
        } else if ($superUser) {
            $roles = $this->getTabName($superUser->roles->toArray());
            $token = $superUser->createToken('M2mwMYQ91JKfw5M2mwMYQM2mwMYQ91JKfw5uFocsInqzZL91JKfw5uFJ8LocsInqzZLuFJcsInqzZL')->plainTextToken;
            $prermissions = $this->getTabName($superUser->allPermissions()->toArray());
        }

        if (($user->active ?? false) || ($adminUser->active ?? false) || ($superUser->active ?? false)) {
            $response = [
                'user'  => $user ?? $adminUser ?? $superUser,
                'token' => $token,
                'roles' => $roles,
                'prermissions' => $prermissions
            ];

            return response($response, 201);
        } else {
            return response([
                'error' => "Votre compte n’a pas encore été activé"
            ], 201);
        }
    }

    public function updateUserPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string',
            'confirm_password' => 'required|string'
        ]);


        if (!Hash::check($request->old_password, $request->user()->password)) {
            return "Incorrect password";
        }

        if (trim($request->new_password) !== trim($request->confirm_password)) {
            return "The passwords do not match";
        }

        $request->user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response([
            'message' => 'Your password has been successfully updated'
        ], 201);
    }


    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return response([
            'message' => 'Logged Out'
        ]);
    }

    public function getUserInfo(Request $request)
    {
        return $request->user();
    }
}
