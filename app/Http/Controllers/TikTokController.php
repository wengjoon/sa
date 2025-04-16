<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TikTokController extends Controller
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = '4388edfedemsh1289c398bba84a2p133c6ajsn23ac51a4963b';
        $this->baseUrl = 'https://tiktok-scraper7.p.rapidapi.com';
    }

    public function index()
    {
        return view('home');
    }

    public function search(Request $request)
    {
        $username = $request->input('username');
        
        if (empty($username)) {
            return redirect()->route('home')->with('error', 'Please enter a username');
        }

        // Clean the username (remove @ if present)
        $username = ltrim($username, '@');

        return redirect()->route('user.profile', ['username' => $username]);
    }

    public function userProfile($username)
    {
        try {
            // Fetch user info
            $userInfo = $this->getUserInfo($username);
            
            if (!$userInfo || isset($userInfo['code']) && $userInfo['code'] !== 0) {
                $errorMessage = isset($userInfo['msg']) ? $userInfo['msg'] : 'User not found or API error occurred';
                return redirect()->route('home')->with('error', $errorMessage);
            }

            // Fetch user posts
            $userPosts = $this->getUserPosts($username);
            
            if (!$userPosts || isset($userPosts['code']) && $userPosts['code'] !== 0) {
                $errorMessage = isset($userPosts['msg']) ? $userPosts['msg'] : 'Could not fetch user videos';
                Log::warning('Failed to fetch videos for user: ' . $username, ['response' => $userPosts]);
                
                // Still show profile but with empty videos
                return view('profile', [
                    'user' => $userInfo['data']['user'],
                    'stats' => $userInfo['data']['stats'],
                    'videos' => [],
                    'cursor' => null,
                    'hasMore' => false,
                    'username' => $username,
                    'error' => $errorMessage
                ]);
            }
            
            return view('profile', [
                'user' => $userInfo['data']['user'],
                'stats' => $userInfo['data']['stats'],
                'videos' => $userPosts['data']['videos'] ?? [],
                'cursor' => $userPosts['data']['cursor'] ?? null,
                'hasMore' => $userPosts['data']['hasMore'] ?? false,
                'username' => $username
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching user profile: ' . $e->getMessage(), [
                'username' => $username,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('home')->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    public function loadMorePosts(Request $request)
    {
        try {
            $username = $request->input('username');
            $cursor = $request->input('cursor');
            
            $userPosts = $this->getUserPosts($username, $cursor);
            
            if (!$userPosts || isset($userPosts['code']) && $userPosts['code'] !== 0) {
                return response()->json([
                    'error' => isset($userPosts['msg']) ? $userPosts['msg'] : 'Failed to load more videos',
                    'videos' => [],
                    'cursor' => null,
                    'hasMore' => false
                ], 200);
            }
            
            return response()->json([
                'videos' => $userPosts['data']['videos'] ?? [],
                'cursor' => $userPosts['data']['cursor'] ?? null,
                'hasMore' => $userPosts['data']['hasMore'] ?? false
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading more posts: ' . $e->getMessage(), [
                'username' => $request->input('username'),
                'cursor' => $request->input('cursor'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error loading more videos: ' . $e->getMessage(),
                'videos' => [],
                'cursor' => null,
                'hasMore' => false
            ], 200);
        }
    }

    protected function getUserInfo($username)
    {
        try {
            $response = Http::timeout(15)->withHeaders([
                'X-RapidAPI-Key' => $this->apiKey,
                'X-RapidAPI-Host' => 'tiktok-scraper7.p.rapidapi.com'
            ])->get($this->baseUrl . '/user/info', [
                'unique_id' => $username
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::warning('Failed API call to get user info', [
                'username' => $username,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return ['code' => -1, 'msg' => 'API request failed: ' . $response->status()];
        } catch (\Exception $e) {
            Log::error('Exception when getting user info: ' . $e->getMessage(), [
                'username' => $username,
                'trace' => $e->getTraceAsString()
            ]);
            
            return ['code' => -1, 'msg' => 'Exception: ' . $e->getMessage()];
        }
    }

    protected function getUserPosts($username, $cursor = 0)
    {
        try {
            $response = Http::timeout(15)->withHeaders([
                'X-RapidAPI-Key' => $this->apiKey,
                'X-RapidAPI-Host' => 'tiktok-scraper7.p.rapidapi.com'
            ])->get($this->baseUrl . '/user/posts', [
                'unique_id' => $username,
                'count' => 10,
                'cursor' => $cursor
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::warning('Failed API call to get user posts', [
                'username' => $username,
                'cursor' => $cursor,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return ['code' => -1, 'msg' => 'API request failed: ' . $response->status()];
        } catch (\Exception $e) {
            Log::error('Exception when getting user posts: ' . $e->getMessage(), [
                'username' => $username,
                'cursor' => $cursor,
                'trace' => $e->getTraceAsString()
            ]);
            
            return ['code' => -1, 'msg' => 'Exception: ' . $e->getMessage()];
        }
    }
} 