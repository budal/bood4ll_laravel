<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = User::orderBy('name', 'asc')
                    ->paginate(100);

        $items->each( function($item) {
            $item->verified = $item->verified($item->email_verified_at);
            // $item->specialties = $item->specialties($item->id)->pluck('name')->all();
        } );
      
        $content = [
            'routeCreate' => 'users.create',
            'routeEdit' => 'users.edit',
            'routeDelete' => 'users.delete',

            'title' => __("Users"),
            'subtitle' => __("Select the user to manage"),
            'description' => __("Manage the registered users of the system."),
            
            'emptyMessage' => __("There is no users to manage."),

            'titles' => [
                "image" => "",
                "name" => __("Name"),
                "email" => __("E-Mail Address"),
                "verified" => __("Verified"),
                "permissions" => __("Permissions"),
            ],

            'items' => $items
        ];
        
        return Inertia::render('Main/ListTable', $content);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return "A";
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
        return "B";
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
        return "C";
        //
    }
}
