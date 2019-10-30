<?php

namespace App\Http\Controllers;

use App\TicketGroup;
use Illuminate\Http\Request;

class TicketGroupController extends Controller
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


            $ticketgroups = TicketGroup::
            withCount('tickets')
            ->orderBy("order")
            ->orderBy("title")
            ->get();

                return response()->json([
                    'ticket_groups'    => $ticketgroups,
                ], 200);
            }

            return view('admin.ticket_group');
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
            'admin_notes'      => 'required|numeric|min:1',
            'checkedTickets'   => 'required|array|min:1',
        ],
        [
            'checkedTickets.required' => 'Ticketgroup can not be empty. Please select minimal one ticket'
        ]);

        $ticketgroup = TicketGroup::create([
            'order'         => request('order'),
            'title'          => request('title'),
            'description'   => request('description'),
            'admin_notes'   => request('admin_notes'),
        ]);

        $ticketgroup->tickets()->sync($request->checkedTickets);//attach extras to ticketgroup

        logger()->channel('info')->info('ticketgroup "'.request("title").'" created by '.auth()->user()->name);

        return response()->json([
            'message'       => 'New Ticketgroup "'.request("title"). '" created'
        ], 200);
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
        $this->validate($request, [
            'admin_notes'      => 'required|numeric|min:1',
            'checkedTickets'   => 'required|array|min:1',
        ],
        [
            'checkedTickets.required' => 'Ticketgroup can not be empty. Please select minimal one ticket'
        ]);

        $ticket_group = TicketGroup::findOrFail($id);

        $ticket_group->order = request('order');
        $ticket_group->title = request('title');
        $ticket_group->description = request('description');
        $ticket_group->admin_notes = request('admin_notes');

        if (!$ticket_group->update()) {
            return response()->json([
                'message' => 'update failed'
            ], 200);

        }
        $ticket_group->tickets()->sync($request->checkedTickets);//attach tickets to ticket_group

        logger()->channel('info')->info('ticketgroup "'.$ticket_group->admin_notes.'" updated by '.auth()->user()->name);

        return response()->json([
            'message' => 'Ticketgroup "'. $ticket_group->admin_notes .'" updated'
        ], 200);
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

    public function allTicketsConnectedToTicketGroup($ticket_group_id)
    {
        //get all categories  connected to a specific extra

        $ticket_group = TicketGroup::findOrFail($ticket_group_id);
        $tickets = $ticket_group->tickets->pluck('id');
       // $tickets = $ticket_group->tickets()->get();
        return response()->json([
            'checkedTickets' => $tickets,
        ], 200);
    }
}
