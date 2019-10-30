<?php

namespace App\Http\Controllers;

use App\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
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


            $categories = Ticket::
            orderBy("order")
            ->orderBy("title")
            ->get();

                return response()->json([
                    'tickets'    => $categories,
                ], 200);
            }

            return view('admin.ticket');
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

        $ticket = Ticket::create([
            'order'         => request('order'),
            'title'         => request('title'),
            'description'   => request('description'),
            'admin_notes'   => request('admin_notes'),
            'price'         => request('price'),
            'vat'           => request('vat'),
        ]);

        logger()->channel('info')->info('ticket "'.request("title").'" created by '.auth()->user()->name);

        return response()->json([
            'message'       => 'New Ticket "'.request('title') . '" created'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'order'         => 'required|min:1',
            'title'          => 'required|max:255',
        ]);

        $ticket = Ticket::findOrFail($id);

        $ticket->order = request('order');
        $ticket->title = request('title');
        $ticket->description = request('description');
        $ticket->admin_notes = request('admin_notes');
        $ticket->price = request('price');
        $ticket->vat = request('vat');

        if (!$ticket->update()) {
            return response()->json([
                'message' => 'update failed'
            ], 200);

        }

        logger()->channel('info')->info('ticket "'.$ticket->title.'" updated by '.auth()->user()->name);

        return response()->json([
            'message' => 'Ticket "'. $ticket->title .'" updated'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $dependencies = $this->countDependencies($id);
        if ($dependencies) {
            return response()->json([
                'message' => 'Can not delete, this ticket is connected to '. $dependencies.' event(s)'
            ], 403);
        }


        $ticket = Ticket::findOrFail($id);
        $title = $ticket->title;
        if ($ticket->delete()) {

            logger()->channel('info')->info('Ticket "'.$title.'" deleted by '.auth()->user()->name);

            return response()->json([
                'message' => 'Ticket "' . $title . '"  deleted',
            ], 200);
        }

        return response()->json([
            'message' => 'Delete failed!',
        ], 422);

    }

    public function countDependencies($id){

        return Ticket::find($id)->ticketgroups->Count();
    }
}
