<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Itineraire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Exception\Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class ItineraireController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Get(
     *     path="/itineraires",
     *     summary="Get all itineraries",
     *     tags={"Itineraires"},
     *     @OA\Response(
     *         response=200,
     *         description="List of itineraries",
     *         @OA\JsonContent(
     *             @OA\Property(property="itineraires", type="array", @OA\Items(
     *                 @OA\Property(property="itineraire_id", type="integer", example=1),
     *                 @OA\Property(property="titre", type="string", example="Adventure in the Alps"),
     *                 @OA\Property(property="duree", type="string", example="5 days"),
     *                 @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
     *                 @OA\Property(property="user_name", type="string", example="John Doe"),
     *                 @OA\Property(property="category_name", type="string", example="Nature"),
     *                 @OA\Property(property="destinations", type="array", @OA\Items(
     *                     @OA\Property(property="logement", type="string", example="Hotel XYZ"),
     *                     @OA\Property(property="nom", type="string", example="Paris"),
     *                     @OA\Property(property="activites", type="string", example="Hiking"),
     *                     @OA\Property(property="plats", type="string", example="French Cuisine")
     *                 ))
     *             ))
     *         )
     *     )
     * )
     */

    public function index()
    {
        try {
            // Retrieve all itineraries with their related categories, users, and destinations
            $itineraires = DB::table('itineraires')
                ->join('users', 'itineraires.user_id', '=', 'users.id')
                ->join('categories', 'itineraires.categorie_id', '=', 'categories.id')
                ->join('itineraire_destination', 'itineraire_destination.itineraire_id', '=', 'itineraires.id')
                ->join('destinations', 'itineraire_destination.destination_id', '=', 'destinations.id')
                ->select(
                    'itineraires.id as itineraire_id',
                    'itineraires.titre',
                    'itineraires.duree',
                    'itineraires.image',
                    'users.name as user_name',
                    'categories.titre as category_name',
                    'destinations.id as destination_id',
                    'destinations.logement',
                    'destinations.nom',
                    'destinations.activites',
                    'destinations.plats'
                )
                ->get();

            // Group the results by itineraire_id and map destinations under each itinerary
            $groupedItineraires = $itineraires->groupBy('itineraire_id')->map(function ($group) {
                $itinerary = $group->first();  // Get the first item (itinerary)
                $itinerary->destinations = $group->map(function ($item) {
                    return [
                        'logement' => $item->logement,
                        'nom' => $item->nom,
                        'activites' => $item->activites,
                        'plats' => $item->plats
                    ];
                });
                unset($itinerary->destination_id); // Remove redundant destination_id field
                return $itinerary;
            });

            return response()->json(['itineraires' => $groupedItineraires]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
    public function itinerairesByUser()
    {
        try
        {
            $user = JWTAuth::parseToken()->authenticate();
            $itineraires = Itineraire::with('destinations','categorie','user')
                ->where('user_id',$user->id)
                ->get();

        } catch (Exception $e)
        {
            return response()->json(['error' => $e->getMessage()]);
        }
        return response()->json(['itineraires' => $itineraires]);



    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *     path="/itineraires/add",
     *     summary="Create a new itinerary",
     *     tags={"Itineraires"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titre", "duree", "image", "categorie_id", "destinations"},
     *             @OA\Property(property="titre", type="string", example="Adventure in the Alps"),
     *             @OA\Property(property="duree", type="string", example="5 days"),
     *             @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
     *             @OA\Property(property="categorie_id", type="integer", example=2),
     *             @OA\Property(property="destinations", type="array", @OA\Items(
     *                 @OA\Property(property="logement", type="string", example="Hotel XYZ"),
     *                 @OA\Property(property="nom", type="string", example="Paris"),
     *                 @OA\Property(property="activite", type="string", example="Hiking"),
     *                 @OA\Property(property="plats", type="string", example="French Cuisine")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Itinerary created successfully"),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function create(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        try {
            $request->validate([
                'titre' => 'required|string',
                'duree' => 'required|string',
                'image' => 'required|string',
                'categorie_id' => 'required|exists:categories,id',
                'destinations' => ['required', 'array', 'min:2'],
                'destinations.*.id' => 'nullable|exists:destinations,id',
                'destinations.*.logement' => 'required_without:destinations.*.id|string',
                'destinations.*.nom' => 'required_without:destinations.*.id|string',
                'destinations.*.activite' => 'required_without:destinations.*.id|string',
                'destinations.*.plats' => 'required_without:destinations.*.id|string',
            ]);

            $itineraire = Itineraire::create([
                'titre' => $request->titre,
                'duree' => $request->duree,
                'image' => $request->image,
                'categorie_id' => $request->categorie_id,
                'user_id' => $user->id,
            ]);

            foreach ($request->destinations as $destination) {
                if (isset($destination['id'])) {
                    $itineraire->destinations()->attach($destination['id']);
                } else {
                    $dest = Destination::create([
                        'logement' => $destination['logement'],
                        'nom' => $destination['nom'],
                        'activites' => $destination['activite'],
                        'plats' => $destination['plats'],
                    ]);
                    $itineraire->destinations()->attach($dest->id);
                }
            }

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'Itinéraire created', 'itineraire' => $itineraire]);
    }
    /**
     * @OA\Put(
     *     path="/itineraire/update/{id}",
     *     summary="Mettre à jour un itinéraire",
     *     tags={"Itinéraires"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titre", "duree", "image", "categorie_id", "destinations"},
     *             @OA\Property(property="titre", type="string", example="Itinéraire 1"),
     *             @OA\Property(property="duree", type="string", example="7 jours"),
     *             @OA\Property(property="image", type="string", example="image_url"),
     *             @OA\Property(property="categorie_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="destinations",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"logement", "nom", "activite", "plats"},
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="logement", type="string", example="Hôtel A"),
     *                     @OA\Property(property="nom", type="string", example="Paris"),
     *                     @OA\Property(property="activite", type="string", example="Visite de la Tour Eiffel"),
     *                     @OA\Property(property="plats", type="string", example="Plat local A")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Itinéraire mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Itinéraire updated"),
     *             @OA\Property(property="itineraire", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=404, description="Itinéraire non trouvé"),
     *     @OA\Response(response=403, description="Non autorisé")
     * )
     */

    public function update(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        try {
            // Validate the request data
            $request->validate([
                'titre' => 'required|string',
                'duree' => 'required|string',
                'image' => 'required|string',
                'categorie_id' => 'required|exists:categories,id',
                'destinations' => ['required', 'array', 'min:2'],
                'destinations.*.id' => 'nullable|exists:destinations,id',
                'destinations.*.logement' => 'required_without:destinations.*.id|string',
                'destinations.*.nom' => 'required_without:destinations.*.id|string',
                'destinations.*.activite' => 'required_without:destinations.*.id|string',
                'destinations.*.plats' => 'required_without:destinations.*.id|string',
            ]);

            // Find the itinerary by ID
            $itineraire = Itineraire::findOrFail($id);

            // Check if the user is authorized to update this itinerary (optional)
            if ($itineraire->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized to update this itinerary'], 403);
            }

            // Update the itinerary
            $itineraire->update([
                'titre' => $request->titre,
                'duree' => $request->duree,
                'image' => $request->image,
                'categorie_id' => $request->categorie_id,
            ]);
            $itineraire->destinations()->detach();
            foreach ($request->destinations as $destination) {
                if (isset($destination['id'])) {
                    $itineraire->destinations()->attach($destination['id']);
                } else {
                    $dest = Destination::create([
                        'logement' => $destination['logement'],
                        'nom' => $destination['nom'],
                        'activites' => $destination['activite'],
                        'plats' => $destination['plats'],
                    ]);
                    $itineraire->destinations()->attach($dest->id);
                }
            }

            // Update destinations

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'Itinéraire updated', 'itineraire' => $itineraire]);
    }

    /**
     * @OA\Post(
     *     path="/itineraires/{id}/visiter",
     *     summary="Attach an itinerary to user's 'to visit' list",
     *     description="Attaches an itinerary to the authenticated user's 'to visit' list.",
     *     operationId="attachItinerary",
     *     tags={"Itineraire"},
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the itinerary to attach",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Itinerary attached successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="itineraires avisiter created")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid data")
     *         )
     *     )
     * )
     */

    public function aavisiter(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request->validate([
                'id' => 'required|exists:itineraires,id'

            ]);
//            $user->itinerairesAVister()->attach($request->id);
            DB::table('avisiter')->insert([
                'user_id' => $user->id,
                'itineraire_id' => $request->id,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
        return response()->json(['message' => 'itineraires avisiter created']);
    }
    /**
     * @OA\Get(
     *     path="/itineraires/avisiter",
     *     summary="Get itineraries to visit for authenticated user",
     *     description="Returns all itineraries marked as 'to visit' by the authenticated user, with related destinations and categories.",
     *     operationId="getUserItinerariesToVisit",
     *     tags={"Itineraire"},
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of itineraries to visit",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="itineraires",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="titre", type="string"),
     *                     @OA\Property(
     *                         property="destinations",
     *                         type="array",
     *                         @OA\Items(
     *                  @OA\Property(property="logement", type="string", example="Hotel XYZ"),
     *                  @OA\Property(property="nom", type="string", example="Paris"),
     *                  @OA\Property(property="activite", type="string", example="Hiking"),
     *                  @OA\Property(property="plats", type="string", example="French Cuisine")
     *              )
     *                     ),
     *                     @OA\Property(property="categorie", type="object"),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */


    public function avisiterUser()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $itineraires = $user->itinerairesAVister()->with('destinations','categorie')->get();
        return response()->json(['itineraires' => $itineraires]);
    }
    /**
     * @OA\Get(
     *     path="/itineraires/search/{search}",
     *     summary="Search itineraries by title",
     *     description="Search itineraries by title, with case-insensitive matching using ILIKE.",
     *     operationId="searchItineraries",
     *     tags={"Itineraire"},
     *     @OA\Parameter(
     *         name="search",
     *         in="path",
     *         description="The search term for itinerary title",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="itineraires",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="titre", type="string"),
     *                     @OA\Property(property="destinations", type="array",@OA\Items(
     *                  @OA\Property(property="logement", type="string", example="Hotel XYZ"),
     *                  @OA\Property(property="nom", type="string", example="Paris"),
     *                  @OA\Property(property="activite", type="string", example="Hiking"),
     *                  @OA\Property(property="plats", type="string", example="French Cuisine")
     *              )),
     *                     @OA\Property(property="categorie", type="object"),
     *                     @OA\Property(property="user", type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid search term")
     *         )
     *     )
     * )
     */

    public function searchIitneraire( $search)
    {
        $itineraires = Itineraire::with('destinations','categorie','user')->where('titre','ILIKE','%'.$search.'%')->get();
        return response()->json(['itineraires' => $itineraires]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }
    /**
     * @OA\Get(
     *     path="/itineraires/top",
     *     summary="Get top 10 popular itineraries",
     *     description="Returns the top 10 itineraries based on the number of favorites (favoris).",
     *     operationId="getTopItineraries",
     *     tags={"Itineraire"},
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of top 10 itineraries",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="itineraires",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="titre", type="string"),
     *                     @OA\Property(property="favoris_count", type="integer")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function topItineraire()
    {
        $itinerairePopulaires = DB::table('avisiter')
            ->join('itineraires', 'avisiter.itineraire_id', '=', 'itineraires.id')
            ->select('itineraires.id', 'itineraires.titre', DB::raw('COUNT(avisiter.itineraire_id) as favoris_count'))
            ->groupBy('itineraires.id', 'itineraires.titre')
            ->orderByDesc('favoris_count')
            ->limit(10) // Récupérer les 10 itinéraires les plus populaires
            ->get();
        return response()->json(['itineraires' => $itinerairePopulaires]);
    }
    /**
     * @OA\Get(
     *     path="/itineraires/categorie/count",
     *     summary="Get the count of itineraries by category",
     *     description="Returns the count of itineraries for each category.",
     *     operationId="getItineraryCountByCategory",
     *     tags={"Itineraire"},
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories with itinerary count",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="itineraires",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="titre", type="string"),
     *                     @OA\Property(property="itineraire_count", type="integer")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function CountItineraireByCategorie()
    {
        $categorieCount = DB::table('categories')
            ->join('itineraires', 'categories.id', '=', 'itineraires.categorie_id')
            ->select('categories.id', 'categories.titre', DB::raw('COUNT(itineraires.id) as itineraire_count'))
            ->groupBy('categories.id', 'categories.titre')
            ->get();
        return response()->json(['itineraires' => $categorieCount]);

    }
    /**
     * @OA\Get(
     *     path="/itineraires/categorie/{id}",
     *     summary="Get itineraries filtered by category",
     *     description="Returns itineraries filtered by category with their destinations.",
     *     operationId="getItinerariesByCategory",
     *     tags={"Itineraire"},
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the category to filter itineraries by",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of itineraries filtered by category with destinations",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="itineraires",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="itineraire_id", type="integer"),
     *                     @OA\Property(property="titre", type="string"),
     *                     @OA\Property(property="duree", type="string"),
     *                     @OA\Property(property="image", type="string"),
     *                     @OA\Property(property="user_name", type="string"),
     *                     @OA\Property(property="category_name", type="string"),
     *                     @OA\Property(
     *                         property="destinations",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="logement", type="string"),
     *                             @OA\Property(property="nom", type="string"),
     *                             @OA\Property(property="activites", type="string"),
     *                             @OA\Property(property="plats", type="string")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function filterParCategorie($id)
    {
        $itineraires = DB::table('itineraires')
            ->join('users', 'itineraires.user_id', '=', 'users.id')
            ->join('categories', 'itineraires.categorie_id', '=', 'categories.id')
            ->join('itineraire_destination', 'itineraire_destination.itineraire_id', '=', 'itineraires.id')
            ->join('destinations', 'itineraire_destination.destination_id', '=', 'destinations.id')
            ->select(
                'itineraires.id as itineraire_id',
                'itineraires.titre',
                'itineraires.duree',
                'itineraires.image',
                'users.name as user_name',
                'categories.titre as category_name',
                'destinations.id as destination_id',
                'destinations.logement',
                'destinations.nom',
                'destinations.activites',
                'destinations.plats'
            )->where('itineraires.categorie_id',$id)
            ->get();

        $groupedItineraires = $itineraires->groupBy('itineraire_id')->map(function ($group) {
            $itinerary = $group->first();  // Get the first item (itinerary)
            $itinerary->destinations = $group->map(function ($item) {
                return [
                    'logement' => $item->logement,
                    'nom' => $item->nom,
                    'activites' => $item->activites,
                    'plats' => $item->plats
                ];
            });
            unset($itinerary->destination_id); // Remove redundant destination_id field
            return $itinerary;
        });

        return response()->json(['itineraires' => $groupedItineraires]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Itineraire  $itineraire
     * @return \Illuminate\Http\Response
     */
    public function show(Itineraire $itineraire)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Itineraire  $itineraire
     * @return \Illuminate\Http\Response
     */
    public function edit(Itineraire $itineraire)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Itineraire  $itineraire
     * @return \Illuminate\Http\Response
     */


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Itineraire  $itineraire
     * @return \Illuminate\Http\Response
     */
    public function destroy(Itineraire $itineraire)
    {
        //
    }
}
