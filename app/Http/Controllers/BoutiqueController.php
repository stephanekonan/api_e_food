<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Kreait\Laravel\Firebase\Facades\Firebase;
class BoutiqueController extends Controller
{
    protected $database;

    public function __construct()
    {
        $this->database = Firebase::database();
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [
            'nom_boutique' => 'required|string',
            'specialite' => 'required|string',
            'location' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'phone' => 'required|string',
            'image' => 'required|mimes:jpg,jpeg,png,gif,bmp|max:51200', // Max 50MB pour l'image
        ], [
            'nom_boutique.required' => 'Le nom de la boutique est requis.',
            'nom_boutique.string' => 'Le nom de la boutique doit être une chaîne de caractères.',
            
            'specialite.required' => 'La spécialité est requise.',
            'specialite.string' => 'La spécialité doit être une chaîne de caractères.',
            
            'location.required' => 'La localisation est requise.',
            'location.string' => 'La localisation doit être une chaîne de caractères.',
            
            'latitude.required' => 'La latitude est requise.',
            'latitude.numeric' => 'La latitude doit être un nombre.',
            
            'longitude.required' => 'La longitude est requise.',
            'longitude.numeric' => 'La longitude doit être un nombre.',
            
            'phone.required' => 'Le numéro de téléphone est requis.',
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            
            'image.required' => 'L\'image est requise.',
            'image.mimes' => 'L\'image doit être un fichier valide (jpg, jpeg, png, gif, bmp).',
            'image.max' => 'L\'image ne doit pas dépasser 50MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        $file = $request->file('image');
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $directory = 'uploads/boutiques';

        $file->move(public_path($directory), $filename);

        $imageUrl = asset($directory . '/' . $filename);

        $data = $validator->validated();
        $data['image_url'] = $imageUrl;

        $boutiqueRef = $this->database->getReference('boutiques')->push($data);

        return response()->json([
            'message' => 'Boutique créée',
            'data' => $boutiqueRef->getValue()
        ]);
    }

    public function index()
    {
        $boutiques = $this->database->getReference('boutiques')->getValue();
        return response()->json([
            'message'=> 'Liste de boutiques',
            'data'=> $boutiques
        ], 200);
    }

    public function show($id)
    {
        $boutique = $this->database->getReference('boutiques/' . $id)->getValue();
        return response()->json($boutique);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),
         [
            'nom_boutique' => 'required|string',
            'specialite' => 'required|string',
            'location' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'phone' => 'required|string',
            'image' => 'nullable|mimes:jpg,jpeg,png,gif,bmp|max:51200', // Image facultative pour la mise à jour
        ], [
            'nom_boutique.required' => 'Le nom de la boutique est requis.',
            'nom_boutique.string' => 'Le nom de la boutique doit être une chaîne de caractères.',
            
            'specialite.required' => 'La spécialité est requise.',
            'specialite.string' => 'La spécialité doit être une chaîne de caractères.',
            
            'location.required' => 'La localisation est requise.',
            'location.string' => 'La localisation doit être une chaîne de caractères.',
            
            'latitude.required' => 'La latitude est requise.',
            'latitude.numeric' => 'La latitude doit être un nombre.',
            
            'longitude.required' => 'La longitude est requise.',
            'longitude.numeric' => 'La longitude doit être un nombre.',
            
            'phone.required' => 'Le numéro de téléphone est requis.',
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            
            'image.mimes' => 'L\'image doit être un fichier valide (jpg, jpeg, png, gif, bmp).',
            'image.max' => 'L\'image ne doit pas dépasser 50MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }
        $data = $validator->validated();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $directory = 'uploads/boutiques';

            $file->move(public_path($directory), $filename);
            $imageUrl = asset($directory . '/' . $filename);
            $data['image_url'] = $imageUrl;
        }

        $boutiqueRef = $this->database->getReference('boutiques/' . $id);

        if (!$boutiqueRef->getSnapshot()->exists()) {
            return response()->json([
                'message' => 'La boutique n\'existe pas.'
            ], 404);
        }

        $boutiqueRef->set($data);

        return response()->json([
            'message' => 'Boutique mise à jour avec succès',
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $this->database->getReference('boutiques/' . $id)->remove();
        return response()->json(['message' => 'Boutique supprimée']);
    }
}
