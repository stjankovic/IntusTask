<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Url;
use Illuminate\Support\Str;
use GuzzleHttp\Client; 
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse; // Correct namespace

class UrlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Store method called');

        $validated = $request->validate([
            'original_url' => 'required|url'
        ]);

        Log::info('Validation passed', $validated);

        $originalUrl = $request->input('original_url');
        Log::info('Original URL:', ['url' => $originalUrl]);
        
        // Check if the URL has already been shortened
        $existingUrl = Url::where('original_url', $originalUrl)->first();
        if ($existingUrl) {
            Log::info('URL already shortened', ['url' => $originalUrl]);
            return response()->json(['shortened_url' => url($existingUrl->hash)]);
        }

        // Check the URL with Google Safe Browsing API
        if (!$this->isUrlSafe($originalUrl)) {
            Log::warning('URL is not safe according to Google Safe Browsing API', ['url' => $originalUrl]);
            return response()->json(['error' => 'URL is not safe according to Google Safe Browsing API'], 422);
        }

        // Generate a unique hash
        $hash = $this->generateUniqueHash();
        Log::info('Generated hash', ['hash' => $hash]);

        // Save to database
        $url = new Url([
            'original_url' => $originalUrl,
            'hash' => $hash
        ]);
        $url->save();
        Log::info('URL saved', ['hash' => $hash, 'original_url' => $originalUrl]);
        
        return response()->json(['shortened_url' => url($hash)]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Url $url)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Url $url)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Url $url)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Url $url)
    {
        //
    }

    
    protected function isUrlSafe($url)
    {
        $client = new Client();
        $apiKey = env('GOOGLE_SAFE_BROWSING_KEY');
        $endpoint = 'https://safebrowsing.googleapis.com/v4/threatMatches:find';
        $params = [
            'client' => [
                'clientId'      => '549256784957',
                'clientVersion' => '1.0.0'
            ],
            'threatInfo' => [
                'threatTypes'      => ['MALWARE', 'SOCIAL_ENGINEERING'],
                'platformTypes'    => ['ANY_PLATFORM'],
                'threatEntryTypes' => ['URL'],
                'threatEntries'    => [
                    ['url' => $url]
                ]
            ]
        ];
    
        $results = []; // Define $results outside of the try block to ensure it's always set
    
        try {
            $response = $client->request('POST', $endpoint, [
                'json' => $params,
                'query' => ['key' => $apiKey]
            ]);
    
            $results = json_decode($response->getBody(), true);
            Log::info('URL safety check response', ['results' => $results]);
    
        } catch (\Exception $e) {
            Log::error('Error checking URL safety', ['url' => $url, 'error' => $e->getMessage()]);
            return false;
        }
        return empty($results['matches']);
    }
    

    protected function generateUniqueHash()
    {
        do {
            $hash = Str::random(6);
            $existingUrl = Url::where('hash', $hash)->first();
        } while ($existingUrl);

        return $hash;
    }
    public function redirect($hash): RedirectResponse
    {
        $url = Url::where('hash', $hash)->first();

        if ($url) {
            // Redirect to the original URL
            return redirect()->to($url->original_url);
        }

        // If the URL is not found, redirect to a default page or show an error
        return redirect()->to('/')->with('error', 'URL not found.');
    }
}
