<?php

namespace App\Http\Controllers;

use App\PromoCode;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
            if($request->wantsJson())  {


            $promocodes = PromoCode::
            orderBy("valid_until")
            ->get();

                return response()->json([
                    'promocodes'    => $promocodes,
                ], 200);
            }

            return view('admin.promocode');
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
            'code'         => 'required|min:6',
            'valid_until'   => 'required|date',

        ]);


        $promocode = PromoCode::create([
            'code'                 => request('code'),
            'description'           => request('description'),
            'discount_amount'                 => request('discount_amount'),
            'discount_perc'                   => request('discount_perc'),
            'apply_to_tickets'                   => request('apply_to_tickets'),
            'apply_to_extras'     => request('apply_to_extras'),
            'pay_by_invoice'            => request('pay_by_invoice'),
            'valid_until'            => request('valid_until'),
        ]);


        logger()->channel('info')->info('Promocode "'.request("code").'" created by '.auth()->user()->name);

        return response()->json([
            'message'       => 'New Promocode "'.request('code'). '" created'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PromoCode  $promoCode
     * @return \Illuminate\Http\Response
     */
    public function show(PromoCode $promoCode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PromoCode  $promoCode
     * @return \Illuminate\Http\Response
     */
    public function edit(PromoCode $promoCode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PromoCode  $promoCode
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'code'          => 'required|min:6',
            'valid_until'   => 'required|date',

        ]);


            $promocode = PromoCode::findOrFail($id);
            $promocode->description     = request('description');
            $promocode->discount_amount     = request('discount_amount');
            $promocode->discount_perc     = request('discount_perc');
            $promocode->apply_to_tickets     = request('apply_to_tickets');
            $promocode->apply_to_extras     = request('apply_to_extras');
            $promocode->pay_by_invoice     = request('pay_by_invoice');
            $promocode->valid_until     = request('valid_until');

            if (!$promocode->update()) {
                return response()->json([
                    'message' => 'update failed'
                ], 200);

            }

        logger()->channel('info')->info('Promocode "'.request("code").'" updated by '.auth()->user()->name);

        return response()->json([
            'message'       => 'Promocode "'.request('code'). '" updated'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PromoCode  $promoCode
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $promocode = PromoCode::findOrFail($id);
        $code = $promocode->code;
        if ($promocode->delete()) {

            logger()->channel('info')->info('Promocode "'.$code.'" deleted by '.auth()->user()->name);

            return response()->json([
                'message' => 'Promocode "'. $code .'"  deleted'
            ], 200);
        }

        return response()->json([
            'message' => 'Delete failed!'
        ], 422);
    }

    public function checkPromoCode($code)
    {
        $promocode = PromoCode::where('code', $code)->first();

        if ($promocode){
            $deadline = date('Y-m-d',strtotime(now()));
            if ($promocode->valid_until >= $deadline){
                return response()->json([
                    'promocode' => $promocode
                ], 200);
            }
        }

         return response()->json([
            'promocode' => 'false',

        ], 200);
    }

}


