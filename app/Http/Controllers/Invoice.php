<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class Invoice extends Controller
{
    public function show($id)
    {
        $transaction = Transaction::find($id)->load('transactionWastes');

        $pdf = Pdf::loadView('invoice', compact('transaction'));

        $paperSize = array(0, 0, 161.57, 842.07);
        $pdf->setPaper($paperSize);

        $GLOBALS['bodyHeight'] = 0;

        $pdf->setCallbacks([
            'myCallbacks' => [
                'event' => 'end_frame',
                'f' => function ($frame) {
                    $node = $frame->get_node();

                    if (strtolower($node->nodeName) === "body") {
                        $padding_box = $frame->get_padding_box();
                        $GLOBALS['bodyHeight'] += $padding_box['h'];
                    }
                }
            ]
        ]);

        $pdf->render();
        unset($pdf);
        $docHeight = $GLOBALS['bodyHeight'];

        $pdf = Pdf::loadView('invoice', compact('transaction'));
        $pdf->setPaper([0, 0, 227, $docHeight]);

        return $pdf->stream();
    }
}
