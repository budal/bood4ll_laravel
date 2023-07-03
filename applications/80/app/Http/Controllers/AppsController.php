<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AppsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $content = [
        'title' => "Apps",
        'subtitle' => "Select the app you want to use",
        'description' => "Applications will be available according to your authorizations, which you can change using the teams selector located in the upper right corner of the screen.",

        'content' => [
            [
                "logo" => '<svg viewBox="0 -64 640 640" xmlns="http://www.w3.org/2000/svg"><path d="M96 224c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm448 0c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm32 32h-64c-17.6 0-33.5 7.1-45.1 18.6 40.3 22.1 68.9 62 75.1 109.4h66c17.7 0 32-14.3 32-32v-32c0-35.3-28.7-64-64-64zm-256 0c61.9 0 112-50.1 112-112S381.9 32 320 32 208 82.1 208 144s50.1 112 112 112zm76.8 32h-8.3c-20.8 10-43.9 16-68.5 16s-47.6-6-68.5-16h-8.3C179.6 288 128 339.6 128 403.2V432c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48v-28.8c0-63.6-51.6-115.2-115.2-115.2zm-223.7-13.4C161.5 263.1 145.6 256 128 256H64c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h65.9c6.3-47.4 34.9-87.3 75.2-109.4z"/></svg>',
                "route" => "users.index",
                "title" => "Users",
                "description" => "Manage users' data registered in the system."
            ],
            [
                "logo" => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 511.842 511.842"><path d="M405.145,106.509h-23.953c-22.156-85.961-173.586-138.302-240.398-85C85.843,25.823,42.59,71.783,42.59,127.842 c0,24.015,7.939,46.175,21.333,64.004v63.996c0,9.182,5.876,17.335,14.587,20.239l22.619,7.54l-27.707,18.471 c-12.666,8.444-12.666,27.057,0,35.501l37.374,24.916l-37.374,24.916c-5.935,3.957-9.5,10.618-9.5,17.75v42.667 c0,7.133,3.565,13.794,9.5,17.75l64,42.667c7.166,4.777,16.501,4.777,23.667,0l64-42.667c5.935-3.957,9.5-10.618,9.5-17.75 V191.846c13.394-17.829,21.333-39.989,21.333-64.004c0-35.016-16.876-66.092-42.938-85.541 c46.38,0.809,105.273,28.358,122.985,64.207h-15.945c-35.398,0-64.107,28.709-64.107,64.107v170.453 c0,35.398,28.709,64.107,64.107,64.107h85.12c35.398,0,64.107-28.709,64.107-64.107V170.615 C469.252,135.217,440.544,106.509,405.145,106.509z M149.256,464.869l-42.667-28.444v-19.832l54.5-36.333 c12.666-8.444,12.666-27.057,0-35.501l-37.374-24.916l37.374-24.916c14.771-9.847,11.754-32.375-5.087-37.989l-49.413-16.471 v-14.842c13.067,5.71,27.495,8.885,42.667,8.885s29.6-3.175,42.667-8.885v210.801L149.256,464.869z M149.256,191.842 c-35.343,0-64-28.657-64-64s28.657-64,64-64s64,28.657,64,64S184.6,191.842,149.256,191.842z M426.585,341.069 c0,11.834-9.606,21.44-21.44,21.44h-85.12c-11.834,0-21.44-9.606-21.44-21.44V170.615c0-11.834,9.606-21.44,21.44-21.44h85.12 c11.834,0,21.44,9.606,21.44,21.44V341.069z"/><path d="M149.256,85.175c-11.776,0-21.333,9.557-21.333,21.333s9.557,21.333,21.333,21.333s21.333-9.557,21.333-21.333S161.032,85.175,149.256,85.175z"/><path d="M362.59,170.509c-11.776,0-21.333,9.557-21.333,21.333s9.557,21.333,21.333,21.333s21.333-9.557,21.333-21.333S374.366,170.509,362.59,170.509z"/><path d="M362.59,234.509c-11.782,0-21.333,9.551-21.333,21.333v64c0,11.782,9.551,21.333,21.333,21.333c11.782,0,21.333-9.551,21.333-21.333v-64C383.923,244.06,374.372,234.509,362.59,234.509z"/></svg>',
                "route" => "authorization.index",
                "title" => "Authorization",
                "description" => "Manage app authorizations and permissions assignable to users."
            ],
            [
                "logo" => '<svg viewBox="0 0 512 512"><path d="M96,234.659H53.333c-5.888,0-10.667,4.779-10.667,10.667v85.333c0,5.888,4.779,10.667,10.667,10.667H96 c5.888,0,10.667-4.779,10.667-10.667v-85.333C106.667,239.437,101.888,234.659,96,234.659z M85.333,319.992H64v-64h21.333 V319.992z"/><path d="M96,362.659H53.333c-5.888,0-10.667,4.779-10.667,10.667v85.333c0,5.888,4.779,10.667,10.667,10.667H96 c5.888,0,10.667-4.779,10.667-10.667v-85.333C106.667,367.437,101.888,362.659,96,362.659z M85.333,447.992H64v-64h21.333 V447.992z"/><path d="M202.667,362.659H160c-5.888,0-10.667,4.779-10.667,10.667v85.333c0,5.888,4.779,10.667,10.667,10.667h42.667 c5.888,0,10.667-4.779,10.667-10.667v-85.333C213.333,367.437,208.555,362.659,202.667,362.659z M192,447.992h-21.333v-64H192 V447.992z"/><path d="M504.469,85.795L227.136,0.461c-3.243-0.96-6.763-0.384-9.472,1.621c-2.731,2.005-4.331,5.184-4.331,8.576v128.277 L7.019,213.965C2.795,215.501,0,219.512,0,223.992v277.333c0,5.888,4.779,10.667,10.667,10.667h490.667 c5.888,0,10.667-4.779,10.667-10.667V95.992C512,91.299,508.949,87.16,504.469,85.795z M234.667,490.659H21.333v-259.2 l206.293-75.008h0.021l7.019-2.56V490.659z M490.667,490.659H256v-352c0-1.749-0.427-3.435-1.195-4.928 c-0.768-1.493-1.92-2.816-3.349-3.819c-2.859-1.984-6.507-2.496-9.771-1.28l-7.019,2.539V25.101l256,78.763V490.659z"/><path d="M288,341.325h42.667c5.888,0,10.667-4.779,10.667-10.667v-85.333c0-5.888-4.779-10.667-10.667-10.667H288 c-5.888,0-10.667,4.779-10.667,10.667v85.333C277.333,336.547,282.112,341.325,288,341.325z M298.667,255.992H320v64h-21.333 V255.992z"/><path d="M202.667,234.659H160c-5.888,0-10.667,4.779-10.667,10.667v85.333c0,5.888,4.779,10.667,10.667,10.667h42.667 c5.888,0,10.667-4.779,10.667-10.667v-85.333C213.333,239.437,208.555,234.659,202.667,234.659z M192,319.992h-21.333v-64H192 V319.992z"/><path d="M288,213.325h42.667c5.888,0,10.667-4.779,10.667-10.667v-85.333c0-5.888-4.779-10.667-10.667-10.667H288 c-5.888,0-10.667,4.779-10.667,10.667v85.333C277.333,208.547,282.112,213.325,288,213.325z M298.667,127.992H320v64h-21.333 V127.992z"/><path d="M288,469.325h42.667c5.888,0,10.667-4.779,10.667-10.667v-85.333c0-5.888-4.779-10.667-10.667-10.667H288 c-5.888,0-10.667,4.779-10.667,10.667v85.333C277.333,464.547,282.112,469.325,288,469.325z M298.667,383.992H320v64h-21.333 V383.992z"/><path d="M394.667,213.325h42.667c5.888,0,10.667-4.779,10.667-10.667v-85.333c0-5.888-4.779-10.667-10.667-10.667h-42.667 c-5.888,0-10.667,4.779-10.667,10.667v85.333C384,208.547,388.779,213.325,394.667,213.325z M405.333,127.992h21.333v64h-21.333 V127.992z"/><path d="M394.667,341.325h42.667c5.888,0,10.667-4.779,10.667-10.667v-85.333c0-5.888-4.779-10.667-10.667-10.667h-42.667 c-5.888,0-10.667,4.779-10.667,10.667v85.333C384,336.547,388.779,341.325,394.667,341.325z M405.333,255.992h21.333v64h-21.333 V255.992z"/><path d="M394.667,469.325h42.667c5.888,0,10.667-4.779,10.667-10.667v-85.333c0-5.888-4.779-10.667-10.667-10.667h-42.667 c-5.888,0-10.667,4.779-10.667,10.667v85.333C384,464.547,388.779,469.325,394.667,469.325z M405.333,383.992h21.333v64h-21.333 V383.992z"/></svg>',
                "route" => "units.index",
                "title" => "Units",
                "description" => "Manage system units and subunits."
            ],
            [
              "logo" => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 20H6C3.79086 20 2 18.2091 2 16V7C2 4.79086 3.79086 3 6 3H17C19.2091 3 21 4.79086 21 7V10" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 2V4" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 2V4" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 8H21" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 15.6429L17 17.1429" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="17" cy="17" r="5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
              "route" => "schedule.index",
              "title" => "Schedule",
              "description" => "Manage your schedule."
            ],
          ]
        ];
        
        return Inertia::render('Main/ListButtons', $content);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
