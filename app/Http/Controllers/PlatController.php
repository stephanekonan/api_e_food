<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\Validator;

class PlatController extends Controller
{
    protected $database;

    public function __construct()
    {
        $this->database = Firebase::database();
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom_plat' => 'required|string',
            'prix' => 'required',
            'description' => 'nullable|string',
            'image' => 'required|mimes:jpg,jpeg,png|max:15360',
        ], [
            'nom_plat.required' => 'Le nom du plat est requis.',
            'nom_plat.string' => 'Le nom du plat doit être une chaîne de caractères.',
            'prix.required' => 'Le prix est requis.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'image.required' => 'L\'image doit être téléchargée.',
            'image.mimes' => 'L\'image doit être un fichier valide (jpg, jpeg, png, gif, bmp).',
            'image.max' => 'L\'image ne doit pas dépasser 15 Mo.',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 400);
        }
    
        $data = $validator->validated();
        $data['boutique_id'] = $request->boutique_id;
    
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $directory = 'uploads/plats';
            $file->move(public_path($directory), $filename);
            $data['image_url'] = asset($directory . '/' . $filename);
        }
    
        $platRef = $this->database->getReference('plats/')->push($data);

        $data['uid'] = $platRef->getKey();
        $platRef->set($data);
    
        return response()->json([
            'message' => 'Plat créé',
            'plat_id' => $platRef->getKey()
        ]);
    }

    public function index()
    {
        $plats = $this->database->getReference('plats')->getValue();

        return response()->json($plats);
    }

    public function specialities() {
        
        $specialities = $this->database->getReference('specialities')->getValue();

        return response()->json($specialities);
    }

    public function update(Request $request, $platId)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string',
            'prix' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'nullable|mimes:jpg,jpeg,png,gif,bmp|max:15360',
        ], [
            'nom.required' => 'Le nom du plat est requis.',
            'nom.string' => 'Le nom du plat doit être une chaîne de caractères.',
            'prix.required' => 'Le prix est requis.',
            'prix.numeric' => 'Le prix doit être un nombre.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'image.mimes' => 'L\'image doit être un fichier valide (jpg, jpeg, png, gif, bmp).',
            'image.max' => 'L\'image ne doit pas dépasser 15 Mo.',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
    
        $data = $validator->validated();
    
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $directory = 'uploads/plats';
            $file->move(public_path($directory), $filename);
            $data['image'] = asset($directory . '/' . $filename);
        }
    
        $this->database->getReference('plats/' . $platId)->update($data);
    
        return response()->json(['message' => 'Plat mis à jour']);
    }

    public function destroy($platId)
    {
        $this->database->getReference('plats/' . $platId)->remove();

        return response()->json(['message' => 'Plat supprimé']);
    }

}
