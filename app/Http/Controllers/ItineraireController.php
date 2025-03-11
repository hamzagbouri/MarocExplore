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


    public function aavisiter(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request->validate([
                'id' => 'required|exists:itineraires,id'

            ]);
            $user->itinerairesAVister()->attach($request->id);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
        return response()->json(['message' => 'itineraires avisiter created']);
    }
    public function visiter($id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $itineraire = Itineraire::findOrFail($id);

            $user->itinerairesAVister()->attach($id);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
        return response()->json(['message' => 'itineraires avisiter created']);
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
    public function update(Request $request, Itineraire $itineraire)
    {
        //
    }

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
