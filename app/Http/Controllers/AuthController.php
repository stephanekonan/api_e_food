<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseAuthService;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuthService $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 400);
        }

        try {
            $firebaseUser = $this->firebaseAuth->signInWithEmailAndPassword(
                $request->email,
                $request->password
            );

            $idToken = $firebaseUser->idToken();
            return response()->json([
                'message' => 'Login successful', 
                'token' => $idToken
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid email or password '. $e->getMessage()
            ], 400);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'username' => 'required|string',
            'phone' => 'required|string',
            'role' => 'required|string|in:client,boutique',
            'nom_boutique' => 'required_if:role,boutique|string',
            'location' => 'required|string',
            'hour_begin' => 'required_if:role,boutique|date_format:H:i',
            'hour_end' => 'required_if:role,boutique|date_format:H:i',
        ], [
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'Veuillez fournir une adresse email valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit comporter au moins 6 caractères.',
            'username.required' => 'Veuillez entrer un nom d\'utilisateur.',
            'username.string' => 'Le nom d\'utilisateur doit être une chaîne de caractères.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'role.required' => 'Veuillez sélectionner un rôle.',
            'role.string' => 'Le rôle doit être une chaîne de caractères.',
            'role.in' => 'Le rôle doit être soit client, soit boutique.',
            'nom_boutique.required_if' => 'Le nom de la boutique est obligatoire pour les utilisateurs de type boutique.',
            'location.string' => 'La localisation doit être une chaîne de caractères.',
            'location.required' => 'Le lieu est obligatoire.',
            'hour_begin.required_if' => 'L\'heure de début est obligatoire pour une boutique.',
            'hour_end.required_if' => 'L\'heure de fin est obligatoire pour une boutique.',
            'hour_begin.date_format' => 'L\'heure de début doit être au format HH:mm.',
            'hour_end.date_format' => 'L\'heure de fin doit être au format HH:mm.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 400);
        }

        if ($request->role == 'boutique') {
            $specialiteData = $this->firebaseAuth->getSpecialitiesData($request->specialite_id);
            if (empty($specialiteData)) {
                return response()->json([
                    'error' => 'La spécialité choisie n\'existe pas dans notre base de données.'
                ], 400);
            }
        }

        try {
            $firebaseUser = $this->firebaseAuth->createUserWithEmailAndPassword(
                $request->email,
                $request->password
            );

            $userData = [
                'uid'=> $firebaseUser->uid,
                'username' => $request->username,
                'email' => $request->email,
                'role' => $request->role ?? 'client',
                'phone' => $request->phone,
                'created_at' => now()->toDateTimeString(),
            ];

            if ($request->role == 'boutique') {

                $userData = array_merge($userData, [
                    'nom_boutique' => $request->nom_boutique,
                    'specialite_id' => $request->specialite_id,
                    'location' => $request->location,
                    'hour_begin' => $request->hour_begin,
                    'hour_end' => $request->hour_end,
                ]);
            }

            $user = $this->firebaseAuth->saveUserData($firebaseUser->uid, $userData);

            return response()->json([
                'message' => 'User registered successfully',
                'data'=> $user->getValue(),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function profile(Request $request)
    {
        $idToken = $request->bearerToken();

        if (!$idToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $verifiedToken = $this->firebaseAuth->verifyIdToken($idToken);
            $uid = $verifiedToken->claims()->get('sub');

            $userData = $this->firebaseAuth->getUserData($uid);

            if (!$userData) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return response()->json(['user' => $userData], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

}
