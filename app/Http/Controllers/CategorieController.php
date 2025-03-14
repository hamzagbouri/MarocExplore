<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="API Endpoints for managing categories"
 * )
 */
class CategorieController extends Controller
{
    /**
     * @OA\Get(
     *     path="/categories",
     *     summary="Get list of all categories",
     *     tags={"Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="categories", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="titre", type="string", example="Technology"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $categories = Categorie::all();
        return response()->json(['categories' => $categories]);
    }

    /**
     * @OA\Post(
     *     path="/categories",
     *     summary="Create a new category",
     *     tags={"Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titre"},
     *             @OA\Property(property="titre", type="string", example="Science")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categorie Created Successfully"),
     *             @OA\Property(property="categorie", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="titre", type="string", example="Science"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function create(Request $request)
    {
        $request->validate([
            'titre' => 'required|string',
        ]);

        $categorie = Categorie::create([
            'titre'=> $request->titre
        ]);

        return response()->json(['message'=> 'Categorie Created Successfully', 'categorie' => $categorie], 201);
    }

    /**
     * @OA\Get(
     *     path="/categories/{id}",
     *     summary="Get a specific category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="titre", type="string", example="Science"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function show(Categorie $categorie)
    {
        return response()->json($categorie);
    }

    /**
     * @OA\Put(
     *     path="/categories/{id}",
     *     summary="Update a category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titre"},
     *             @OA\Property(property="titre", type="string", example="Updated Category Name")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Category updated successfully"),
     *     @OA\Response(response=404, description="Category not found"),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function update(Request $request, Categorie $categorie)
    {
        $request->validate([
            'titre' => 'required|string',
        ]);

        $categorie->update([
            'titre' => $request->titre
        ]);

        return response()->json(['message' => 'Category updated successfully', 'categorie' => $categorie]);
    }

    /**
     * @OA\Delete(
     *     path="/categories/{id}",
     *     summary="Delete a category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Category deleted successfully"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function destroy(Categorie $categorie)
    {
        $categorie->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
