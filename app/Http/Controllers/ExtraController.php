<?php

namespace App\Http\Controllers;

use App\Extra;
use Illuminate\Http\Request;

class ExtraController extends Controller
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


            $extras = Extra::
            orderBy("order")
            ->get();

                return response()->json([
                    'extras'    => $extras,
                ], 200);
            }

            return view('admin.extra');
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
        $this->validate($request, [
            'order'         => 'required|numeric|min:1',
            'title'          => 'required|max:255',

        ]);


        $extra = Extra::create([
            'order'                 => request('order'),
            'title'                 => request('title'),
            'description'           => request('description'),
            'admin_notes'           => request('admin_notes'),
            'price'                 => request('price'),
            'vat'                   => request('vat'),
            'max'                   => request('max'),
            'show_on_door_list'     => request('show_on_door_list'),
            'properties'            => request('properties'),
        ]);


        logger()->channel('info')->info('extra "'.request("title").'" created by '.auth()->user()->name);

        return response()->json([
            'message'       => 'New Extra "'.request('title'). '" created'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Extra  $extra
     * @return \Illuminate\Http\Response
     */
    public function show(Extra $extra)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Extra  $extra
     * @return \Illuminate\Http\Response
     */
    public function edit(Extra $extra)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Extra  $extra
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'order'         => 'required|numeric|min:1',
            'title'          => 'required|max:255',
        ]);

        $extra = Extra::findOrFail($id);
            $extra->order               = request('order');
            $extra->title               = request('title');
            $extra->description         = request('description');
            $extra->admin_notes         = request('admin_notes');
            $extra->price               = request('price');
            $extra->vat                 = request('vat');
            $extra->max                 = request('max');
            $extra->show_on_door_list   = request('show_on_door_list');
            $extra->properties          = request('properties');

        if (!$extra->update()) {
            return response()->json([
                'message' => 'update failed'
            ], 200);

        }


        logger()->channel('info')->info('extra "'.request("title").'" updated by '.auth()->user()->name);

        return response()->json([
            'message'       => 'Extra "'.request('title'). '" updated'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Extra  $extra
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        $dependencies = $this->countDependencies($id);
        if ($dependencies) {
            return response()->json([
                'message' => 'Can not delete, this extra is connected to '. $dependencies.' category(s)'
            ], 403);
        }


        $extra = Extra::findOrFail($id);
        $title = $extra->title;
        if ($extra->delete()) {

            logger()->channel('info')->info('Extra "'.$title.'" deleted by '.auth()->user()->name);

            return response()->json([
                'message' => 'Extra "' . $title . '"  deleted',
            ], 200);
        }

        return response()->json([
            'message' => 'Delete failed!',
        ], 422);

    }



    public function countDependencies($id){

        return Extra::find($id)->categories->Count();
    }

    public function allCategoriesConnectedToExtra($extra_id)
    {
        //get all categories  connected to a specific extra

        $extra = Extra::findOrFail($extra_id);
        $checkedcategories = $extra->categories->pluck('id');

        return response()->json([
            'checkedcategories' => $checkedcategories,
        ], 200);
    }
}
