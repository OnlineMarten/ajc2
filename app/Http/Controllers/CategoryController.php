<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        //used from router to display page and from an axios call to get the (initial) data

        if($request->wantsJson())  {


        $categories = Category::
        orderBy("order")
        ->orderBy("title")
        ->get();

            return response()->json([
                'categories'    => $categories,
            ], 200);
        }

        return view('admin.category');

    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //vue-axios
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'order'         => 'required|numeric|min:1',
            'title'          => 'required|max:255',
        ]);

        $category = Category::create([
            'order'         => request('order'),
            'title'          => request('title'),
            'description'   => request('description'),
            'admin_notes'   => request('admin_notes'),
        ]);

        logger()->channel('info')->info('category "'.request("title").'" created by '.auth()->user()->name);

        return response()->json([
            'message'       => 'New Category "'.request("title"). '" created'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //vue-axios
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        //vue-axios
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'order'         => 'required|min:1',
            'title'          => 'required|max:255',
        ]);

        $category = Category::findOrFail($id);

        $category->order = request('order');
        $category->title = request('title');
        $category->description = request('description');
        $category->admin_notes = request('admin_notes');

        if (!$category->update()) {
            return response()->json([
                'message' => 'update failed'
            ], 200);

        }

        logger()->channel('info')->info('category "'.$category->title.'" updated by '.auth()->user()->name);

        return response()->json([
            'message' => 'Category "'. $category->title .'" updated'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
       // $category = Category::findOrFail($id);
        $title = $category->title;
        if ($category->delete()) {

            logger()->channel('info')->info('category "'.$title.'" deleted by '.auth()->user()->name);

            return response()->json([
                'message' => 'Category "'. $title .'"  deleted'
            ], 200);
        }

        return response()->json([
            'message' => 'Delete failed!'
        ], 422);
    }
}
