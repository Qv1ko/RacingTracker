<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverController extends Controller
{
    public function index(Request $req)
    {
        return Inertia::render('drivers/index');
    }

    public function show(Request $req)
    {
        return Inertia::render('drivers/show');
    }

    public function create()
    {
        return Inertia::render('drivers/create');
    }

    public function store(Request $req)
    {
        return Inertia::render('drivers/store');
    }

    public function edit(Request $req)
    {
        return Inertia::render('drivers/edit');
    }

    public function update(Request $req)
    {
        return Inertia::render('drivers/update');
    }

    public function destroy(Request $req)
    {
        return Inertia::render('drivers/destroy');
    }
}
