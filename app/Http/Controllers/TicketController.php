<?php

namespace App\Http\Controllers;

use App\backend;
use App\Http\Controllers\Controller;
use App\Models\TicketModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $ticketList = TicketModel::get();
        $url_type = self::getURLType();
        return view('view-raised-ticket', ['ticketList' => $ticketList, 'url_type' => $url_type]);
    }
    public function createTicket(Request $request)
    {
        // 🔹 Step 1: Validation
        $request->validate([
            'ticket_type'        => 'required|string|max:255',
            'ticket_subject'     => 'required|string|max:255',
            'ticket_description' => 'required|string',
            'error_file'         => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048', // 2MB limit
        ]);
        try {
            $filePath = null;
            if ($request->hasFile('error_file')) {
                $filePath = $request->file('error_file')->store('tickets', 'public');
            }
            $ticket = TicketModel::create([
                'type'        => $request->ticket_type,
                'subject'     => $request->ticket_subject,
                'description' => $request->ticket_description,
                'file'        => $filePath,
                'is_read'     => 0,
                'status'      => 'open',
                'created_by'  => auth()->id() ?? null,
            ]);
            return json_encode([
                'status'     => 200,
                'status_msg' => 'Ticket created successfully!',
                'data'       => $ticket
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'status' => 500,
                'status_msg' => 'Something went wrong. ' . $e->getMessage(),
            ]);
        }
    }

    // Mark ticket as resolved
    public function markAsResolved($id)
    {
        try {
            $ticket = TicketModel::findOrFail($id);
            $ticket->status = 'Resolved';   // assuming you have a `status` column
            $ticket->is_read = 1;           // optional: also mark as read
            $ticket->save();

            return json_encode([
                'status' => 200,
                'message' => 'Ticket marked as resolved successfully!'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'status' => 500,
                'message' => 'Something went wrong. Please try again.'
            ]);
        }
    }

    public function viewTickets()
    {
        $ticketList = TicketModel::orderBy('created_at', 'desc')->get();
        return view('ticket-management', compact('ticketList'));
    }
}
