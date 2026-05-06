<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PacketParserService;
use App\Models\Protocol;

class PacketAnalyzerController extends Controller
{
    protected $packetParser;

    public function __construct(PacketParserService $packetParser)
    {
        $this->packetParser = $packetParser;
    }

    public function index()
    {
        return view('packet_analyzer.index');
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'raw_packet' => 'required|string',
        ]);

        $rawData = $request->input('raw_packet');

        // We will fetch all active protocols and their packet types
        $protocols = Protocol::with(['packetTypes' => function ($q) {
            $q->where('is_active', true);
        }, 'packetTypes.fields' => function ($q) {
            $q->orderBy('sequence');
        }])->where('is_active', true)->get();

        $matches = [];

        foreach ($protocols as $protocol) {
            foreach ($protocol->packetTypes as $candidate) {
                // Using reflection or a public method if available
                // PacketParserService has processPacket as protected, so we can't call it directly.
                // We will use the parse method, but parse() returns only the BEST match.
                // To get all matches, we can use parse() if we pass the protocolId and packetTypeId!
                
                $result = $this->packetParser->parse($rawData, $protocol->id, $candidate->id, null);
                
                // If the status is not 'none' (which means skipped/unmatched), it's a match candidate.
                if ($result['status'] !== 'none') {
                    $matches[] = $result;
                }
            }
        }

        // Sort matches: Valid ones first, then by least errors
        usort($matches, function ($a, $b) {
            if ($a['is_valid'] && !$b['is_valid']) return -1;
            if (!$a['is_valid'] && $b['is_valid']) return 1;
            
            return count($a['errors']) <=> count($b['errors']);
        });

        return response()->json([
            'matches' => $matches,
            'raw_packet' => $rawData,
        ]);
    }
}
