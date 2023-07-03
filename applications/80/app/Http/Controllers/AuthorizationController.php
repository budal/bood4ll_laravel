<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Team;

class AuthorizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Team::orderBy('name', 'asc')
                    ->paginate(100);

        $content = [
            'routeCreate' => 'authorization.create',
            'routeEdit' => 'authorization.edit',
            'routeDelete' => 'authorization.delete',
            
            'title' => "Authorization",
            'subtitle' => "Select the authorization to manage",
            'description' => "Manage app authorizations and permissions assignable to users.",
            
            'emptyMessage' => "There is no authorizations to manage.",

            'titles' => [
                "name" => "Name",
                "user_id" => "E-Mail Address",
                "personal_team" => "Permissions",
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
        return 9;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return 3;
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
        return 1;
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
        return 2;
        //
    }
}
