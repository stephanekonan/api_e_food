<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\Validator;
use App\Services\NotificationService;

class CommandeController extends Controller
{
    protected $database;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->database = Firebase::database();
        $this->notificationService = $notificationService;
    }

    public function create(Request $request)
    {
        $data = Validator::make($request->all(), [
            'client_id' => 'required|string',
            'boutique_id' => 'required|string',
            'plats' => 'required|array|min:1',
            'total' => 'required|numeric',
            'status' => 'required|string|in:en_attente,en_cours,terminee,annulee',
        ], [
            'client_id.required' => 'Le client_id est requis.',
            'client_id.string' => 'Le client_id doit être une chaîne de caractères.',
            'boutique_id.required' => 'Le boutique_id est requis.',
            'boutique_id.string' => 'Le boutique_id doit être une chaîne de caractères.',
            'plats.required' => 'Les plats sont requis.',
            'plats.array' => 'Les plats doivent être sous forme de tableau.',
            'plats.min' => 'La commande doit contenir au moins un plat.',
            'total.required' => 'Le total de la commande est requis.',
            'total.numeric' => 'Le total de la commande doit être un nombre.',
            'status.required' => 'Le statut de la commande est requis.',
            'status.string' => 'Le statut doit être une chaîne de caractères.',
            'status.in' => 'Le statut de la commande doit être l\'un des suivants : en_attente, en_cours, terminee, annulee.',
        ]);

        if ($data->fails()) {
            return response()->json([
                'errors' => $data->errors()->first(),
            ], 400);
        }

        $validatedData = $data->validated();

        $boutiqueData = $this->database->getReference("users/{$validatedData['boutique_id']}")->getValue();
        if (!$boutiqueData) {
            return response()->json([
                'error' => 'La boutique choisie n\'existe pas dans notre base de données.'
            ], 400);
        }

        foreach ($validatedData['plats'] as $plat) {
            $platData = $this->database->getReference("plats/{$plat['uid']}")->getValue();
            if (!$platData) {
                return response()->json([
                    'error' => "Le plat avec l'ID {$plat['uid']} n'existe pas."
                ], 400);
            }
        }

        $commandeRef = $this->database->getReference('commandes')->push($validatedData);

        $boutiqueToken = $this->database->getReference("users/{$validatedData['boutique_id']}/fcm_token")->getValue();

        if ($boutiqueToken) {
            $result = $this->notificationService->sendNotification(
                $boutiqueToken,
                'Nouvelle commande reçue',
                'Vous avez reçu une nouvelle commande. Consultez-la maintenant !'
            );

            if ($result !== true) {
                return response()->json([
                    'message' => 'Commande créée, mais la notification n\'a pas pu être envoyée.',
                    'error' => $result,
                    'data' => $commandeRef->getValue(),
                ], 201);
            }
        }

        return response()->json([
            'message' => 'Commande créée avec succès.',
            'data' => $commandeRef->getValue(),
        ], 201);
    }



    public function index()
    {
        $commandes = $this->database->getReference('commandes')->getValue();
        return response()->json($commandes);
    }

    public function show($id)
    {
        $commande = $this->database->getReference('commandes/' . $id)->getValue();
        return response()->json($commande);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:en_attente,en_cours,terminee,annulee',
            'client_id' => 'required|string',
        ], [
            'client_id.required'=> 'Utilisateur non connecté',
            'status.required' => 'Le statut est requis.',
            'status.string' => 'Le statut doit être une chaîne de caractères.',
            'status.in' => 'Le statut doit être l\'un des suivants : en_attente, en_cours, terminee ou annulee.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], 400);
        }

        $validatedData = $validator->validated();

        $commandeRef = $this->database->getReference('commandes/' . $id)->update($validatedData);

        $clientToken  = $this->database->getReference("users/{$validatedData['client_id']}/fcm_token")->getValue();

        if ($clientToken ) {
            $result = $this->notificationService->sendNotification(
                $clientToken ,
                'Annulation',
                'Commande annulée !'
            );

            if ($result !== true) {
                return response()->json([
                    'message' => 'Commande annulée, mais la notification n\'a pas pu être envoyée.',
                    'error' => $result,
                    'data' => $commandeRef->getValue(),
                ]);
            }
        }

        return response()->json([
            'message' => 'Commande mise à jour avec succès.',
        ]);
    }

}
